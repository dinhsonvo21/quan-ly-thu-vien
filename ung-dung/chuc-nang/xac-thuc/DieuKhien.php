<?php
declare(strict_types=1);

namespace ChucNang\XacThuc;

use Loi\CauHinh;
use Loi\DieuKhienCoSo;
use Loi\PhienLamViec;
use Loi\TrangLoi;

/**
 * Chủ sở hữu: Võ Đình Sơn.  Báo cáo: 2.2.2, 4.2 (SR-02), 4.3 (SR-03).
 */
final class DieuKhien extends DieuKhienCoSo
{
    public function hienDangKy(): void
    {
        $this->hienThi('dang-ky', [], 'nguoi-dung', 'Đăng ký');
    }

    public function xuLyDangKy(): void
    {
        // TODO (Sơn): kiểm tra họ/tên/email (KiemTra::email)/mật khẩu (KiemTra::matKhau)/xác nhận/điều khoản,
        //             trùng email -> báo lỗi dưới trường; hợp lệ -> MoHinh::taoBanDoc() (password_hash) -> chuyển sang /dang-nhap.
        $this->chuaLam('Xử lý đăng ký (SR-02)', '/dang-ky');
    }

    public function hienDangNhap(): void
    {
        $this->hienThi('dang-nhap', [], 'nguoi-dung', 'Đăng nhập');
    }

    public function xuLyDangNhap(): void
    {
        // TODO (Sơn): MoHinh::timTheoEmail -> password_verify -> kiểm tra trang_thai != 'tam_khoa'
        //             -> PhienLamViec::dangNhap() -> "Ghi nhớ đăng nhập" -> điều hướng theo vai trò:
        //             ban_doc về '/', thu_thu/quan_tri về '/quan-tri'.
        $this->chuaLam('Xử lý đăng nhập (SR-03)', '/dang-nhap');
    }

    public function dangXuat(): void
    {
        PhienLamViec::dangXuat();
        $this->chuyenHuong('/');
    }

    public function hienQuenMatKhau(): void
    {
        $this->hienThi('quen-mat-khau', [], 'nguoi-dung', 'Quên mật khẩu');
    }

    public function xuLyQuenMatKhau(): void
    {
        // TODO (Sơn): gửi liên kết đặt lại qua email, hoặc ghi chú "liên hệ Thủ thư để cấp lại" (báo cáo cho phép cả hai).
        $this->chuaLam('Khôi phục mật khẩu', '/quen-mat-khau');
    }

    /** Đăng nhập giả để xem giao diện: /dev/dang-nhap-nhanh/ban_doc | thu_thu | quan_tri */
    public function dangNhapNhanh(string $vaiTro): void
    {
        if (!CauHinh::lay('ung-dung', 'che_do_go_loi', false)) {
            TrangLoi::hien(404);
        }
        $mau = [
            VAI_TRO_BAN_DOC  => ['id' => 1, 'ma' => 'SV2024001', 'ho_ten' => 'Nguyễn Trần Thị', 'email' => 'nguyenthi@example.com'],
            VAI_TRO_THU_THU  => ['id' => 2, 'ma' => 'TT-002', 'ho_ten' => 'Nguyễn Đình Quân', 'email' => 'quan@qnu.edu.vn'],
            VAI_TRO_QUAN_TRI => ['id' => 3, 'ma' => 'QT-001', 'ho_ten' => 'Võ Đình Sơn', 'email' => 'admin.son@qnu.edu.vn'],
        ];
        if (!isset($mau[$vaiTro])) {
            TrangLoi::hien(404);
        }
        PhienLamViec::dangNhap($mau[$vaiTro] + ['vai_tro' => $vaiTro]);
        $this->chuyenHuong($vaiTro === VAI_TRO_BAN_DOC ? '/' : '/quan-tri');
    }
}
