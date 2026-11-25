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
        return CoSoDuLieu::truyVan('SELECT id, ten_the_loai FROM categories ORDER BY ten_the_loai')->fetchAll();
    }

    public static function tonTai(int $id): bool
    {
        return CoSoDuLieu::truyVan('SELECT 1 FROM categories WHERE id = ?', [$id])->fetchColumn() !== false;
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