<?php
declare(strict_types=1);

namespace ChucNang\TraCuu;

use Loi\CoSoDuLieu;

/** Truy vấn tra cứu sách cho khách / bạn đọc (chỉ đọc) + bảng `reading_lists`. */
final class MoHinh
{
    /** @return array<int, array<string, mixed>> */
    public static function layDanhSachTheLoai(): array
    {
        return CoSoDuLieu::truyVan(
            'SELECT id, ten_the_loai
             FROM categories
             ORDER BY ten_the_loai ASC'
        )->fetchAll();
    }

    /**
     * @param array<string, mixed> $boLoc
     * @return array{danh_sach: array<int, array<string, mixed>>, tong_so: int, tong: int}
     */
    public static function timKiemSach(array $boLoc, int $trang = 1, int $soLuongMoiTrang = 8): array
    {
        $trang = max(1, $trang);
        $soLuongMoiTrang = max(1, $soLuongMoiTrang);
        $dieuKien = ["b.trang_thai_luu_hanh = 'dang_luu_hanh'"];
        $thamSo = [];

        $q = trim((string) ($boLoc['q'] ?? ''));
        if ($q !== '') {
            $dieuKien[] = '(b.ten_sach LIKE ? OR b.tac_gia LIKE ? OR b.isbn LIKE ?)';
            $tuKhoa = '%' . $q . '%';
            $thamSo[] = $tuKhoa;
            $thamSo[] = $tuKhoa;
            $thamSo[] = $tuKhoa;
        }

        $theLoai = trim((string) ($boLoc['the_loai'] ?? ''));
        if ($theLoai !== '') {
            if (ctype_digit($theLoai)) {
                $dieuKien[] = 'c.id = ?';
            } else {
                $dieuKien[] = 'c.ten_the_loai = ?';
            }
            $thamSo[] = $theLoai;
        }

        $loaiTaiLieu = trim((string) ($boLoc['loai_tai_lieu'] ?? ''));
        if ($loaiTaiLieu !== '') {
            $dieuKien[] = 'b.loai_tai_lieu = ?';
            $thamSo[] = $loaiTaiLieu;
        }

        $trangThai = trim((string) ($boLoc['trang_thai'] ?? ''));
        if ($trangThai === 'con_sach') {
            $dieuKien[] = "EXISTS (SELECT 1 FROM book_copies bc
                                     WHERE bc.sach_id = b.id AND bc.trang_thai = 'san_co')";
        } elseif ($trangThai === 'dang_muon') {
            $dieuKien[] = "NOT EXISTS (SELECT 1 FROM book_copies bc
                                     WHERE bc.sach_id = b.id AND bc.trang_thai = 'san_co')
                           AND EXISTS (SELECT 1 FROM book_copies bc
                                     WHERE bc.sach_id = b.id)";
        }

        $where = implode(' AND ', $dieuKien);
        $tongSo = (int) CoSoDuLieu::truyVan(
            'SELECT COUNT(*)
             FROM books b
             INNER JOIN categories c ON c.id = b.the_loai_id
             WHERE ' . $where,
            $thamSo
        )->fetchColumn();

        $sapXep = (string) ($boLoc['sap_xep'] ?? 'moi_nhat');
        $orderBy = match ($sapXep) {
            'ten_az' => 'b.ten_sach ASC',
            'nam_xb' => 'b.nam_xuat_ban DESC, b.ngay_tao DESC',
            default => 'b.ngay_tao DESC, b.id DESC',
        };

        $thamSoDanhSach = $thamSo;
        $thamSoDanhSach[] = $soLuongMoiTrang;
        $thamSoDanhSach[] = ($trang - 1) * $soLuongMoiTrang;

        $danhSach = CoSoDuLieu::truyVan(
            'SELECT b.id, b.ma_sach, b.isbn, b.ten_sach, b.tac_gia,
                    b.nha_xuat_ban, b.nam_xuat_ban, b.loai_tai_lieu,
                    b.anh_bia, b.ngay_tao, c.id AS the_loai_id, c.ten_the_loai,
                    EXISTS (
                        SELECT 1 FROM book_copies bc
                        WHERE bc.sach_id = b.id AND bc.trang_thai = \'san_co\'
                    ) AS con_sach,
                    (SELECT COUNT(*) FROM book_copies bc WHERE bc.sach_id = b.id) AS tong_ban_sao,
                    (SELECT COUNT(*) FROM book_copies bc
                     WHERE bc.sach_id = b.id AND bc.trang_thai = \'san_co\') AS ban_sao_san_co
             FROM books b
             INNER JOIN categories c ON c.id = b.the_loai_id
             WHERE ' . $where . '
             ORDER BY ' . $orderBy . '
             LIMIT ? OFFSET ?',
            $thamSoDanhSach
        )->fetchAll();

        return [
            'danh_sach' => $danhSach,
            'tong_so' => $tongSo,
            'tong' => $tongSo,
        ];
    }

    /**
     * @param array{tu_khoa?:string, the_loai_id?:int, loai_tai_lieu?:string, trang_thai?:string, sap_xep?:string} $boLoc
     * @return array{danh_sach: array<int, array<string, mixed>>, tong: int}
     */
    public static function timKiem(array $boLoc, int $trang): array
    {
        $boLocMoi = $boLoc;
        if (isset($boLoc['tu_khoa'])) {
            $boLocMoi['q'] = $boLoc['tu_khoa'];
        }
        if (isset($boLoc['the_loai_id'])) {
            $boLocMoi['the_loai'] = (string) $boLoc['the_loai_id'];
        }
        $ketQua = self::timKiemSach($boLocMoi, $trang, SO_SACH_MOI_TRANG);
        return ['danh_sach' => $ketQua['danh_sach'], 'tong' => $ketQua['tong_so']];
    }

    /** @return array<string, mixed>|null */
    public static function chiTiet(int $sachId): ?array
    {
        // TODO (Sơn)
        return null;
    }

    public static function themDanhSachDoc(int $nguoiDungId, int $sachId): bool
    {
        // TODO (Sơn): INSERT ... (cặp người dùng + sách là duy nhất)
        return false;
    }

    /** @return array<int, array<string, mixed>> */
    public static function layDanhSachDoc(int $nguoiDungId): array
    {
        // TODO (Sơn)
        return [];
    }
}
