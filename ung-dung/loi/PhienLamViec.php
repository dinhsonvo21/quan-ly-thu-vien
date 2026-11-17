<?php
declare(strict_types=1);

namespace Loi;

/**
 * Session, thông báo flash, CSRF.
 *
 * Người dùng đang đăng nhập (PhienLamViec::nguoiDung()) có dạng:
 *   ['id' => 1, 'ma' => 'SV2024001', 'ho_ten' => 'Nguyễn Văn A',
 *    'email' => '...', 'vai_tro' => 'ban_doc'|'thu_thu'|'quan_tri']
 */
final class PhienLamViec
{
    public static function batDau(): void
    {
        if (PHP_SAPI === 'cli' || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_name('QNUSESSID');
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']),
        ]);
        session_start();
    }

    /** @return array<string, mixed>|null */
    public static function nguoiDung(): ?array
    {
        return $_SESSION['nguoi_dung'] ?? null;
    }

    /** @param array<string, mixed> $nguoiDung */
    public static function dangNhap(array $nguoiDung): void
    {
        session_regenerate_id(true);
        $_SESSION['nguoi_dung'] = [
            'id'      => (int) $nguoiDung['id'],
            'ma'      => (string) $nguoiDung['ma'],
            'ho_ten'  => (string) $nguoiDung['ho_ten'],
            'email'   => (string) $nguoiDung['email'],
            'vai_tro' => (string) $nguoiDung['vai_tro'],
        ];
    }

    public static function dangXuat(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    /** $loai: thanh_cong | loi | thong_tin */
    public static function flash(string $loai, string $noiDung): void
    {
        $_SESSION['flash'][] = ['loai' => $loai, 'noi_dung' => $noiDung];
    }

    /** @return array<int, array{loai:string, noi_dung:string}> */
    public static function layFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    public static function csrf(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function kiemTraCsrf(): bool
    {
        $token = $_POST['_csrf'] ?? '';
        return is_string($token) && hash_equals(self::csrf(), $token);
    }
}
