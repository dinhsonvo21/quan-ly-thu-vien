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

    /**
     * Kèm số đầu sách thuộc thể loại (trang Quản lý thể loại).
     * @return array<int, array{id:int, ten_the_loai:string, so_sach:int}>
     */
    public static function tatCaKemSoSach(): array
    {
        $ds = CoSoDuLieu::truyVan(
            'SELECT c.id, c.ten_the_loai, COUNT(b.id) AS so_sach
               FROM categories c LEFT JOIN books b ON b.the_loai_id = c.id
              GROUP BY c.id, c.ten_the_loai
              ORDER BY c.ten_the_loai'
        )->fetchAll();
        foreach ($ds as &$d) {
            $d['id'] = (int) $d['id'];
            $d['so_sach'] = (int) $d['so_sach'];
        }
        unset($d);
        return $ds;
    }

    public static function tonTai(int $id): bool
    {
        return CoSoDuLieu::truyVan('SELECT 1 FROM categories WHERE id = ?', [$id])->fetchColumn() !== false;
    }

    /** Tên đã có ở thể loại khác chưa ($boQuaId = id đang sửa). Dùng cùng collation với khóa UNIQUE của bảng. */
    public static function trungTen(string $ten, ?int $boQuaId = null): bool
    {
        return CoSoDuLieu::truyVan(
            'SELECT 1 FROM categories WHERE ten_the_loai = ? AND id <> ?',
            [$ten, $boQuaId ?? 0]
        )->fetchColumn() !== false;
    }

    /** Thêm mới (id = null) hoặc đổi tên. Trả về id thể loại. */
    public static function luu(?int $id, string $ten): int
    {
        if ($id !== null) {
            CoSoDuLieu::truyVan('UPDATE categories SET ten_the_loai = ? WHERE id = ?', [$ten, $id]);
            return $id;
        }
        CoSoDuLieu::truyVan('INSERT INTO categories (ten_the_loai) VALUES (?)', [$ten]);
        return (int) CoSoDuLieu::ketNoi()->lastInsertId();
    }

    /** Chỉ xóa khi không còn sách nào thuộc thể loại. false nếu còn sách hoặc không tồn tại. */
    public static function xoa(int $id): bool
    {
        return CoSoDuLieu::truyVan(
            'DELETE FROM categories
              WHERE id = ? AND NOT EXISTS (SELECT 1 FROM books WHERE the_loai_id = ?)',
            [$id, $id]
        )->rowCount() > 0;
    }
}