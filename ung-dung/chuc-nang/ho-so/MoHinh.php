<?php
declare(strict_types=1);

namespace ChucNang\HoSo;

use Loi\CoSoDuLieu;
use Loi\DinhDang;

/** Truy vấn cho SR-05: bảng `users` (hồ sơ, mật khẩu) và `borrow_tickets` (lịch sử mượn, gia hạn). */
final class MoHinh
{
    /** @return array<string, mixed>|null có thêm khóa `ho_ten` = ho_dem + ten */
    public static function layHoSo(int $nguoiDungId): ?array
    {
        $dong = CoSoDuLieu::truyVan(
            'SELECT id, ma_nguoi_dung, ho_dem, ten, email, sdt, khoa_lop
             FROM users
             WHERE id = :id
             LIMIT 1',
            ['id' => $nguoiDungId]
        )->fetch();

        if ($dong === false) {
            return null;
        }
        $dong['ho_ten'] = trim((string) $dong['ho_dem'] . ' ' . (string) $dong['ten']);
        return $dong;
    }

    /**
     * Tách "Nguyễn Trần Thị" -> ['Nguyễn Trần', 'Thị'] (từ cuối là tên, phần còn lại là họ đệm).
     * @return array{0:string, 1:string}
     */
    public static function tachHoTen(string $hoTen): array
    {
        $hoTen = trim(preg_replace('/\s+/u', ' ', $hoTen) ?? $hoTen);
        if (preg_match('/^(.*)\s(\S+)$/u', $hoTen, $m) === 1) {
            return [$m[1], $m[2]];
        }
        return ['', $hoTen];
    }

    /** @param array<string, string> $duLieu ho_ten, email, sdt, khoa_lop (không đụng tới mã người dùng) */
    public static function capNhat(int $nguoiDungId, array $duLieu): bool
    {
        [$hoDem, $ten] = self::tachHoTen($duLieu['ho_ten']);
        CoSoDuLieu::truyVan(
            'UPDATE users
             SET ho_dem = :ho_dem, ten = :ten, email = :email, sdt = :sdt, khoa_lop = :khoa_lop
             WHERE id = :id',
            [
                'ho_dem'   => $hoDem,
                'ten'      => $ten,
                'email'    => $duLieu['email'],
                'sdt'      => $duLieu['sdt'] !== '' ? $duLieu['sdt'] : null,
                'khoa_lop' => $duLieu['khoa_lop'] !== '' ? $duLieu['khoa_lop'] : null,
                'id'       => $nguoiDungId,
            ]
        );
        return true;
    }

    /** Email đã thuộc về người dùng khác chưa? */
    public static function emailDaDuocDung(string $email, int $boQuaNguoiDungId): bool
    {
        return CoSoDuLieu::truyVan(
            'SELECT 1 FROM users WHERE email = :email AND id <> :id LIMIT 1',
            ['email' => $email, 'id' => $boQuaNguoiDungId]
        )->fetchColumn() !== false;
    }

    public static function layMatKhauBam(int $nguoiDungId): ?string
    {
        $giaTri = CoSoDuLieu::truyVan(
            'SELECT mat_khau_bam FROM users WHERE id = :id LIMIT 1',
            ['id' => $nguoiDungId]
        )->fetchColumn();
        return $giaTri === false ? null : (string) $giaTri;
    }

    /** $matKhauBam là kết quả password_hash(). */
    public static function doiMatKhau(int $nguoiDungId, string $matKhauBam): bool
    {
        CoSoDuLieu::truyVan(
            'UPDATE users SET mat_khau_bam = :mk WHERE id = :id',
            ['mk' => $matKhauBam, 'id' => $nguoiDungId]
        );
        return true;
    }

    /**
     * Phiếu mượn của bạn đọc, mới nhất trước. Mỗi dòng có:
     *   id, ma_phieu (PM-2081), ten_sach, ngay_muon, han_tra, ngay_tra,
     *   trang_thai_chu ("Đang mượn" | "Quá hạn 3 ngày" | "Đã trả 14/10"),
     *   da_tra, qua_han, co_the_gia_han (bool), ly_do_khong_gia_han (?string)
     * @return array<int, array<string, mixed>>
     */
    public static function layLichSuMuon(int $nguoiDungId): array
    {
        $cac = CoSoDuLieu::truyVan(
            'SELECT bt.id, bt.ngay_muon, bt.han_tra, bt.ngay_tra, bt.so_lan_gia_han, bt.trang_thai,
                    DATEDIFF(CURDATE(), bt.han_tra) AS so_ngay_tre,
                    b.id AS sach_id, b.ten_sach
             FROM borrow_tickets bt
             JOIN book_copies bc ON bc.id = bt.ban_sao_id
             JOIN books b        ON b.id = bc.sach_id
             WHERE bt.nguoi_dung_id = :id
             ORDER BY bt.ngay_muon DESC, bt.id DESC',
            ['id' => $nguoiDungId]
        )->fetchAll();

        $ketQua = [];
        foreach ($cac as $p) {
            $daTra    = $p['trang_thai'] === 'da_tra';
            $soNgayTre = (int) $p['so_ngay_tre'];
            $quaHan   = !$daTra && $soNgayTre > 0;

            if ($daTra) {
                $chu = 'Đã trả ' . DinhDang::ngay((string) $p['ngay_tra'], 'd/m');
            } elseif ($quaHan) {
                $chu = 'Quá hạn ' . $soNgayTre . ' ngày';
            } else {
                $chu = 'Đang mượn';
            }

            // Chỉ truy vấn "có người đặt trước" cho phiếu còn đang mượn và chưa quá hạn.
            $coNguoiDat = !$daTra && !$quaHan
                && self::coNguoiKhacDatTruoc((int) $p['sach_id'], $nguoiDungId);
            $lyDo = self::lyDoKhongGiaHan($daTra, $soNgayTre, (int) $p['so_lan_gia_han'], $coNguoiDat);

            $p['ma_phieu']            = DinhDang::maPhieuMuon((int) $p['id']);
            $p['trang_thai_chu']      = $chu;
            $p['da_tra']              = $daTra;
            $p['qua_han']             = $quaHan;
            $p['co_the_gia_han']      = $lyDo === null;
            $p['ly_do_khong_gia_han'] = $lyDo;
            $ketQua[] = $p;
        }
        return $ketQua;
    }

