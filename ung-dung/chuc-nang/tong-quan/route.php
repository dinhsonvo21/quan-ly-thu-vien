<?php
declare(strict_types=1);

use ChucNang\TongQuan\DieuKhien;
use Loi\DinhTuyen;
use Loi\PhanQuyen;

return static function (DinhTuyen $r): void {
    $nv = PhanQuyen::NHAN_VIEN;
    $r->get('/quan-tri', [DieuKhien::class, 'tongQuan'], $nv);
    $r->get('/quan-tri/qua-han', [DieuKhien::class, 'quaHan'], $nv);
    $r->post('/quan-tri/nhac-tra/{id}', [DieuKhien::class, 'nhacTra'], $nv);
    $r->get('/quan-tri/xuat-bao-cao', [DieuKhien::class, 'xuatBaoCao'], $nv);
};
