<?php
declare(strict_types=1);

namespace Loi;

/**
 * Kiểm tra quyền Ở PHÍA MÁY CHỦ (báo cáo mục 3.3) — theo VAI TRÒ, không theo thư mục.
 *
 * Quyền được khai báo cùng route (xem route.php của từng chức năng) và Router
 * kiểm tra trước khi gọi controller. Controller nhạy cảm có thể gọi lại
 * yeuCauVaiTro() như lớp bảo vệ thứ hai.
 */
final class PhanQuyen
{
    /** Chỉ cần đã đăng nhập, vai trò nào cũng được. */
    public const BAT_KY = ['*'];
    /** Thủ thư + Quản trị viên: SR-06, SR-07, SR-08, SR-09. */
    public const NHAN_VIEN = [VAI_TRO_THU_THU, VAI_TRO_QUAN_TRI];
    /** Chỉ Quản trị viên: SR-10. */
    public const CHI_QUAN_TRI = [VAI_TRO_QUAN_TRI];

    /** @param array<int, string> $vaiTro */
    public static function choPhep(array $vaiTro): bool
    {
        $nguoiDung = PhienLamViec::nguoiDung();
        if ($nguoiDung === null) {
            return false;
        }
        return $vaiTro === self::BAT_KY || in_array($nguoiDung['vai_tro'], $vaiTro, true);
    }

    public static function yeuCauDangNhap(): void
    {
        self::yeuCauVaiTro(self::BAT_KY);
    }

    /** @param array<int, string> $vaiTro */
    public static function yeuCauVaiTro(array $vaiTro): void
    {
        if (!self::choPhep($vaiTro)) {
            self::tuChoi();
        }
    }

    /** Chưa đăng nhập -> về trang đăng nhập; đã đăng nhập nhưng sai vai trò -> 403. */
    public static function tuChoi(): never
    {
        if (PhienLamViec::nguoiDung() === null) {
            PhienLamViec::flash('thong_tin', 'Vui lòng đăng nhập để tiếp tục.');
            header('Location: ' . url('/dang-nhap'));
            exit;
        }
        TrangLoi::hien(403);
    }
}
