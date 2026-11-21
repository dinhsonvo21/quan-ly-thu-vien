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
        $layChuoi = static fn (string $ten): string =>
            is_string($_GET[$ten] ?? null) ? trim((string) $_GET[$ten]) : '';

        $boLoc = [
            'q' => $layChuoi('q'),
            'the_loai' => $layChuoi('the_loai'),
            'loai_tai_lieu' => $layChuoi('loai_tai_lieu'),
            'trang_thai' => $layChuoi('trang_thai'),
            'sap_xep' => $layChuoi('sap_xep') ?: 'moi_nhat',
        ];
        $trangGiaTri = filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT);
        $trang = is_int($trangGiaTri) && $trangGiaTri > 0 ? $trangGiaTri : 1;
        $soLuongMoiTrang = 8;

        $ketQua = MoHinh::timKiemSach($boLoc, $trang, $soLuongMoiTrang);
        $tongSoTrang = max(1, (int) ceil($ketQua['tong_so'] / $soLuongMoiTrang));
        if ($trang > $tongSoTrang) {
            $trang = $tongSoTrang;
            $ketQua = MoHinh::timKiemSach($boLoc, $trang, $soLuongMoiTrang);
        }

        $this->hienThi('trang-chu', [
            'boLoc' => $boLoc,
            'theLoai' => MoHinh::layDanhSachTheLoai(),
            'danhSach' => $ketQua['danh_sach'],
            'tongSo' => $ketQua['tong_so'],
            'trang' => $trang,
            'tongSoTrang' => $tongSoTrang,
            'soLuongMoiTrang' => $soLuongMoiTrang,
        ], 'nguoi-dung', 'Trang chủ');
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
