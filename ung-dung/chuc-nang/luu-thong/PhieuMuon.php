<?php
declare(strict_types=1);

namespace ChucNang\LuuThong;

use Loi\CoSoDuLieu;

final class PhieuMuon
{
    private const SELECT_BAN_DOC =
        "SELECT id, ma_nguoi_dung, CONCAT(ho_dem, ' ', ten) AS ho_ten, khoa_lop, trang_thai
           FROM users WHERE vai_tro = 'ban_doc' AND ";

    /**
     * Đủ điều kiện mượn = tài khoản hoạt động VÀ < SO_SACH_TOI_DA phiếu đang mượn
     * VÀ không có phiếu quá hạn VÀ không nợ phí phạt. (SR-08 khối kiểm tra điều kiện)
     * Tra theo mã sinh viên (SV2024025). Mảng rỗng nếu không có bạn đọc nào mang mã đó.
     * $khoa = true: SELECT ... FOR UPDATE (gọi trong giaoDich để hai quầy không cùng vượt hạn mức).
     * @return array{id:int, ho_ten:string, ma:string, khoa:string, tai_khoan_hoat_dong:bool, dang_muon:int, toi_da:int,
     *         co_qua_han:bool, phi_chua_thanh_toan:int, du_dieu_kien:bool, ly_do:?string}|array{}
     */
    public static function dieuKienMuon(string $maSinhVien, bool $khoa = false): array
    {
        $u = CoSoDuLieu::truyVan(self::SELECT_BAN_DOC . 'ma_nguoi_dung = ? LIMIT 1' . ($khoa ? ' FOR UPDATE' : ''), [$maSinhVien])->fetch();
        return $u === false ? [] : self::tinhDieuKien($u);
    }

    /** Như dieuKienMuon() nhưng tra theo id (dùng khi giao sách đặt trước). @return array<string, mixed> */
    public static function dieuKienMuonTheoId(int $nguoiDungId, bool $khoa = false): array
    {
        $u = CoSoDuLieu::truyVan(self::SELECT_BAN_DOC . 'id = ? LIMIT 1' . ($khoa ? ' FOR UPDATE' : ''), [$nguoiDungId])->fetch();
        return $u === false ? [] : self::tinhDieuKien($u);
    }

    /**
     * @param array<string, mixed> $u
     * @return array<string, mixed>
     */
    private static function tinhDieuKien(array $u): array
    {
        $id = (int) $u['id'];
        $dangMuon = (int) CoSoDuLieu::truyVan(
            "SELECT COUNT(*) FROM borrow_tickets WHERE nguoi_dung_id = ? AND trang_thai = 'dang_muon'", [$id]
        )->fetchColumn();
        $coQuaHan = (bool) CoSoDuLieu::truyVan(
            "SELECT 1 FROM borrow_tickets WHERE nguoi_dung_id = ? AND trang_thai = 'dang_muon' AND han_tra < CURDATE() LIMIT 1", [$id]
        )->fetchColumn();
        $phi = PhieuPhat::tongChuaThu($id);
        $hoatDong = $u['trang_thai'] === 'hoat_dong';

        $lyDo = null;
        if (!$hoatDong) {
            $lyDo = 'Tài khoản đang bị khóa.';
        } elseif ($dangMuon >= SO_SACH_TOI_DA) {
            $lyDo = 'Đã mượn tối đa ' . SO_SACH_TOI_DA . ' cuốn.';
        } elseif ($coQuaHan) {
            $lyDo = 'Còn phiếu quá hạn chưa trả.';
        } elseif ($phi > 0) {
            $lyDo = 'Còn nợ phí phạt ' . tien($phi) . '.';
        }

        return [
            'id' => $id, 'ho_ten' => (string) $u['ho_ten'], 'ma' => (string) $u['ma_nguoi_dung'], 'khoa' => (string) ($u['khoa_lop'] ?? ''),
            'tai_khoan_hoat_dong' => $hoatDong, 'dang_muon' => $dangMuon, 'toi_da' => SO_SACH_TOI_DA,
            'co_qua_han' => $coQuaHan, 'phi_chua_thanh_toan' => $phi, 'du_dieu_kien' => $lyDo === null, 'ly_do' => $lyDo,
        ];
    }

