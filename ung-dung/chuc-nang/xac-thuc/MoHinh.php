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
        $dong = CoSoDuLieu::truyVan(
            'SELECT id, ma_nguoi_dung, ho_dem, ten, email, mat_khau_bam, vai_tro, trang_thai
             FROM users
             WHERE email = :email
             LIMIT 1',
            ['email' => $email]
        )->fetch();

        return $dong === false ? null : $dong;
    }

    /** Tạo tài khoản bạn đọc; $matKhauBam là kết quả password_hash(). Trả về id mới. */
    public static function taoBanDoc(string $hoDem, string $ten, string $email, string $matKhauBam): int
    {
        return CoSoDuLieu::giaoDich(static function (\PDO $pdo) use ($hoDem, $ten, $email, $matKhauBam): int {
            $cau = $pdo->prepare(
                'INSERT INTO users (ho_dem, ten, email, mat_khau_bam, vai_tro, trang_thai)
                 VALUES (:ho_dem, :ten, :email, :mat_khau_bam, :vai_tro, :trang_thai)'
            );
            $cau->execute([
                'ho_dem' => $hoDem,
                'ten' => $ten,
                'email' => $email,
                'mat_khau_bam' => $matKhauBam,
                'vai_tro' => 'ban_doc',
                'trang_thai' => 'hoat_dong',
            ]);

            $id = (int) $pdo->lastInsertId();
            $maNguoiDung = 'SV' . date('Y') . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
            $capNhat = $pdo->prepare('UPDATE users SET ma_nguoi_dung = :ma WHERE id = :id');
            $capNhat->execute(['ma' => $maNguoiDung, 'id' => $id]);

            return $id;
        });
    }

    public static function emailDaTonTai(string $email): bool
    {
        return CoSoDuLieu::truyVan(
            'SELECT 1 FROM users WHERE email = :email LIMIT 1',
            ['email' => $email]
        )->fetchColumn() !== false;
    }

    public static function luuMaOtp(string $email, string $maOtp): bool
    {
        return CoSoDuLieu::truyVan(
            'UPDATE users
             SET reset_token = :reset_token,
                 reset_expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE)
             WHERE email = :email',
            ['reset_token' => $maOtp, 'email' => $email]
        )->rowCount() === 1;
    }

    public static function xacThucOtp(string $email, string $maOtp): bool
    {
        return CoSoDuLieu::truyVan(
            'SELECT 1
             FROM users
             WHERE email = :email
               AND reset_token = :reset_token
               AND reset_expires_at > NOW()
             LIMIT 1',
            ['email' => $email, 'reset_token' => $maOtp]
        )->fetchColumn() !== false;
    }

    public static function capNhatMatKhauMoi(string $email, string $maOtp, string $matKhauBam): bool
    {
        return CoSoDuLieu::truyVan(
            'UPDATE users
             SET mat_khau_bam = :mat_khau_bam,
                 reset_token = NULL,
                 reset_expires_at = NULL
             WHERE email = :email
               AND reset_token = :reset_token
               AND reset_expires_at > NOW()',
            [
                'mat_khau_bam' => $matKhauBam,
                'email' => $email,
                'reset_token' => $maOtp,
            ]
        )->rowCount() === 1;
    }
}
