<?php
declare(strict_types=1);

namespace ChucNang\KhoSach;

use Loi\CoSoDuLieu;
use PDO;

/**
 * Bảng `book_copies` (từng bản sao: BK-002-05...). ĐÂY LÀ HỢP ĐỒNG — Quân (SR-08/09) và Sơn (đặt chỗ) gọi:
 * giữ nguyên chữ ký hàm. Các hàm đổi trạng thái phải được gọi BÊN TRONG CoSoDuLieu::giaoDich().
 */
final class BanSao
{
    /**
     * Lấy 1 bản san_co của đầu sách và KHÓA dòng (SELECT ... FOR UPDATE) để không ai khác lấy trùng.
     * @return array{id:int, ma_ban_sao:string, sach_id:int, trang_thai:string}|null  null nếu hết sách
     */
    public static function layBanSanCo(int $sachId): ?array
    {
        $r = CoSoDuLieu::truyVan(
            'SELECT id, ma_ban_sao, sach_id, trang_thai FROM book_copies
              WHERE sach_id = ? AND trang_thai = ? ORDER BY id LIMIT 1 FOR UPDATE',
            [$sachId, BS_SAN_CO]
        )->fetch();
        if ($r === false) {
            return null;
        }
        $r['id'] = (int) $r['id'];
        $r['sach_id'] = (int) $r['sach_id'];
        return $r;
    }

    /** $trangThai: BS_SAN_CO | BS_DANG_GIU | BS_DANG_MUON | BS_HONG | BS_MAT. true nếu trạng thái đã được đổi. */
    public static function doiTrangThai(int $banSaoId, string $trangThai): bool
    {
        if (!in_array($trangThai, [BS_SAN_CO, BS_DANG_GIU, BS_DANG_MUON, BS_HONG, BS_MAT], true)) {
            return false;
        }
        return CoSoDuLieu::truyVan(
            'UPDATE book_copies SET trang_thai = ? WHERE id = ?',
            [$trangThai, $banSaoId]
        )->rowCount() > 0;
    }

    /** Số bản đang san_co của một đầu sách (nhãn "Còn sách", "Còn 3/5 bản"). */
    public static function demSanCo(int $sachId): int
    {
        return (int) CoSoDuLieu::truyVan(
            'SELECT COUNT(*) FROM book_copies WHERE sach_id = ? AND trang_thai = ?',
            [$sachId, BS_SAN_CO]
        )->fetchColumn();
    }

    /** Tổng số bản sao (mọi trạng thái) của một đầu sách. */
    public static function demTong(int $sachId): int
    {
        return (int) CoSoDuLieu::truyVan('SELECT COUNT(*) FROM book_copies WHERE sach_id = ?', [$sachId])->fetchColumn();
    }

    /** Tạo thêm $soLuong bản sao, tự sinh mã BK-xxx-nn. */
    public static function taoBanSao(int $sachId, int $soLuong): bool
    {
        if ($soLuong < 1) {
            return false;
        }
        return (bool) CoSoDuLieu::giaoDich(static function (PDO $pdo) use ($sachId, $soLuong): bool {
            $cau = $pdo->prepare('SELECT ma_sach FROM books WHERE id = ? FOR UPDATE');
            $cau->execute([$sachId]);
            $maSach = $cau->fetchColumn();
            if ($maSach === false) {
                return false;
            }
            $cuoi = (int) CoSoDuLieu::truyVan(
                "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(ma_ban_sao, '-', -1) AS UNSIGNED)), 0)
                   FROM book_copies WHERE sach_id = ?",
                [$sachId]
            )->fetchColumn();
            $them = $pdo->prepare('INSERT INTO book_copies (ma_ban_sao, sach_id, trang_thai) VALUES (?, ?, ?)');
            for ($i = 1; $i <= $soLuong; $i++) {
                $them->execute([sprintf('%s-%02d', $maSach, $cuoi + $i), $sachId, BS_SAN_CO]);
            }
            return true;
        });
    }

    /**
     * Xóa $soLuong bản san_co (mã lớn nhất trước) CHƯA từng có phiếu mượn / đặt chỗ.
     * Không đủ bản để xóa -> không xóa gì, trả false.
     */
    public static function xoaBanSanCo(int $sachId, int $soLuong): bool
    {
        if ($soLuong < 1) {
            return false;
        }
        return (bool) CoSoDuLieu::giaoDich(static function (PDO $pdo) use ($sachId, $soLuong): bool {
            $cau = $pdo->prepare(
                'SELECT c.id FROM book_copies c
                  WHERE c.sach_id = ? AND c.trang_thai = ?
                    AND NOT EXISTS (SELECT 1 FROM borrow_tickets t WHERE t.ban_sao_id = c.id)
                    AND NOT EXISTS (SELECT 1 FROM reservations r WHERE r.ban_sao_id = c.id)
                  ORDER BY c.id DESC LIMIT ' . $soLuong . ' FOR UPDATE'
            );
            $cau->execute([$sachId, BS_SAN_CO]);
            $ids = $cau->fetchAll(PDO::FETCH_COLUMN);
            if (count($ids) < $soLuong) {
                return false;
            }
            $dau = implode(',', array_fill(0, count($ids), '?'));
            $pdo->prepare("DELETE FROM book_copies WHERE id IN ($dau)")->execute($ids);
            return true;
        });
    }
}