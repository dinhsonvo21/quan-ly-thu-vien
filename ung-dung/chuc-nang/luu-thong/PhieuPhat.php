<?php
declare(strict_types=1);

namespace ChucNang\LuuThong;

use Loi\CoSoDuLieu;

final class PhieuPhat
{
    /**
     * Ghi một khoản phạt (mỗi phiếu tối đa 1 dòng). Gọi BÊN TRONG CoSoDuLieu::giaoDich().
     * da_thu = true -> ghi luôn ngày thu (thu đủ tại quầy); false -> ghi nợ, chờ thu sau.
     * @param array{phieu_muon_id:int, so_ngay_tre:int, phi_tre:int, tinh_trang_sach:string, phi_boi_hoan:int, da_thu:bool} $duLieu
     */
    public static function ghi(array $duLieu): int
    {
        $daThu = !empty($duLieu['da_thu']);
        CoSoDuLieu::truyVan(
            'INSERT INTO fines (phieu_muon_id, so_ngay_tre, phi_tre, tinh_trang_sach, phi_boi_hoan, da_thu, ngay_thu)
             VALUES (?, ?, ?, ?, ?, ?, ' . ($daThu ? 'NOW()' : 'NULL') . ')',
            [
                $duLieu['phieu_muon_id'], $duLieu['so_ngay_tre'], $duLieu['phi_tre'],
                $duLieu['tinh_trang_sach'], $duLieu['phi_boi_hoan'], $daThu ? 1 : 0,
            ]
        );
        return (int) CoSoDuLieu::ketNoi()->lastInsertId();
    }

    public static function tongChuaThu(int $nguoiDungId): int
    {
        return (int) CoSoDuLieu::truyVan(
            'SELECT COALESCE(SUM(f.phi_tre + f.phi_boi_hoan), 0)
               FROM fines f JOIN borrow_tickets t ON t.id = f.phieu_muon_id
              WHERE t.nguoi_dung_id = ? AND f.da_thu = 0',
            [$nguoiDungId]
        )->fetchColumn();
    }

    private const SELECT_CHUA_THU =
        "SELECT f.id, CONCAT('PM-', t.id) AS ma_phieu,
                CONCAT(u.ho_dem, ' ', u.ten) AS sinh_vien, COALESCE(u.ma_nguoi_dung, '') AS ma_sv,
                b.ten_sach, f.so_ngay_tre, f.phi_tre, f.tinh_trang_sach, f.phi_boi_hoan,
                (f.phi_tre + f.phi_boi_hoan) AS tong, f.ngay_ghi
           FROM fines f
           JOIN borrow_tickets t ON t.id = f.phieu_muon_id
           JOIN users u ON u.id = t.nguoi_dung_id
           JOIN book_copies c ON c.id = t.ban_sao_id
           JOIN books b ON b.id = c.sach_id
          WHERE f.da_thu = 0";

    /**
     * Các khoản phạt đang ghi nợ, cũ nhất trước — bảng "Phí phạt chưa thu".
     * @return array<int, array<string, mixed>>
     */
    public static function layChuaThu(): array
    {
        return CoSoDuLieu::truyVan(self::SELECT_CHUA_THU . ' ORDER BY f.ngay_ghi, f.id LIMIT 100')->fetchAll();
    }

    /** @return array<string, mixed>|null khoản còn nợ theo id; null nếu không có hoặc đã thu */
    public static function timChuaThu(int $id): ?array
    {
        $r = CoSoDuLieu::truyVan(self::SELECT_CHUA_THU . ' AND f.id = ?', [$id])->fetch();
        return $r === false ? null : $r;
    }

    public static function danhDauDaThu(int $id): bool
    {
        return CoSoDuLieu::truyVan(
            'UPDATE fines SET da_thu = 1, ngay_thu = NOW() WHERE id = ? AND da_thu = 0',
            [$id]
        )->rowCount() > 0;
    }
}
