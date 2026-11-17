<?php
declare(strict_types=1);

namespace ChucNang\KhoSach;

use Loi\CoSoDuLieu;

/** Bảng `categories`. */
final class TheLoai
{
    /** @return array<int, array{id:int, ten_the_loai:string}> */
    public static function tatCa(): array
    {
        // TODO (Trạng)
        return [];
    }

    public static function luu(?int $id, string $ten): int
    {
        // TODO (Trạng)
        return 0;
    }

    public static function xoa(int $id): bool
    {
        // TODO (Trạng)
        return false;
    }
}
