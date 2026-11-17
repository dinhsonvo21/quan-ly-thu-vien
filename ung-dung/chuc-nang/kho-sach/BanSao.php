<?php
declare(strict_types=1);

namespace ChucNang\KhoSach;

use Loi\CoSoDuLieu;

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
        // STUB: dữ liệu giả — thay bằng SQL thật.
        return ['id' => 1, 'ma_ban_sao' => 'BK-002-05', 'sach_id' => $sachId, 'trang_thai' => BS_SAN_CO];
    }

    /** $trangThai: BS_SAN_CO | BS_DANG_GIU | BS_DANG_MUON | BS_HONG | BS_MAT */
    public static function doiTrangThai(int $banSaoId, string $trangThai): bool
    {
        // TODO (Trạng)
        return false;
    }

    /** Số bản đang san_co của một đầu sách (nhãn "Còn sách", "Còn 3/5 bản"). */
    public static function demSanCo(int $sachId): int
    {
        // STUB
        return 3;
    }

    /** Tạo thêm $soLuong bản sao, tự sinh mã BK-xxx-nn. */
    public static function taoBanSao(int $sachId, int $soLuong): bool
    {
        // TODO (Trạng)
        return false;
    }
}
