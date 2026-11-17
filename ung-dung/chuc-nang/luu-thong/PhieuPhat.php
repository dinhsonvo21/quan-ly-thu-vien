<?php
declare(strict_types=1);

namespace ChucNang\LuuThong;

use Loi\CoSoDuLieu;

/** Bảng `fines`. `da_thu` = false cho phép ghi nợ (SR-05: "hoàn tất phí quá hạn trước khi mượn thêm"). */
final class PhieuPhat
{
    /**
     * @param array{phieu_muon_id:int, so_ngay_tre:int, phi_tre:int, tinh_trang_sach:string, phi_boi_hoan:int, da_thu:bool} $duLieu
     */
    public static function ghi(array $duLieu): int
    {
        // TODO (Quân)
        return 0;
    }

    /** Tổng phí chưa thu của một bạn đọc (chặn mượn nếu > 0). */
    public static function tongChuaThu(int $nguoiDungId): int
    {
        // TODO (Quân)
        return 0;
    }
}
