<?php
declare(strict_types=1);

use ChucNang\LuuThong\DieuKhien;
use Loi\DinhTuyen;
use Loi\PhanQuyen;

return static function (DinhTuyen $r): void {
    $nv = PhanQuyen::NHAN_VIEN;   // Thủ thư + Quản trị viên đều được lập phiếu / nhận trả (báo cáo 2.1)
    // SR-08: giao sách đặt trước + cho mượn trực tiếp (sidebar "Đặt chỗ")
    $r->get('/quan-tri/dat-cho', [DieuKhien::class, 'datCho'], $nv);
    $r->post('/quan-tri/dat-cho/{id}/giao', [DieuKhien::class, 'giaoSach'], $nv);
    $r->get('/quan-tri/muon/tra-cuu-sinh-vien', [DieuKhien::class, 'traCuuSinhVien'], $nv);   // trả JSON
    $r->post('/quan-tri/muon/xac-nhan', [DieuKhien::class, 'choMuon'], $nv);
    // SR-09: nhận trả sách + phạt (sidebar "Mượn / Trả sách")
    $r->get('/quan-tri/muon-tra', [DieuKhien::class, 'muonTra'], $nv);
    $r->post('/quan-tri/muon-tra/{id}/tra', [DieuKhien::class, 'traSach'], $nv);
};
