<?php
declare(strict_types=1);

namespace ChucNang\TraCuu;

use ChucNang\DatCho\MoHinh as DatCho;
use Loi\DieuKhienCoSo;
use Loi\PhanQuyen;
use Loi\PhienLamViec;
use Loi\TrangLoi;

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
        if (!ctype_digit($id) || (int) $id < 1) {
            TrangLoi::hien(404);
        }

        $sach = MoHinh::chiTiet((int) $id);
        if ($sach === null) {
            TrangLoi::hien(404);
        }

        $nguoiDung = PhienLamViec::nguoiDung();
        $dieuKienDatCho = $nguoiDung === null
            ? null
            : DatCho::kiemTraDieuKienDatCho((int) $nguoiDung['id'], (int) $sach['id']);
        $daTrongDanhSachDoc = $nguoiDung !== null
            && ($nguoiDung['vai_tro'] ?? '') === VAI_TRO_BAN_DOC
            && MoHinh::coTrongDanhSachDoc((int) $nguoiDung['id'], (int) $sach['id']);

        $this->hienThi('chi-tiet-sach', [
            'sach' => $sach,
            'nguoiDung' => $nguoiDung,
            'dieuKienDatCho' => $dieuKienDatCho,
            'daTrongDanhSachDoc' => $daTrongDanhSachDoc,
        ], 'nguoi-dung', 'Chi tiết sách');
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
        if (!ctype_digit($id) || (int) $id < 1) {
            TrangLoi::hien(404);
        }
        PhanQuyen::yeuCauVaiTro([VAI_TRO_BAN_DOC]);

        $sachId = (int) $id;
        if (MoHinh::chiTiet($sachId) === null) {
            TrangLoi::hien(404);
        }

        $nguoiDungId = (int) PhienLamViec::nguoiDung()['id'];
        if (MoHinh::themDanhSachDoc($nguoiDungId, $sachId)) {
            $this->flash('thanh_cong', 'Đã thêm sách vào danh sách đọc.');
        } else {
            $this->flash('thong_tin', 'Sách này đã có trong danh sách đọc của bạn.');
        }
        $this->chuyenHuong('/sach/' . $sachId);
    }
}
