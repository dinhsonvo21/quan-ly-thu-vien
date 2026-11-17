<?php
declare(strict_types=1);

namespace Loi;

final class TrangLoi
{
    public static function hien(int $ma, string $thongDiep = ''): never
    {
        http_response_code($ma);
        $tieuDe = $ma === 403 ? 'Không có quyền truy cập' : ($ma === 404 ? 'Không tìm thấy trang' : 'Đã xảy ra lỗi');
        if ($thongDiep === '') {
            $thongDiep = $ma === 403
                ? 'Tài khoản của bạn không được phép dùng chức năng này.'
                : 'Đường dẫn không tồn tại hoặc đã bị di chuyển.';
        }

        $noiDung = '<section class="trang-loi"><p class="nhan">Lỗi ' . $ma . '</p>'
            . '<h1>' . e($tieuDe) . '</h1><p>' . e($thongDiep) . '</p>'
            . '<a class="nut" href="' . e(url('/')) . '">Về trang chủ</a></section>';
        $nguoiDung = PhienLamViec::nguoiDung();
        require UNG_DUNG . '/khung-giao-dien/nguoi-dung.php';
        exit;
    }
}
