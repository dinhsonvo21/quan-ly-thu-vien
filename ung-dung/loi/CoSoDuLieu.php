<?php
declare(strict_types=1);

namespace Loi;

use PDO;

/**
 * Kết nối PDO dùng chung (tạo khi cần — trang không đụng DB vẫn chạy được).
 *
 *   $dong = CoSoDuLieu::truyVan('SELECT * FROM users WHERE email = ?', [$email])->fetch();
 *
 *   // Mọi thao tác đổi trạng thái bản sao / phiếu mượn PHẢI nằm trong giaoDich():
 *   CoSoDuLieu::giaoDich(function (PDO $pdo) { ... SELECT ... FOR UPDATE ... });
 */
final class CoSoDuLieu
{
    private static ?PDO $pdo = null;

    public static function ketNoi(): PDO
    {
        if (self::$pdo === null) {
            $c = [];
            foreach (['host', 'port', 'ten_csdl', 'nguoi_dung', 'mat_khau', 'charset'] as $khoa) {
                $c[$khoa] = CauHinh::lay('csdl', $khoa);
            }
            if ($c['host'] === null) {
                throw new \RuntimeException(
                    'Chưa có ung-dung/cau-hinh/csdl.php — hãy sao chép csdl.mau.php thành csdl.php rồi sửa thông số.'
                );
            }
            $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['ten_csdl']};charset={$c['charset']}";
            self::$pdo = new PDO($dsn, (string) $c['nguoi_dung'], (string) $c['mat_khau'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }

    /** @param array<int|string, mixed> $thamSo */
    public static function truyVan(string $sql, array $thamSo = []): \PDOStatement
    {
        $cau = self::ketNoi()->prepare($sql);
        $cau->execute($thamSo);
        return $cau;
    }

    /**
     * Chạy $viec trong một transaction; lỗi thì tự rollback rồi ném lại ngoại lệ.
     * @param callable(PDO): mixed $viec
     */
    public static function giaoDich(callable $viec): mixed
    {
        $pdo = self::ketNoi();
        if ($pdo->inTransaction()) {
            return $viec($pdo);
        }
        $pdo->beginTransaction();
        try {
            $ketQua = $viec($pdo);
            $pdo->commit();
            return $ketQua;
        } catch (\Throwable $loi) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $loi;
        }
    }
}
