<?php
declare(strict_types=1);

namespace Loi;

/** Đọc file trong ung-dung/cau-hinh/:  CauHinh::lay('ung-dung', 'mui_gio') */
final class CauHinh
{
    /** @var array<string, array<string, mixed>> */
    private static array $boNho = [];

    public static function lay(string $tep, string $khoa, mixed $macDinh = null): mixed
    {
        if (!isset(self::$boNho[$tep])) {
            $duongDan = UNG_DUNG . '/cau-hinh/' . $tep . '.php';
            self::$boNho[$tep] = is_file($duongDan) ? (array) require $duongDan : [];
        }
        return self::$boNho[$tep][$khoa] ?? $macDinh;
    }
}
