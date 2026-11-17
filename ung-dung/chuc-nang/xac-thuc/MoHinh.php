<?php
declare(strict_types=1);

namespace ChucNang\XacThuc;

use Loi\CoSoDuLieu;

/** Truy vấn bảng `users` phục vụ đăng ký / đăng nhập. SQL chỉ viết ở file này. */
final class MoHinh
{
    /** @return array<string, mixed>|null */
    public static function timTheoEmail(string $email): ?array
    {
        // TODO (Sơn)
        return null;
    }

    /** Tạo tài khoản bạn đọc; $matKhauBam là kết quả password_hash(). Trả về id mới. */
    public static function taoBanDoc(string $hoDem, string $ten, string $email, string $matKhauBam): int
    {
        // TODO (Sơn)
        return 0;
    }

    public static function emailDaTonTai(string $email): bool
    {
        // TODO (Sơn)
        return false;
    }
}
