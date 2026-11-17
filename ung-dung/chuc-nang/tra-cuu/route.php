<?php
declare(strict_types=1);

use ChucNang\TraCuu\DieuKhien;
use Loi\DinhTuyen;
use Loi\PhanQuyen;

return static function (DinhTuyen $r): void {
    $r->get('/', [DieuKhien::class, 'trangChu']);                           // SR-01 (khách xem được)
    $r->get('/sach/{id}', [DieuKhien::class, 'chiTiet']);                   // SR-04
    $r->get('/noi-quy', [DieuKhien::class, 'noiQuy']);
    $r->get('/danh-sach-doc', [DieuKhien::class, 'danhSachDoc'], PhanQuyen::BAT_KY);
    $r->post('/danh-sach-doc/{id}', [DieuKhien::class, 'themDanhSachDoc'], PhanQuyen::BAT_KY);
};
