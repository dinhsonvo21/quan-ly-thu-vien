<?php
declare(strict_types=1);

namespace ChucNang\LuuThong;

/**
 * Công thức phạt theo báo cáo 2.2.4:
 *   Phí quá hạn = (Ngày trả thực tế − Hạn trả) × 2.000 đ / ngày / cuốn
 * Hàm thuần (không đụng CSDL) nên dễ kiểm thử. Dashboard (SR-06) cũng gọi hàm này.
 */
final class TinhPhiPhat
{
    /** Ngày dạng 'Y-m-d' (hoặc có giờ — chỉ lấy phần ngày). Không quá hạn -> 0. */
    public static function soNgayTre(string $hanTra, string $ngayTra): int
    {
        $han = new \DateTimeImmutable(substr($hanTra, 0, 10));
        $tra = new \DateTimeImmutable(substr($ngayTra, 0, 10));
        return $tra > $han ? (int) $han->diff($tra)->days : 0;
    }

    public static function phiQuaHan(string $hanTra, string $ngayTra): int
    {
        return self::soNgayTre($hanTra, $ngayTra) * PHI_PHAT_MOI_NGAY;
    }

    /**
     * Phí bồi hoàn theo tình trạng sách khi trả: 'binh_thuong' | 'hong' | 'mat'.
     * Tỉ lệ nằm ở cau-hinh/quy-dinh.php (TODO: nhóm chốt cách tính — giá bìa hay nhập tay).
     */
    public static function phiBoiHoan(string $tinhTrang, int $giaBia): int
    {
        return match ($tinhTrang) {
            'hong'  => (int) round($giaBia * TY_LE_BOI_HOAN_HONG),
            'mat'   => (int) round($giaBia * TY_LE_BOI_HOAN_MAT),
            default => 0,
        };
    }
}
