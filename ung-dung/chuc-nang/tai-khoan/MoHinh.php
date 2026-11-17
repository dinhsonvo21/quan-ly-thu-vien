<?php
declare(strict_types=1);

namespace ChucNang\TaiKhoan;

use Loi\CoSoDuLieu;

/** Bảng `users` ở góc nhìn quản trị. ID hiển thị (SV2024001 / TT-001 / QT-001) là cột `ma_nguoi_dung`. */
final class MoHinh
{
    /** @return array{tong:int, ban_doc:int, thu_thu:int, quan_tri:int} 4 thẻ thống kê đầu trang */
    public static function thongKe(): array
    {
        // TODO (Quân)
        return ['tong' => 0, 'ban_doc' => 0, 'thu_thu' => 0, 'quan_tri' => 0];
    }

    /**
     * @param array{tu_khoa?:string, vai_tro?:string, trang_thai?:string} $boLoc
     * @return array{danh_sach: array<int, array<string, mixed>>, tong: int}
     */
    public static function timKiem(array $boLoc, int $trang): array
    {
        // TODO (Quân)
        return ['danh_sach' => [], 'tong' => 0];
    }

    public static function taoThuThu(string $hoTen, string $email, string $matKhauBam): int
    {
        // TODO (Quân): sinh mã TT-xxx.
        return 0;
    }

    public static function doiVaiTro(int $id, string $vaiTro): bool
    {
        // TODO (Quân)
        return false;
    }

    /** $trangThai: 'hoat_dong' | 'tam_khoa' */
    public static function doiTrangThai(int $id, string $trangThai): bool
    {
        // TODO (Quân)
        return false;
    }
}
