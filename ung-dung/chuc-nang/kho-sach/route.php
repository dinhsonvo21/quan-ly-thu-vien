<?php
declare(strict_types=1);

use ChucNang\KhoSach\DieuKhien;
use Loi\DinhTuyen;
use Loi\PhanQuyen;

return static function (DinhTuyen $r): void {
    $nv = PhanQuyen::NHAN_VIEN;   // Thủ thư + Quản trị viên
    $r->get('/quan-tri/kho-sach', [DieuKhien::class, 'danhSach'], $nv);
    $r->post('/quan-tri/kho-sach/luu', [DieuKhien::class, 'luuSach'], $nv);              // thêm mới hoặc sửa
    $r->get('/quan-tri/the-loai', [DieuKhien::class, 'theLoai'], $nv);
    $r->post('/quan-tri/the-loai/luu', [DieuKhien::class, 'luuTheLoai'], $nv);
    $r->post('/quan-tri/the-loai/{id}/xoa', [DieuKhien::class, 'xoaTheLoai'], $nv);
};
