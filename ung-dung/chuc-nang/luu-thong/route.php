<?php
declare(strict_types=1);

use ChucNang\LuuThong\DieuKhien;
use Loi\DinhTuyen;
use Loi\PhanQuyen;

return static function (DinhTuyen $r): void {
    $nv = PhanQuyen::NHAN_VIEN; 
    $r->get('/quan-tri/dat-cho', [DieuKhien::class, 'datCho'], $nv);
    $r->post('/quan-tri/dat-cho/{id}/giao', [DieuKhien::class, 'giaoSach'], $nv);
    $r->get('/quan-tri/muon/tra-cuu-sinh-vien', [DieuKhien::class, 'traCuuSinhVien'], $nv);   // trả JSON
    $r->post('/quan-tri/muon/xac-nhan', [DieuKhien::class, 'choMuon'], $nv);
    $r->get('/quan-tri/muon-tra', [DieuKhien::class, 'muonTra'], $nv);
    $r->post('/quan-tri/muon-tra/{id}/tra', [DieuKhien::class, 'traSach'], $nv);
    $r->post('/quan-tri/muon-tra/phat/{id}/thu', [DieuKhien::class, 'thuPhi'], $nv);   
};
