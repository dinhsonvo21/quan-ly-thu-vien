<?php
declare(strict_types=1);

use ChucNang\HoSo\DieuKhien;
use Loi\DinhTuyen;
use Loi\PhanQuyen;

return static function (DinhTuyen $r): void {
    $r->get('/ho-so', [DieuKhien::class, 'hienHoSo'], PhanQuyen::BAT_KY);
    $r->post('/ho-so/cap-nhat', [DieuKhien::class, 'capNhat'], PhanQuyen::BAT_KY);
    $r->post('/ho-so/doi-mat-khau', [DieuKhien::class, 'doiMatKhau'], PhanQuyen::BAT_KY);
    $r->post('/gia-han/{id}', [DieuKhien::class, 'giaHan'], PhanQuyen::BAT_KY);
    // "Hủy đặt chỗ" gửi tới POST /dat-cho/{id}/huy (chức năng dat-cho, của Sơn).
};
