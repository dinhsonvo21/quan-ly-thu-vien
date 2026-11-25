<?php
declare(strict_types=1);

namespace ChucNang\KhoSach;

use Loi\CoSoDuLieu;

/** Bảng `books` (đầu sách). Mọi SQL về sách của khu quản trị viết ở đây. */
final class Sach
{
    private const SELECT = "SELECT b.*, c.ten_the_loai,
            COUNT(bc.id) AS tong_ban,
            COALESCE(SUM(bc.trang_thai = 'san_co'), 0)    AS san_co,
            COALESCE(SUM(bc.trang_thai = 'dang_muon'), 0) AS dang_muon,
            COALESCE(SUM(bc.trang_thai = 'dang_giu'), 0)  AS dang_giu
        FROM books b
        JOIN categories c ON c.id = b.the_loai_id
        LEFT JOIN book_copies bc ON bc.sach_id = b.id";

    /**
     * @param array{tu_khoa?:string, the_loai_id?:int, trang_thai?:string} $boLoc
     * @return array{danh_sach: array<int, array<string, mixed>>, tong: int, trang: int, so_trang: int}
     */
    public static function timKiem(array $boLoc, int $trang): array
    {
        $dieuKien = ['1 = 1'];
        $tham = [];

        $tuKhoa = trim((string) ($boLoc['tu_khoa'] ?? ''));
        if ($tuKhoa !== '') {
            $mau = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $tuKhoa) . '%';
            $dieuKien[] = '(b.ten_sach LIKE :k1 OR b.ma_sach LIKE :k2 OR b.tac_gia LIKE :k3 OR b.isbn LIKE :k4)';
            $tham += ['k1' => $mau, 'k2' => $mau, 'k3' => $mau, 'k4' => $mau];
        }
        if ((int) ($boLoc['the_loai_id'] ?? 0) > 0) {
            $dieuKien[] = 'b.the_loai_id = :tl';
            $tham['tl'] = (int) $boLoc['the_loai_id'];
        }
        if (in_array($boLoc['trang_thai'] ?? '', ['dang_luu_hanh', 'ngung_phuc_vu'], true)) {
            $dieuKien[] = 'b.trang_thai_luu_hanh = :tt';
            $tham['tt'] = $boLoc['trang_thai'];
        }
        $where = implode(' AND ', $dieuKien);

        $tong = (int) CoSoDuLieu::truyVan("SELECT COUNT(*) FROM books b WHERE $where", $tham)->fetchColumn();
        $soTrang = max(1, (int) ceil($tong / SO_SACH_MOI_TRANG));
        $trang = min(max(1, $trang), $soTrang);
        $viTri = ($trang - 1) * SO_SACH_MOI_TRANG;

        $ds = CoSoDuLieu::truyVan(
            self::SELECT . " WHERE $where GROUP BY b.id, c.ten_the_loai
              ORDER BY b.ma_sach LIMIT " . SO_SACH_MOI_TRANG . " OFFSET $viTri",
            $tham
        )->fetchAll();

        return [
            'danh_sach' => array_map([self::class, 'chuanKieu'], $ds),
            'tong'      => $tong,
            'trang'     => $trang,
            'so_trang'  => $soTrang,
        ];
    }

    /** @return array<string, mixed>|null */
    public static function timTheoId(int $id): ?array
    {
        $r = CoSoDuLieu::truyVan(
            self::SELECT . ' WHERE b.id = ? GROUP BY b.id, c.ten_the_loai',
            [$id]
        )->fetch();
        return $r === false ? null : self::chuanKieu($r);
    }

    /** Số liệu đầu trang: "1.248 đầu sách · 12.480 bản sao". @return array{dau_sach:int, ban_sao:int} */
    public static function tongQuan(): array
    {
        $r = CoSoDuLieu::truyVan(
            'SELECT (SELECT COUNT(*) FROM books) AS dau_sach, (SELECT COUNT(*) FROM book_copies) AS ban_sao'
        )->fetch();
        return ['dau_sach' => (int) $r['dau_sach'], 'ban_sao' => (int) $r['ban_sao']];
    }

    /**
     * Thêm mới (id = null, mã sách tự sinh BK-xxx) hoặc cập nhật. Trả về id sách.
     * $duLieu: isbn, ten_sach, tac_gia, nha_xuat_ban, nam_xuat_ban, ngon_ngu, loai_tai_lieu, mo_ta,
     *          gia_bia, vi_tri_ke, trang_thai_luu_hanh, the_loai_id, anh_bia (null = giữ ảnh cũ).
     */
    public static function luu(?int $id, array $duLieu): int
    {
        $cot = ['isbn', 'ten_sach', 'tac_gia', 'nha_xuat_ban', 'nam_xuat_ban', 'ngon_ngu', 'loai_tai_lieu',
                'mo_ta', 'gia_bia', 'vi_tri_ke', 'trang_thai_luu_hanh', 'the_loai_id'];
        $tham = [];
        foreach ($cot as $c) {
            $tham[$c] = $duLieu[$c] ?? null;
        }
        if (($duLieu['anh_bia'] ?? null) !== null) {
            $cot[] = 'anh_bia';
            $tham['anh_bia'] = $duLieu['anh_bia'];
        }

        if ($id !== null) {
            $set = implode(', ', array_map(static fn(string $c) => "$c = :$c", $cot));
            $tham['id'] = $id;
            CoSoDuLieu::truyVan("UPDATE books SET $set WHERE id = :id", $tham);
            return $id;
        }

        $cot[] = 'ma_sach';
        $tham['ma_sach'] = self::maSachTiepTheo();
        $dsCot = implode(', ', $cot);
        $dsTen = implode(', ', array_map(static fn(string $c) => ':' . $c, $cot));
        CoSoDuLieu::truyVan("INSERT INTO books ($dsCot) VALUES ($dsTen)", $tham);
        return (int) CoSoDuLieu::ketNoi()->lastInsertId();
    }

    /** Đang lưu hành / Ngừng phục vụ (không xóa để giữ lịch sử giao dịch). */
    public static function doiTrangThaiLuuHanh(int $id, string $trangThai): bool
    {
        if (!in_array($trangThai, ['dang_luu_hanh', 'ngung_phuc_vu'], true)) {
            return false;
        }
        return CoSoDuLieu::truyVan(
            'UPDATE books SET trang_thai_luu_hanh = ? WHERE id = ?',
            [$trangThai, $id]
        )->rowCount() > 0;
    }

    private static function maSachTiepTheo(): string
    {
        $so = (int) CoSoDuLieu::truyVan(
            "SELECT COALESCE(MAX(CAST(SUBSTRING(ma_sach, 4) AS UNSIGNED)), 0) + 1
               FROM books WHERE ma_sach REGEXP '^BK-[0-9]+$'"
        )->fetchColumn();
        return sprintf('BK-%03d', $so);
    }

    /** @param array<string, mixed> $r @return array<string, mixed> */
    private static function chuanKieu(array $r): array
    {
        foreach (['id', 'the_loai_id', 'gia_bia', 'tong_ban', 'san_co', 'dang_muon', 'dang_giu'] as $k) {
            $r[$k] = (int) $r[$k];
        }
        return $r;
    }
}