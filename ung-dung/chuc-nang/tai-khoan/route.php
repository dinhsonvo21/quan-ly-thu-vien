<?php
declare(strict_types=1);

use ChucNang\TaiKhoan\DieuKhien;
use Loi\DinhTuyen;
use Loi\PhanQuyen;

return static function (DinhTuyen $r): void {
    $qt = PhanQuyen::CHI_QUAN_TRI;   // SR-10 chỉ dành cho Quản trị viên
    $r->get('/quan-tri/tai-khoan', [DieuKhien::class, 'danhSach'], $qt);
    $r->post('/quan-tri/tai-khoan/tao', [DieuKhien::class, 'taoThuThu'], $qt);
    $r->post('/quan-tri/tai-khoan/{id}/doi-vai-tro', [DieuKhien::class, 'doiVaiTro'], $qt);
    $r->post('/quan-tri/tai-khoan/{id}/khoa', [DieuKhien::class, 'khoa'], $qt);
    $r->post('/quan-tri/tai-khoan/{id}/mo-khoa', [DieuKhien::class, 'moKhoa'], $qt);
};
