<?php
declare(strict_types=1);

namespace Loi;

/** Kiểm tra dữ liệu nhập. Mỗi hàm trả true nếu hợp lệ. */
final class KiemTra
{
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /** Tối thiểu MAT_KHAU_TOI_THIEU ký tự, có cả chữ và số (SR-02, SR-05). */
    public static function matKhau(string $matKhau): bool
    {
        return strlen($matKhau) >= MAT_KHAU_TOI_THIEU
            && preg_match('/[A-Za-z]/', $matKhau) === 1
            && preg_match('/\d/', $matKhau) === 1;
    }

    /** Ngày dạng Y-m-d có thật trên lịch. */
    public static function ngay(string $ngay): bool
    {
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $ngay);
        return $d !== false && $d->format('Y-m-d') === $ngay;
    }

    public static function khongRong(?string $chuoi): bool
    {
        return $chuoi !== null && trim($chuoi) !== '';
    }
}
