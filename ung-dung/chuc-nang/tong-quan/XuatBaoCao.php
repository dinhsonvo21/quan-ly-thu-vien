<?php
declare(strict_types=1);

namespace ChucNang\TongQuan;

/** Xuất báo cáo thống kê theo tháng (nút "Xuất báo cáo"). */
final class XuatBaoCao
{
    /** @return string đường dẫn file tạm (CSV/Excel/PDF) để controller cho tải về. */
    public static function taoFile(string $thang): string
    {
        // TODO (Trạng): bắt đầu bằng CSV (mở được bằng Excel); nâng cấp lên xlsx/pdf nếu còn thời gian.
        return '';
    }
}
