<?php
declare(strict_types=1);

namespace ChucNang\KhoSach;

use Loi\CoSoDuLieu;

/** Bảng `books` (đầu sách). Mọi SQL về sách của khu quản trị viết ở đây. */
final class Sach
{
    /**
     * @param array{tu_khoa?:string, the_loai_id?:int, trang_thai?:string} $boLoc
     * @return array{danh_sach: array<int, array<string, mixed>>, tong: int}
     */
    public static function timKiem(array $boLoc, int $trang): array
    {
        // TODO (Trạng): kèm cột tổng bản / số bản san_co để hiện "Tổng / Sẵn có".
        return ['danh_sach' => [], 'tong' => 0];
    }

    /** @return array<string, mixed>|null */
    public static function timTheoId(int $id): ?array
    {
        // TODO (Trạng)
        return null;
    }

    /** Thêm mới (id = null) hoặc cập nhật. Trả về id sách. */
    public static function luu(?int $id, array $duLieu): int
    {
        // TODO (Trạng)
        return 0;
    }

    /** Đang lưu hành / Ngừng phục vụ (không xóa để giữ lịch sử giao dịch). */
    public static function doiTrangThaiLuuHanh(int $id, string $trangThai): bool
    {
        // TODO (Trạng)
        return false;
    }
}
