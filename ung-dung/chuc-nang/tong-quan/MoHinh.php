<?php
declare(strict_types=1);

namespace ChucNang\TongQuan;

use ChucNang\LuuThong\TinhPhiPhat;
use Loi\CoSoDuLieu;

/** Số liệu thống kê — chỉ ĐỌC từ books / book_copies / borrow_tickets / fines / reservations. */
final class MoHinh
{
    /** Chuẩn hóa tham số tháng về 'Y-m'; sai định dạng thì dùng tháng hiện tại. */
    public static function chuanHoaThang(?string $thang): string
    {
        if ($thang !== null && preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $thang) === 1) {
            return $thang;
        }
        return date('Y-m');
    }

    /** @return array<string, string> 'Y-m' => 'Tháng m/Y' (12 tháng gần nhất, mới nhất trước) */
    public static function cacThangChon(): array
    {
        $ds = [];
        $dau = new \DateTimeImmutable('first day of this month');
        for ($i = 0; $i < 12; $i++) {
            $t = $dau->modify("-{$i} month");
            $ds[$t->format('Y-m')] = 'Tháng ' . $t->format('m/Y');
        }
        return $ds;
    }

    /**
     * 4 thẻ SR-06. Các số "tồn" (sách, đang mượn, quá hạn, phí chưa thu) là ảnh chụp hiện tại;
     * chỉ phieu_moi_thang phụ thuộc tháng được chọn.
     * @return array<string, int>
     */
    public static function thongKe(string $thang): array
    {
        $thang = self::chuanHoaThang($thang);
        $dauThang = $thang . '-01';

        $sach = CoSoDuLieu::truyVan(
            "SELECT (SELECT COUNT(*) FROM books WHERE trang_thai_luu_hanh = 'dang_luu_hanh') AS dau_sach,
                    (SELECT COUNT(*) FROM book_copies) AS ban_sao"
        )->fetch();

        $muon = CoSoDuLieu::truyVan(
            "SELECT COUNT(*) AS dang_cho_muon,
                    COALESCE(SUM(han_tra < CURDATE()), 0) AS phieu_qua_han
               FROM borrow_tickets WHERE trang_thai = 'dang_muon'"
        )->fetch();

        $moi = CoSoDuLieu::truyVan(
            "SELECT COUNT(*) FROM borrow_tickets
              WHERE ngay_muon >= ? AND ngay_muon < DATE_ADD(?, INTERVAL 1 MONTH)",
            [$dauThang, $dauThang]
        )->fetchColumn();

        $phi = CoSoDuLieu::truyVan(
            "SELECT COUNT(*) AS so_phieu, COALESCE(SUM(phi_tre + phi_boi_hoan), 0) AS tong
               FROM fines WHERE da_thu = 0 AND (phi_tre + phi_boi_hoan) > 0"
        )->fetch();

        return [
            'dau_sach'           => (int) $sach['dau_sach'],
            'ban_sao'            => (int) $sach['ban_sao'],
            'dang_cho_muon'      => (int) $muon['dang_cho_muon'],
            'phieu_moi_thang'    => (int) $moi,
            'phieu_qua_han'      => (int) $muon['phieu_qua_han'],
            'phi_phat_chua_thu'  => (int) $phi['tong'],
            'phieu_phi_chua_thu' => (int) $phi['so_phieu'],
        ];
    }

    /**
     * Phiếu quá hạn, quá hạn lâu nhất trước.
     * @return array<int, array<string, mixed>>
     */
    public static function phieuQuaHan(?int $gioiHan = null, int $viTri = 0): array
    {
        $sql = "SELECT t.id, t.han_tra, u.email,
                       TRIM(CONCAT(u.ho_dem, ' ', u.ten)) AS sinh_vien,
                       b.ten_sach
                  FROM borrow_tickets t
                  JOIN users u       ON u.id = t.nguoi_dung_id
                  JOIN book_copies c ON c.id = t.ban_sao_id
                  JOIN books b       ON b.id = c.sach_id
                 WHERE t.trang_thai = 'dang_muon' AND t.han_tra < CURDATE()
                 ORDER BY t.han_tra ASC, t.id ASC";
        if ($gioiHan !== null) {
            $sql .= ' LIMIT ' . max(0, $gioiHan) . ' OFFSET ' . max(0, $viTri);
        }

        $homNay = date('Y-m-d');
        $ds = CoSoDuLieu::truyVan($sql)->fetchAll();
        foreach ($ds as &$p) {
            $p['id']           = (int) $p['id'];
            $p['so_ngay_tre']  = TinhPhiPhat::soNgayTre((string) $p['han_tra'], $homNay);
            $p['phi_tam_tinh'] = TinhPhiPhat::phiQuaHan((string) $p['han_tra'], $homNay);
        }
        unset($p);
        return $ds;
    }

    public static function demPhieuQuaHan(): int
    {
        return (int) CoSoDuLieu::truyVan(
            "SELECT COUNT(*) FROM borrow_tickets WHERE trang_thai = 'dang_muon' AND han_tra < CURDATE()"
        )->fetchColumn();
    }

    /** @return array{cho_nhan:int, sap_het_han:int} */
    public static function datChoHomNay(): array
    {
        $r = CoSoDuLieu::truyVan(
            "SELECT COUNT(*) AS cho_nhan,
                    COALESCE(SUM(het_han <= DATE_ADD(NOW(), INTERVAL 2 HOUR)), 0) AS sap_het_han
               FROM reservations WHERE trang_thai = 'cho_nhan' AND het_han > NOW()"
        )->fetch();
        return ['cho_nhan' => (int) $r['cho_nhan'], 'sap_het_han' => (int) $r['sap_het_han']];
    }

    /** @return array{luot_muon:int, luot_tra:int} */
    public static function muonTraHomNay(): array
    {
        $r = CoSoDuLieu::truyVan(
            "SELECT COALESCE(SUM(ngay_muon = CURDATE()), 0) AS luot_muon,
                    COALESCE(SUM(ngay_tra = CURDATE()), 0)  AS luot_tra
               FROM borrow_tickets WHERE ngay_muon = CURDATE() OR ngay_tra = CURDATE()"
        )->fetch();
        return ['luot_muon' => (int) $r['luot_muon'], 'luot_tra' => (int) $r['luot_tra']];
    }

    /** Thông tin 1 phiếu quá hạn để gửi email nhắc. */
    public static function phieuDeNhac(int $id): ?array
    {
        $p = CoSoDuLieu::truyVan(
            "SELECT t.id, t.han_tra, u.email, TRIM(CONCAT(u.ho_dem, ' ', u.ten)) AS sinh_vien, b.ten_sach
               FROM borrow_tickets t
               JOIN users u       ON u.id = t.nguoi_dung_id
               JOIN book_copies c ON c.id = t.ban_sao_id
               JOIN books b       ON b.id = c.sach_id
              WHERE t.id = ? AND t.trang_thai = 'dang_muon' AND t.han_tra < CURDATE()",
            [$id]
        )->fetch();
        return $p === false ? null : $p;
    }

    /** Số liệu cho file báo cáo tháng. @return array<string, int> */
    public static function baoCaoThang(string $thang): array
    {
        $thang = self::chuanHoaThang($thang);
        $dau = $thang . '-01';
        $r = CoSoDuLieu::truyVan(
            "SELECT
               (SELECT COUNT(*) FROM borrow_tickets
                 WHERE ngay_muon >= :a AND ngay_muon < DATE_ADD(:b, INTERVAL 1 MONTH)) AS luot_muon,
               (SELECT COUNT(*) FROM borrow_tickets
                 WHERE ngay_tra >= :c AND ngay_tra < DATE_ADD(:d, INTERVAL 1 MONTH)) AS luot_tra,
               (SELECT COALESCE(SUM(phi_tre + phi_boi_hoan), 0) FROM fines
                 WHERE ngay_ghi >= :e AND ngay_ghi < DATE_ADD(:f, INTERVAL 1 MONTH)) AS phi_phat_ghi,
               (SELECT COALESCE(SUM(phi_tre + phi_boi_hoan), 0) FROM fines
                 WHERE da_thu = 1 AND ngay_thu >= :g AND ngay_thu < DATE_ADD(:h, INTERVAL 1 MONTH)) AS phi_phat_da_thu",
            ['a' => $dau, 'b' => $dau, 'c' => $dau, 'd' => $dau, 'e' => $dau, 'f' => $dau, 'g' => $dau, 'h' => $dau]
        )->fetch();
        return array_map('intval', $r);
    }
}