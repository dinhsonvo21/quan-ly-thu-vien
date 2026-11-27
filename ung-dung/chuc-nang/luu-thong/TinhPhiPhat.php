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

    /**
     * Ngày trả hợp lệ: đúng định dạng Y-m-d, không ở tương lai, không trước ngày mượn.
     * Server luôn kiểm tra lại, không tin ô nhập từ trình duyệt.
     */
    public static function ngayTraHopLe(string $ngayMuon, string $ngayTra, ?string $homNay = null): bool
    {
        $mau = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($mau, $ngayTra) || !preg_match($mau, substr($ngayMuon, 0, 10))) {
            return false;
        }
        $tra = \DateTimeImmutable::createFromFormat('!Y-m-d', $ngayTra);
        if ($tra === false || $tra->format('Y-m-d') !== $ngayTra) {
            return false;
        }
        $homNay ??= date('Y-m-d');
        return $ngayTra <= $homNay && $ngayTra >= substr($ngayMuon, 0, 10);
    }

    /**
     * Tổng cần thu khi trả sách = phí quá hạn + phí bồi hoàn. SERVER gọi để tự tính lại;
     * số JS hiển thị chỉ để xem trước.
     * @return array{so_ngay_tre:int, phi_tre:int, phi_boi_hoan:int, tong:int}
     */
    public static function tongCanThu(string $hanTra, string $ngayTra, string $tinhTrang, int $giaBia): array
    {
        $soNgay = self::soNgayTre($hanTra, $ngayTra);
        $phiTre = $soNgay * PHI_PHAT_MOI_NGAY;
        $boiHoan = self::phiBoiHoan($tinhTrang, $giaBia);
        return ['so_ngay_tre' => $soNgay, 'phi_tre' => $phiTre, 'phi_boi_hoan' => $boiHoan, 'tong' => $phiTre + $boiHoan];
    }
}
