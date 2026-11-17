<?php
declare(strict_types=1);

namespace ChucNang\TaiKhoan;

use Loi\DieuKhienCoSo;

/** Chủ sở hữu: Nguyễn Đình Quân. Báo cáo: 2.1, 4.10 (SR-10). Chỉ Quản trị viên. */
final class DieuKhien extends DieuKhienCoSo
{
    public function danhSach(): void
    {
        $this->hienThi('quan-ly-tai-khoan', [], 'quan-tri', 'Tài khoản & phân quyền');
    }

    public function taoThuThu(): void
    {
        $this->chuaLam('Tạo tài khoản thủ thư', '/quan-tri/tai-khoan');
    }

    public function doiVaiTro(string $id): void
    {
        // TODO (Quân): không cho Admin tự hạ quyền của chính mình.
        $this->chuaLam('Đổi vai trò', '/quan-tri/tai-khoan');
    }

    public function khoa(string $id): void
    {
        // TODO (Quân): không cho Admin tự khóa chính mình (SR-10 hiện "Tài khoản hiện tại").
        $this->chuaLam('Khóa tài khoản', '/quan-tri/tai-khoan');
    }

    public function moKhoa(string $id): void
    {
        $this->chuaLam('Mở khóa tài khoản', '/quan-tri/tai-khoan');
    }
}