    /**
     * Gia hạn thêm GIA_HAN_NGAY ngày. Thành công -> true.
     * Không đủ điều kiện -> ném \DomainException, getMessage() là lý do hiển thị cho bạn đọc.
     */
    public static function giaHan(int $phieuMuonId, int $nguoiDungId): bool
    {
        return (bool) CoSoDuLieu::giaoDich(static function (\PDO $pdo) use ($phieuMuonId, $nguoiDungId): bool {
            // Khóa dòng phiếu để hai lần bấm "Gia hạn" cùng lúc không cộng 2 lần.
            $cau = $pdo->prepare(
                'SELECT id, ban_sao_id, trang_thai, so_lan_gia_han,
                        DATEDIFF(CURDATE(), han_tra) AS so_ngay_tre
                 FROM borrow_tickets
                 WHERE id = :id AND nguoi_dung_id = :nguoi_dung_id
                 FOR UPDATE'
            );
            $cau->execute(['id' => $phieuMuonId, 'nguoi_dung_id' => $nguoiDungId]);
            $phieu = $cau->fetch();
            if ($phieu === false) {
                throw new \DomainException('Không tìm thấy phiếu mượn của bạn.');
            }

            $cauSach = $pdo->prepare('SELECT sach_id FROM book_copies WHERE id = :id');
            $cauSach->execute(['id' => $phieu['ban_sao_id']]);
            $sachId = (int) $cauSach->fetchColumn();

            $daTra = $phieu['trang_thai'] === 'da_tra';
            $lyDo = self::lyDoKhongGiaHan(
                $daTra,
                (int) $phieu['so_ngay_tre'],
                (int) $phieu['so_lan_gia_han'],
                !$daTra && self::coNguoiKhacDatTruoc($sachId, $nguoiDungId)
            );
            if ($lyDo !== null) {
                throw new \DomainException($lyDo);
            }

            $cap = $pdo->prepare(
                'UPDATE borrow_tickets
                 SET han_tra = DATE_ADD(han_tra, INTERVAL :ngay DAY),
                     so_lan_gia_han = so_lan_gia_han + 1
                 WHERE id = :id AND trang_thai = \'dang_muon\''
            );
            $cap->execute(['ngay' => GIA_HAN_NGAY, 'id' => $phieuMuonId]);
            return true;
        });
    }

    /** Hạn trả hiện tại của một phiếu (dùng để báo "hạn trả mới" sau khi gia hạn). */
    public static function layHanTra(int $phieuMuonId): ?string
    {
        $giaTri = CoSoDuLieu::truyVan(
            'SELECT han_tra FROM borrow_tickets WHERE id = :id',
            ['id' => $phieuMuonId]
        )->fetchColumn();
        return $giaTri === false ? null : (string) $giaTri;
    }

    /** Có bạn đọc khác đang chờ nhận một bản của đầu sách này không? */
    private static function coNguoiKhacDatTruoc(int $sachId, int $nguoiDungId): bool
    {
        return CoSoDuLieu::truyVan(
            "SELECT 1
             FROM reservations r
             JOIN book_copies bc ON bc.id = r.ban_sao_id
             WHERE bc.sach_id = :sach_id
               AND r.trang_thai = 'cho_nhan'
               AND r.nguoi_dung_id <> :nguoi_dung_id
             LIMIT 1",
            ['sach_id' => $sachId, 'nguoi_dung_id' => $nguoiDungId]
        )->fetchColumn() !== false;
    }

    /** null = đủ điều kiện gia hạn; ngược lại là lý do. Một nơi duy nhất để bảng và nút POST luôn khớp nhau. */
    private static function lyDoKhongGiaHan(bool $daTra, int $soNgayTre, int $soLanGiaHan, bool $coNguoiDat): ?string
    {
        if ($daTra) {
            return 'Sách này đã được trả.';
        }
        if ($soNgayTre > 0) {
            return 'Phiếu đã quá hạn nên không thể gia hạn.';
        }
        if ($soLanGiaHan >= SO_LAN_GIA_HAN_TOI_DA) {
            return 'Phiếu này đã gia hạn tối đa ' . SO_LAN_GIA_HAN_TOI_DA . ' lần.';
        }
        if ($coNguoiDat) {
            return 'Đã có bạn đọc khác đặt trước cuốn sách này.';
        }
        return null;
    }
}
