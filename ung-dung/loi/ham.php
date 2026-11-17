<?php
declare(strict_types=1);

/** Hàm tiện ích toàn cục, dùng trong file giao diện. */

/** Thoát HTML — BẮT BUỘC dùng cho mọi dữ liệu in ra giao diện. */
function e(mixed $giaTri): string
{
    return htmlspecialchars((string) $giaTri, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Tạo URL nội bộ, tự cộng đường dẫn gốc:  url('/dang-nhap') */
function url(string $duongDan = '/'): string
{
    return Loi\DinhTuyen::goc() . '/' . ltrim($duongDan, '/');
}

/** URL file tĩnh trong public/tai-nguyen:  tai_nguyen('css/chung.css') */
function tai_nguyen(string $duongDan): string
{
    return url('tai-nguyen/' . ltrim($duongDan, '/'));
}

/** Ô ẩn CSRF — đặt trong MỌI form POST. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Loi\PhienLamViec::csrf()) . '">';
}

function tien(int|float $soTien): string
{
    return Loi\DinhDang::tien($soTien);
}

function ngay(?string $ngay, string $dang = 'd/m/Y'): string
{
    return Loi\DinhDang::ngay($ngay, $dang);
}
