<?php
declare(strict_types=1);

namespace ChucNang\TraCuu;

use Loi\DieuKhienCoSo;

/**
 * Chủ sở hữu: Võ Đình Sơn.  Báo cáo: 2.2.1, 4.1 (SR-01), 4.4 (SR-04).
 * Menu "Danh mục sách" trỏ về khối danh mục của trang chủ (/#danh-muc), không có trang riêng.
 */
final class DieuKhien extends DieuKhienCoSo
{
    public function trangChu(): void
    {
        // TODO (Sơn): đọc $_GET (tu_khoa, the_loai, loai_tai_lieu, trang_thai, sap_xep, trang)
        //             -> MoHinh::timKiem() -> truyền danh sách + phân trang vào giao diện.
        $this->hienThi('trang-chu', [], 'nguoi-dung', 'Trang chủ');
    }

    public function chiTiet(string $id): void
    {
        // TODO (Sơn): MoHinh::chiTiet((int) $id), 404 nếu không có; số bản sẵn có lấy từ KhoSach\BanSao::demSanCo().
        $this->hienThi('chi-tiet-sach', ['id' => (int) $id], 'nguoi-dung', 'Chi tiết sách');
    }

    public function noiQuy(): void
    {
        $this->hienThi('noi-quy', [], 'nguoi-dung', 'Nội quy thư viện');
    }

    public function danhSachDoc(): void
    {
        $this->hienThi('danh-sach-doc', [], 'nguoi-dung', 'Danh sách đọc');
    }

    public function themDanhSachDoc(string $id): void
    {
        // TODO (Sơn): MoHinh::themDanhSachDoc(nguoiDungId, (int) $id) rồi quay lại /sach/{id}.
        $this->chuaLam('Thêm vào danh sách đọc', '/sach/' . (int) $id);
    }
}
