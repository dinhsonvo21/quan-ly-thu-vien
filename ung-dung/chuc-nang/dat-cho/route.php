<?php
declare(strict_types=1);

use ChucNang\DatCho\DieuKhien;
use Loi\DinhTuyen;
use Loi\PhanQuyen;

return static function (DinhTuyen $r): void {
    $r->post('/dat-giu-cho', [DieuKhien::class, 'tao'], PhanQuyen::BAT_KY);
    $r->post('/dat-cho', [DieuKhien::class, 'tao'], PhanQuyen::BAT_KY);              // popup SR-04
    $r->post('/dat-cho/{id}/huy', [DieuKhien::class, 'huy'], PhanQuyen::BAT_KY);     // nút "Hủy đặt chỗ" SR-05
};
