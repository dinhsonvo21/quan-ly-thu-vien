<?php
declare(strict_types=1);

namespace ChucNang\TraCuu;

use Loi\CoSoDuLieu;

/** Truy vấn tra cứu sách cho khách / bạn đọc (chỉ đọc) + bảng `reading_lists`. */
final class MoHinh
{
    /**
     * @param array{tu_khoa?:string, the_loai_id?:int, loai_tai_lieu?:string, trang_thai?:string, sap_xep?:string} $boLoc
     * @return array{danh_sach: array<int, array<string, mixed>>, tong: int}
     */
    public static function timKiem(array $boLoc, int $trang): array
    {
        // TODO (Sơn): LIMIT SO_SACH_MOI_TRANG; trang_thai 'con_sach' = có >= 1 bản sao san_co.
        return ['danh_sach' => [], 'tong' => 0];
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
