<?php
declare(strict_types=1);

namespace ChucNang\KhoSach;

use Loi\DieuKhienCoSo;

/** Chủ sở hữu: Nguyễn Minh Trạng. Báo cáo: 2.2.3, 4.7 (SR-07). */
final class DieuKhien extends DieuKhienCoSo
{
    public function danhSach(): void
    {
        $this->hienThi('quan-ly-kho-sach', [], 'quan-tri', 'Kho sách & danh mục');
    }

    public function luuSach(): void
    {
        // TODO (Trạng): kiểm tra dữ liệu; TaiAnh::luu($_FILES['anh_bia'], 'anh-bia-sach') nếu có ảnh;
        //   Sach::luu(); khi tăng "Tổng số bản" -> BanSao::taoBanSao(); giảm -> chỉ cho phép nếu còn đủ bản san_co.
        $this->chuaLam('Lưu thông tin sách', '/quan-tri/kho-sach');
    }

    public function theLoai(): void
    {
        $this->hienThi('quan-ly-the-loai', [], 'quan-tri', 'Quản lý thể loại');
    }

    public function luuTheLoai(): void
    {
        $this->chuaLam('Lưu thể loại', '/quan-tri/the-loai');
    }

    public function xoaTheLoai(string $id): void
    {
        // TODO (Trạng): không cho xóa thể loại còn sách.
        $this->chuaLam('Xóa thể loại', '/quan-tri/the-loai');
    }
}
