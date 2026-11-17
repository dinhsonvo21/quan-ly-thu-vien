<?php
declare(strict_types=1);

namespace ChucNang\HoSo;

use ChucNang\DatCho\MoHinh as DatCho;
use Loi\DieuKhienCoSo;
use Loi\PhienLamViec;

/** Chủ sở hữu: Nguyễn Minh Trạng. Báo cáo: 2.2.2 (Quản lý hồ sơ), 4.5 (SR-05). */
final class DieuKhien extends DieuKhienCoSo
{
    public function hienHoSo(): void
    {
        $nguoiDung = PhienLamViec::nguoiDung();
        $this->hienThi('ho-so-ca-nhan', [
            'datCho' => DatCho::layCuaNguoiDung((int) $nguoiDung['id']),   // bảng "Đặt chỗ đang hoạt động"
            'lichSu' => MoHinh::layLichSuMuon((int) $nguoiDung['id']),     // bảng "Lịch sử mượn sách"
        ], 'nguoi-dung', 'Tài khoản của tôi');
    }

    public function capNhat(): void
    {
        // TODO (Trạng): Mã sinh viên chỉ đọc; cập nhật họ tên, email, SĐT, khoa/lớp (KiemTra::email).
        $this->chuaLam('Cập nhật hồ sơ', '/ho-so');
    }

    public function doiMatKhau(): void
    {
        // TODO (Trạng): password_verify mật khẩu cũ; mới == xác nhận; KiemTra::matKhau; password_hash.
        $this->chuaLam('Đổi mật khẩu', '/ho-so#doi-mat-khau');
    }

    public function giaHan(string $id): void
    {
        // TODO (Trạng): MoHinh::giaHan() — chưa quá hạn, chưa quá SO_LAN_GIA_HAN_TOI_DA, không ai khác đặt trước; +GIA_HAN_NGAY.
        $this->chuaLam('Gia hạn sách', '/ho-so#lich-su');
    }
}