    public static function tao(int $nguoiDungId, int $banSaoId, int $thuThuId, ?int $datChoId = null): int
    {
        CoSoDuLieu::truyVan(
            'INSERT INTO borrow_tickets (nguoi_dung_id, ban_sao_id, dat_cho_id, thu_thu_id, ngay_muon, han_tra)
             VALUES (?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ' . HAN_MUON_NGAY . ' DAY))',
            [$nguoiDungId, $banSaoId, $datChoId, $thuThuId]
        );
        return (int) CoSoDuLieu::ketNoi()->lastInsertId();
    }

    public static function hanTra(int $phieuId): string
    {
        return (string) CoSoDuLieu::truyVan('SELECT han_tra FROM borrow_tickets WHERE id = ?', [$phieuId])->fetchColumn();
    }

    /**
     * Đơn đặt chỗ đang chờ nhận theo id, KHÓA dòng. Gọi trong giaoDich(). null nếu không có / đã giao / đã hủy.
     * @return array{id:int, ma_dat_cho:string, nguoi_dung_id:int, ban_sao_id:int, ten_sach:string, het_han:string, da_het_han:bool}|null
     */
    public static function layDonDeGiao(int $datChoId): ?array
    {
        $r = CoSoDuLieu::truyVan(
            "SELECT r.id, CONCAT('DC-', LPAD(r.id, 4, '0')) AS ma_dat_cho, r.nguoi_dung_id, r.ban_sao_id, b.ten_sach,
                    r.het_han, (r.het_han <= NOW()) AS da_het_han
               FROM reservations r
               JOIN book_copies c ON c.id = r.ban_sao_id
               JOIN books b ON b.id = c.sach_id
              WHERE r.id = ? AND r.trang_thai = 'cho_nhan'
              FOR UPDATE",
            [$datChoId]
        )->fetch();
        if ($r === false) {
            return null;
        }
        foreach (['id', 'nguoi_dung_id', 'ban_sao_id'] as $cot) {
            $r[$cot] = (int) $r[$cot];
        }
        $r['da_het_han'] = (bool) $r['da_het_han'];
        return $r;
    }

    public static function danhDauDonDaGiao(int $datChoId): bool
    {
        return CoSoDuLieu::truyVan(
            "UPDATE reservations SET trang_thai = 'da_giao' WHERE id = ? AND trang_thai = 'cho_nhan'", [$datChoId]
        )->rowCount() > 0;
    }

    public static function danhDauDonHetHan(int $datChoId): bool
    {
        return CoSoDuLieu::truyVan(
            "UPDATE reservations SET trang_thai = 'het_han' WHERE id = ? AND trang_thai = 'cho_nhan'", [$datChoId]
        )->rowCount() > 0;
    }

    /**
     * Các đơn thủ thư vừa hủy trong phiên (để bảng hiện chữ "Đã hủy" tại dòng đó). Cùng dạng với DatCho::layDangCho()
     * cộng thêm cột trang_thai.
     * @param array<int, int> $ids
     * @return array<int, array<string, mixed>>
     */
    public static function layDonVuaHuy(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), static fn (int $v): bool => $v > 0));
        if ($ids === []) {
            return [];
        }
        return CoSoDuLieu::truyVan(
            "SELECT r.id, CONCAT('DC-', LPAD(r.id, 4, '0')) AS ma_dat_cho, CONCAT(u.ho_dem, ' ', u.ten) AS sinh_vien,
                    COALESCE(u.ma_nguoi_dung, '') AS ma_sv, b.ten_sach, r.het_han, r.trang_thai
               FROM reservations r
               JOIN users u ON u.id = r.nguoi_dung_id
               JOIN book_copies c ON c.id = r.ban_sao_id
               JOIN books b ON b.id = c.sach_id
              WHERE r.id IN (" . implode(',', $ids) . ") AND r.trang_thai IN ('het_han', 'da_huy')"
        )->fetchAll();
    }

    public static function dangMuonDauSach(int $nguoiDungId, int $sachId): bool
    {
        return (bool) CoSoDuLieu::truyVan(
            "SELECT 1 FROM borrow_tickets t JOIN book_copies c ON c.id = t.ban_sao_id
              WHERE t.nguoi_dung_id = ? AND c.sach_id = ? AND t.trang_thai = 'dang_muon' LIMIT 1",
            [$nguoiDungId, $sachId]
        )->fetchColumn();
    }

    /**
     * Đầu sách đang lưu hành cho ô "Tìm & chọn sách" (SR-08): số bản sẵn có / tổng, kệ, một bản sao gợi ý.
     * @return array<int, array{id:int, ma:string, ten:string, tac_gia:string, san_co:int, tong:int, ke:string, ban_sao_goi_y:?string}>
     */
    public static function layDanhSachSachMuon(): array
    {
        $ds = CoSoDuLieu::truyVan(
            "SELECT b.id, b.ma_sach AS ma, b.ten_sach AS ten, b.tac_gia, b.vi_tri_ke AS ke,
                    COALESCE(SUM(c.trang_thai = 'san_co'), 0) AS san_co, COUNT(c.id) AS tong,
                    MIN(CASE WHEN c.trang_thai = 'san_co' THEN c.ma_ban_sao END) AS ban_sao_goi_y
               FROM books b
               LEFT JOIN book_copies c ON c.sach_id = b.id
              WHERE b.trang_thai_luu_hanh = 'dang_luu_hanh'
              GROUP BY b.id, b.ma_sach, b.ten_sach, b.tac_gia, b.vi_tri_ke
              ORDER BY b.ma_sach"
        )->fetchAll();
        foreach ($ds as &$d) {
            $d['id'] = (int) $d['id'];
            $d['san_co'] = (int) $d['san_co'];
            $d['tong'] = (int) $d['tong'];
        }
        return $ds;
    }

    public static function sachDangLuuHanh(int $sachId): bool
    {
        return (bool) CoSoDuLieu::truyVan(
            "SELECT 1 FROM books WHERE id = ? AND trang_thai_luu_hanh = 'dang_luu_hanh' FOR UPDATE", [$sachId]
        )->fetchColumn();
    }

    /**
     * Bảng nền SR-09. Mặc định là phiếu ĐANG mượn, quá hạn nhất xếp trước.
     * $trangThai: null|'tat-ca' | 'dang-muon' (còn hạn) | 'qua-han' | 'da-tra' (lịch sử, mới trả nhất trước).
     * 'tat-ca' còn gồm các phiếu trong $vuaTraIds (vừa nhận trả trong phiên làm việc) để dòng đó đổi thành chữ
     * "Đã trả" ngay tại chỗ thay vì biến mất.
     * @param array<int, int> $vuaTraIds
     * @return array<int, array<string, mixed>> mỗi dòng: id, ma_phieu, sinh_vien, ma_sv, ten_sach, tac_gia, ma_ban_sao,
     *         gia_bia, ngay_muon, han_tra, trang_thai ('dang_muon'|'da_tra'), ngay_tra, con_no (đ còn nợ của phiếu)
     */
    public static function layDangMuon(?string $tuKhoa = null, ?string $trangThai = null, array $vuaTraIds = []): array
    {
        $tham = [];
        $vuaTra = array_values(array_filter(array_map('intval', $vuaTraIds), static fn (int $v): bool => $v > 0));
        $sapXep = 't.han_tra, t.id';
        $gioiHan = 200;

        if ($trangThai === 'da-tra') {
            $dieuKien = ["t.trang_thai = 'da_tra'"];
            $sapXep = 't.ngay_tra DESC, t.id DESC';
            $gioiHan = 50;
        } elseif ($trangThai === 'qua-han') {
            $dieuKien = ["t.trang_thai = 'dang_muon'", 't.han_tra < CURDATE()'];
        } elseif ($trangThai === 'dang-muon') {
            $dieuKien = ["t.trang_thai = 'dang_muon'", 't.han_tra >= CURDATE()'];
        } else {
            $dieuKien = [$vuaTra === []
                ? "t.trang_thai = 'dang_muon'"
                : "(t.trang_thai = 'dang_muon' OR t.id IN (" . implode(',', $vuaTra) . '))'];
        }

        $k = trim((string) $tuKhoa);
        if ($k !== '') {
            $mau = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $k) . '%';
            $dieuKien[] = "(CONCAT('PM-', t.id) LIKE :k1 OR u.ma_nguoi_dung LIKE :k2 OR CONCAT(u.ho_dem, ' ', u.ten) LIKE :k3
                            OR b.ten_sach LIKE :k4 OR c.ma_ban_sao LIKE :k5)";
            $tham += ['k1' => $mau, 'k2' => $mau, 'k3' => $mau, 'k4' => $mau, 'k5' => $mau];
        }
        $where = implode(' AND ', $dieuKien);

        $ds = CoSoDuLieu::truyVan(
            "SELECT t.id, CONCAT('PM-', t.id) AS ma_phieu, CONCAT(u.ho_dem, ' ', u.ten) AS sinh_vien,
                    COALESCE(u.ma_nguoi_dung, '') AS ma_sv, b.ten_sach, b.tac_gia, c.ma_ban_sao, b.gia_bia,
                    t.ngay_muon, t.han_tra, t.trang_thai, t.ngay_tra,
                    IF(f.da_thu = 0, f.phi_tre + f.phi_boi_hoan, 0) AS con_no
               FROM borrow_tickets t
               JOIN users u ON u.id = t.nguoi_dung_id
               JOIN book_copies c ON c.id = t.ban_sao_id
               JOIN books b ON b.id = c.sach_id
               LEFT JOIN fines f ON f.phieu_muon_id = t.id
              WHERE $where
              ORDER BY $sapXep
              LIMIT $gioiHan",
            $tham
        )->fetchAll();
        foreach ($ds as &$d) {
            $d['id'] = (int) $d['id'];
            $d['gia_bia'] = (int) $d['gia_bia'];
            $d['con_no'] = (int) $d['con_no'];
        }
        return $ds;
    }

    /**
     * Phiếu ĐANG mượn theo id, KHÓA dòng (FOR UPDATE) để hai thủ thư không cùng nhận trả một phiếu.
     * Gọi bên trong CoSoDuLieu::giaoDich(). null nếu không có hoặc đã trả.
     * @return array{id:int, ma_phieu:string, nguoi_dung_id:int, ban_sao_id:int, ngay_muon:string, han_tra:string, gia_bia:int, sinh_vien:string, ten_sach:string}|null
     */
    public static function layDeTra(int $id): ?array
    {
        $r = CoSoDuLieu::truyVan(
            "SELECT t.id, CONCAT('PM-', t.id) AS ma_phieu, t.nguoi_dung_id, t.ban_sao_id, t.ngay_muon, t.han_tra,
                    b.gia_bia, CONCAT(u.ho_dem, ' ', u.ten) AS sinh_vien, b.ten_sach
               FROM borrow_tickets t
               JOIN users u ON u.id = t.nguoi_dung_id
               JOIN book_copies c ON c.id = t.ban_sao_id
               JOIN books b ON b.id = c.sach_id
              WHERE t.id = ? AND t.trang_thai = 'dang_muon'
              FOR UPDATE",
            [$id]
        )->fetch();
        if ($r === false) {
            return null;
        }
        foreach (['id', 'nguoi_dung_id', 'ban_sao_id', 'gia_bia'] as $cot) {
            $r[$cot] = (int) $r[$cot];
        }
        return $r;
    }

    public static function danhDauDaTra(int $phieuId, string $ngayTra): bool
    {
        if ($ngayTra > date('Y-m-d')) {
            return false;
        }
        return CoSoDuLieu::truyVan(
            "UPDATE borrow_tickets SET trang_thai = 'da_tra', ngay_tra = ?
              WHERE id = ? AND trang_thai = 'dang_muon' AND ngay_muon <= ?",
            [$ngayTra, $phieuId, $ngayTra]
        )->rowCount() > 0;
    }
}
