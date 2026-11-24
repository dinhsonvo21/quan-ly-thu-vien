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
        $sach = CoSoDuLieu::truyVan(
            "SELECT b.id, b.ma_sach, b.isbn, b.ten_sach, b.tac_gia, b.nha_xuat_ban,
                    b.nam_xuat_ban, b.ngon_ngu, b.loai_tai_lieu, b.so_trang, b.mo_ta,
                    b.vi_tri_ke, b.anh_bia, c.ten_the_loai,
                    (SELECT COUNT(*) FROM book_copies bc WHERE bc.sach_id = b.id) AS tong_ban_sao,
                    (SELECT COUNT(*) FROM book_copies bc
                     WHERE bc.sach_id = b.id AND bc.trang_thai = 'san_co') AS ban_sao_san_co
             FROM books b
             INNER JOIN categories c ON c.id = b.the_loai_id
             WHERE b.id = :id AND b.trang_thai_luu_hanh = 'dang_luu_hanh'
             LIMIT 1",
            ['id' => $sachId]
        )->fetch();

        if ($sach === false) {
            return null;
        }

        $sach['ban_sao'] = CoSoDuLieu::truyVan(
            'SELECT ma_ban_sao, trang_thai
             FROM book_copies
             WHERE sach_id = :sach_id
             ORDER BY ma_ban_sao',
            ['sach_id' => $sachId]
        )->fetchAll();

        return $sach;
    }

    public static function themDanhSachDoc(int $nguoiDungId, int $sachId): bool
    {
        try {
            CoSoDuLieu::truyVan(
                'INSERT INTO reading_lists (nguoi_dung_id, sach_id)
                 VALUES (:nguoi_dung_id, :sach_id)',
                ['nguoi_dung_id' => $nguoiDungId, 'sach_id' => $sachId]
            );
            return true;
        } catch (\PDOException $ngoaiLe) {
            if ((int) ($ngoaiLe->errorInfo[1] ?? 0) !== 1062) {
                throw $ngoaiLe;
            }
            return false;
        }
    }

    public static function coTrongDanhSachDoc(int $nguoiDungId, int $sachId): bool
    {
        return CoSoDuLieu::truyVan(
            'SELECT 1
             FROM reading_lists
             WHERE nguoi_dung_id = :nguoi_dung_id AND sach_id = :sach_id
             LIMIT 1',
            ['nguoi_dung_id' => $nguoiDungId, 'sach_id' => $sachId]
        )->fetchColumn() !== false;
    }

    /** @return array<int, array<string, mixed>> */
    public static function layDanhSachDoc(int $nguoiDungId): array
    {
        return CoSoDuLieu::truyVan(
            'SELECT b.id, b.ma_sach, b.ten_sach, b.tac_gia, b.anh_bia, c.ten_the_loai
             FROM reading_lists rl
             INNER JOIN books b ON b.id = rl.sach_id
             INNER JOIN categories c ON c.id = b.the_loai_id
             WHERE rl.nguoi_dung_id = :nguoi_dung_id
             ORDER BY rl.ngay_them DESC, rl.id DESC',
            ['nguoi_dung_id' => $nguoiDungId]
        )->fetchAll();
    }
}
