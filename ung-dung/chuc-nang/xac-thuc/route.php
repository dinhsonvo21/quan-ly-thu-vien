<?php
declare(strict_types=1);

use ChucNang\XacThuc\DieuKhien;
use Loi\DinhTuyen;
use Loi\PhanQuyen;

return static function (DinhTuyen $r): void {
    $r->get('/dang-ky', [DieuKhien::class, 'hienDangKy']);
    $r->post('/dang-ky', [DieuKhien::class, 'xuLyDangKy']);
    $r->get('/dang-nhap', [DieuKhien::class, 'hienDangNhap']);
    $r->post('/dang-nhap', [DieuKhien::class, 'xuLyDangNhap']);
    $r->post('/dang-xuat', [DieuKhien::class, 'dangXuat'], PhanQuyen::BAT_KY);
    $r->get('/quen-mat-khau', [DieuKhien::class, 'hienQuenMatKhau']);
    $r->post('/quen-mat-khau', [DieuKhien::class, 'xuLyQuenMatKhau']);

    // CHỈ DÙNG KHI PHÁT TRIỂN: đăng nhập giả để xem giao diện (che_do_go_loi = true). Xóa trước khi nộp.
    $r->get('/dev/dang-nhap-nhanh/{vaiTro}', [DieuKhien::class, 'dangNhapNhanh']);
};
