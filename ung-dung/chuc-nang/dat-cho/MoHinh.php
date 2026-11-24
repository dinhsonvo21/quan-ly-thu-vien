<?php
declare(strict_types=1);

namespace ChucNang\DatCho;

use Loi\CoSoDuLieu;

/**
 * Bảng `reservations`. ĐÂY LÀ HỢP ĐỒNG giữa các thành viên — giữ nguyên chữ ký hàm:
 *   - Trạng (SR-05) gọi: layCuaNguoiDung(), huy()
 *   - Quân (SR-08)  gọi: layDangCho(), danhDauDaGiao()
 *   - Tác vụ nền    gọi: huyQuaHan()
 */
final class MoHinh
{
    /** Tạo đơn giữ chỗ (giao dịch + khóa bản sao). Trả về id đơn. */
    public static function tao(int $nguoiDungId, int $sachId, string $noiNhan = NOI_NHAN_MAC_DINH): int
    {
        return (int) CoSoDuLieu::giaoDich(static function (\PDO $pdo) use ($nguoiDungId, $sachId, $noiNhan): int {
            $cauNguoiDung = $pdo->prepare(
                'SELECT trang_thai, vai_tro FROM users WHERE id = :id FOR UPDATE'
            );
            $cauNguoiDung->execute(['id' => $nguoiDungId]);
            $nguoiDung = $cauNguoiDung->fetch();
            if ($nguoiDung === false || $nguoiDung['trang_thai'] !== 'hoat_dong') {
                throw new \DomainException('Tài khoản không hoạt động nên không thể đặt giữ chỗ.');
            }
            if ($nguoiDung['vai_tro'] !== VAI_TRO_BAN_DOC) {
                throw new \DomainException('Chỉ bạn đọc mới có thể đặt giữ chỗ sách.');
            }

            $cauSach = $pdo->prepare(
                "SELECT id FROM books
                 WHERE id = :id AND trang_thai_luu_hanh = 'dang_luu_hanh'
                 FOR UPDATE"
            );
            $cauSach->execute(['id' => $sachId]);
            if ($cauSach->fetchColumn() === false) {
                throw new \DomainException('Sách không còn được lưu hành.');
            }

            $cauTrung = $pdo->prepare(
                "SELECT 1
                 FROM reservations r
                 INNER JOIN book_copies bc ON bc.id = r.ban_sao_id
                 WHERE r.nguoi_dung_id = :nguoi_dung_id
                   AND bc.sach_id = :sach_id
                   AND r.trang_thai = 'cho_nhan'
                   AND r.het_han > NOW()
                 LIMIT 1"
            );
            $cauTrung->execute(['nguoi_dung_id' => $nguoiDungId, 'sach_id' => $sachId]);
            if ($cauTrung->fetchColumn() !== false) {
                throw new \DomainException('Bạn đã có phiếu giữ chỗ đang hoạt động cho sách này.');
            }

            $cauMuon = $pdo->prepare(
                "SELECT 1
                 FROM borrow_tickets bt
                 INNER JOIN book_copies bc ON bc.id = bt.ban_sao_id
                 WHERE bt.nguoi_dung_id = :nguoi_dung_id
                   AND bc.sach_id = :sach_id
                   AND bt.trang_thai = 'dang_muon'
                 LIMIT 1"
            );
            $cauMuon->execute(['nguoi_dung_id' => $nguoiDungId, 'sach_id' => $sachId]);
            if ($cauMuon->fetchColumn() !== false) {
                throw new \DomainException('Bạn đang mượn sách này nên không thể đặt giữ chỗ thêm.');
            }

            $cauDem = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM reservations
                 WHERE nguoi_dung_id = :nguoi_dung_id
                   AND trang_thai = 'cho_nhan'
                   AND het_han > NOW()"
            );
            $cauDem->execute(['nguoi_dung_id' => $nguoiDungId]);
            if ((int) $cauDem->fetchColumn() >= SO_SACH_TOI_DA) {
                throw new \DomainException(
                    'Bạn đã đạt hạn mức ' . SO_SACH_TOI_DA . ' phiếu giữ chỗ đang hoạt động.'
                );
            }

            $cauBanSao = $pdo->prepare(
                "SELECT id
                 FROM book_copies
                 WHERE sach_id = :sach_id AND trang_thai = 'san_co'
                 ORDER BY id
                 LIMIT 1
                 FOR UPDATE"
            );
            $cauBanSao->execute(['sach_id' => $sachId]);
            $banSaoId = $cauBanSao->fetchColumn();
            if ($banSaoId === false) {
                throw new \DomainException('Sách vừa hết bản sẵn có. Vui lòng thử lại sau.');
            }

            $cauDatCho = $pdo->prepare(
                "INSERT INTO reservations (nguoi_dung_id, ban_sao_id, noi_nhan, het_han, trang_thai)
                 VALUES (:nguoi_dung_id, :ban_sao_id, :noi_nhan,
                         DATE_ADD(NOW(), INTERVAL " . GIU_CHO_GIO . " HOUR), 'cho_nhan')"
            );
            $cauDatCho->execute([
                'nguoi_dung_id' => $nguoiDungId,
                'ban_sao_id' => (int) $banSaoId,
                'noi_nhan' => $noiNhan,
            ]);
            $datChoId = (int) $pdo->lastInsertId();

            $cauCapNhat = $pdo->prepare(
                "UPDATE book_copies
                 SET trang_thai = 'dang_giu'
                 WHERE id = :id AND trang_thai = 'san_co'"
            );
            $cauCapNhat->execute(['id' => (int) $banSaoId]);
            if ($cauCapNhat->rowCount() !== 1) {
                throw new \RuntimeException('Không thể chuyển bản sao sang trạng thái đang giữ.');
            }

            return $datChoId;
        });
    }

    /**
     * Đơn đang chờ nhận, mới hết hạn trước. Dùng cho bảng SR-08.
     * @return array<int, array{id:int, ma_dat_cho:string, sinh_vien:string, ma_sv:string, ten_sach:string, het_han:string}>
     */
    public static function layDangCho(?string $tuKhoa = null): array
    {
        $sql = "SELECT r.id, CONCAT('DC-', LPAD(r.id, 4, '0')) AS ma_dat_cho,
                       CONCAT(u.ho_dem, ' ', u.ten) AS sinh_vien,
                       u.ma_nguoi_dung AS ma_sv, b.ten_sach, r.het_han
                FROM reservations r
                INNER JOIN users u ON u.id = r.nguoi_dung_id
                INNER JOIN book_copies bc ON bc.id = r.ban_sao_id
                INNER JOIN books b ON b.id = bc.sach_id
                WHERE r.trang_thai = 'cho_nhan'";
        $thamSo = [];
        $tuKhoa = trim((string) $tuKhoa);
        if ($tuKhoa !== '') {
            $sql .= " AND (u.ma_nguoi_dung LIKE :ma_nguoi_dung
                           OR u.ho_dem LIKE :ho_dem
                           OR u.ten LIKE :ten
                           OR b.ten_sach LIKE :ten_sach
                           OR bc.ma_ban_sao LIKE :ma_ban_sao)";
            $tuKhoaLike = '%' . $tuKhoa . '%';
            $thamSo = [
                'ma_nguoi_dung' => $tuKhoaLike,
                'ho_dem' => $tuKhoaLike,
                'ten' => $tuKhoaLike,
                'ten_sach' => $tuKhoaLike,
                'ma_ban_sao' => $tuKhoaLike,
            ];
        }
        $sql .= ' ORDER BY r.het_han ASC, r.id ASC';

        return CoSoDuLieu::truyVan($sql, $thamSo)->fetchAll();
    }

    /** @return array<int, array<string, mixed>> đơn đang hoạt động của một bạn đọc (bảng SR-05). */
    public static function layCuaNguoiDung(int $nguoiDungId): array
    {
        return CoSoDuLieu::truyVan(
            "SELECT r.id, r.noi_nhan, r.ngay_dat, r.het_han, r.trang_thai,
                    b.id AS sach_id, b.ten_sach, bc.ma_ban_sao
             FROM reservations r
             INNER JOIN book_copies bc ON bc.id = r.ban_sao_id
             INNER JOIN books b ON b.id = bc.sach_id
             WHERE r.nguoi_dung_id = :nguoi_dung_id
               AND r.trang_thai = 'cho_nhan'
               AND r.het_han > NOW()
             ORDER BY r.het_han ASC, r.id DESC",
            ['nguoi_dung_id' => $nguoiDungId]
        )->fetchAll();
    }

    /** Chuyển đơn sang 'da_giao' — Quân gọi bên trong giao dịch lập phiếu mượn. */
    public static function danhDauDaGiao(int $datChoId): bool
    {
        // TODO (Sơn)
        return false;
    }

    /** Hủy đơn của chính người đó, trả bản sao về san_co. */
    public static function huy(int $datChoId, int $nguoiDungId): bool
    {
        return (bool) CoSoDuLieu::giaoDich(static function (\PDO $pdo) use ($datChoId, $nguoiDungId): bool {
            $cau = $pdo->prepare(
                "SELECT ban_sao_id
                 FROM reservations
                 WHERE id = :id
                   AND nguoi_dung_id = :nguoi_dung_id
                   AND trang_thai = 'cho_nhan'
                 FOR UPDATE"
            );
            $cau->execute(['id' => $datChoId, 'nguoi_dung_id' => $nguoiDungId]);
            $banSaoId = $cau->fetchColumn();
            if ($banSaoId === false) {
                return false;
            }

            $huy = $pdo->prepare(
                "UPDATE reservations SET trang_thai = 'da_huy'
                 WHERE id = :id AND trang_thai = 'cho_nhan'"
            );
            $huy->execute(['id' => $datChoId]);
            if ($huy->rowCount() !== 1) {
                return false;
            }

            $traBanSao = $pdo->prepare(
                "UPDATE book_copies SET trang_thai = 'san_co'
                 WHERE id = :id AND trang_thai = 'dang_giu'"
            );
            $traBanSao->execute(['id' => (int) $banSaoId]);
            if ($traBanSao->rowCount() !== 1) {
                throw new \RuntimeException('Không thể giải phóng bản sao khi hủy giữ chỗ.');
            }

            return true;
        });
    }

    /** Tự hủy mọi đơn quá hạn giữ chỗ (> GIU_CHO_GIO) và giải phóng bản sao. Trả về số đơn đã hủy. */
    public static function huyQuaHan(): int
    {
        return (int) CoSoDuLieu::giaoDich(static function (\PDO $pdo): int {
            $cau = $pdo->query(
                "SELECT id, ban_sao_id
                 FROM reservations
                 WHERE trang_thai = 'cho_nhan' AND het_han <= NOW()
                 FOR UPDATE"
            );
            $danhSach = $cau->fetchAll();
            if ($danhSach === []) {
                return 0;
            }

            $capNhatDatCho = $pdo->prepare(
                "UPDATE reservations SET trang_thai = 'het_han'
                 WHERE id = :id AND trang_thai = 'cho_nhan'"
            );
            $giaiPhongBanSao = $pdo->prepare(
                "UPDATE book_copies SET trang_thai = 'san_co'
                 WHERE id = :id AND trang_thai = 'dang_giu'"
            );
            $soDon = 0;
            foreach ($danhSach as $datCho) {
                $capNhatDatCho->execute(['id' => (int) $datCho['id']]);
                if ($capNhatDatCho->rowCount() !== 1) {
                    continue;
                }
                $giaiPhongBanSao->execute(['id' => (int) $datCho['ban_sao_id']]);
                if ($giaiPhongBanSao->rowCount() !== 1) {
                    throw new \RuntimeException('Không thể giải phóng bản sao của phiếu giữ chỗ quá hạn.');
                }
                $soDon++;
            }

            return $soDon;
        });
    }

    /**
     * @return array{dat_cho: array<string, mixed>|null, du_dieu_kien: bool, ly_do: ?string, so_luong: int}
     */
    public static function kiemTraDieuKienDatCho(int $nguoiDungId, int $sachId): array
    {
        $nguoiDung = CoSoDuLieu::truyVan(
            'SELECT trang_thai, vai_tro FROM users WHERE id = :id LIMIT 1',
            ['id' => $nguoiDungId]
        )->fetch();
        $datCho = CoSoDuLieu::truyVan(
            "SELECT r.id, r.noi_nhan, r.ngay_dat, r.het_han, r.trang_thai,
                    bc.ma_ban_sao
             FROM reservations r
             INNER JOIN book_copies bc ON bc.id = r.ban_sao_id
             WHERE r.nguoi_dung_id = :nguoi_dung_id
               AND bc.sach_id = :sach_id
               AND r.trang_thai = 'cho_nhan'
               AND r.het_han > NOW()
             ORDER BY r.het_han ASC
             LIMIT 1",
            ['nguoi_dung_id' => $nguoiDungId, 'sach_id' => $sachId]
        )->fetch();
        if ($datCho !== false) {
            return ['dat_cho' => $datCho, 'du_dieu_kien' => false, 'ly_do' => null, 'so_luong' => 0];
        }
        if ($nguoiDung === false || $nguoiDung['trang_thai'] !== 'hoat_dong') {
            return ['dat_cho' => null, 'du_dieu_kien' => false, 'ly_do' => 'Tài khoản không hoạt động nên không thể đặt giữ chỗ.', 'so_luong' => 0];
        }
        if ($nguoiDung['vai_tro'] !== VAI_TRO_BAN_DOC) {
            return ['dat_cho' => null, 'du_dieu_kien' => false, 'ly_do' => 'Chỉ bạn đọc mới có thể đặt giữ chỗ sách.', 'so_luong' => 0];
        }

        $coPhieuMuon = CoSoDuLieu::truyVan(
            "SELECT 1
             FROM borrow_tickets bt
             INNER JOIN book_copies bc ON bc.id = bt.ban_sao_id
             WHERE bt.nguoi_dung_id = :nguoi_dung_id
               AND bc.sach_id = :sach_id
               AND bt.trang_thai = 'dang_muon'
             LIMIT 1",
            ['nguoi_dung_id' => $nguoiDungId, 'sach_id' => $sachId]
        )->fetchColumn() !== false;
        if ($coPhieuMuon) {
            return ['dat_cho' => null, 'du_dieu_kien' => false, 'ly_do' => 'Bạn đang mượn sách này nên không thể đặt giữ chỗ thêm.', 'so_luong' => 0];
        }

        $soLuong = (int) CoSoDuLieu::truyVan(
            "SELECT COUNT(*)
             FROM reservations
             WHERE nguoi_dung_id = :nguoi_dung_id
               AND trang_thai = 'cho_nhan'
               AND het_han > NOW()",
            ['nguoi_dung_id' => $nguoiDungId]
        )->fetchColumn();
        if ($soLuong >= SO_SACH_TOI_DA) {
            return [
                'dat_cho' => null,
                'du_dieu_kien' => false,
                'ly_do' => 'Bạn đã đạt hạn mức ' . SO_SACH_TOI_DA . ' phiếu giữ chỗ đang hoạt động.',
                'so_luong' => $soLuong,
            ];
        }

        return ['dat_cho' => null, 'du_dieu_kien' => true, 'ly_do' => null, 'so_luong' => $soLuong];
    }
}
