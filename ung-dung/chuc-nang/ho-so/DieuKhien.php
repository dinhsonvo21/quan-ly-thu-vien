<?php
declare(strict_types=1);

namespace ChucNang\HoSo;

use ChucNang\DatCho\MoHinh as DatCho;
use Loi\DieuKhienCoSo;
use Loi\DinhDang;
use Loi\KiemTra;
use Loi\PhienLamViec;

/** Chủ sở hữu: Nguyễn Minh Trạng. Báo cáo: 2.2.2 (Quản lý hồ sơ), 4.5 (SR-05). */
final class DieuKhien extends DieuKhienCoSo
{
    public function hienHoSo(): void
    {
        $this->veTrang();
    }

    public function capNhat(): void
    {
        $id = (int) PhienLamViec::nguoiDung()['id'];

        $duLieu = [
            'ho_ten'   => trim($this->layChuoi('ho_ten')),
            'email'    => trim($this->layChuoi('email')),
            'sdt'      => trim($this->layChuoi('sdt')),
            'khoa_lop' => trim($this->layChuoi('khoa_lop')),
        ];
        $loi = [];

        // Mã sinh viên chỉ đọc: cố tình không đọc $_POST['ma_sinh_vien'].
        if (!KiemTra::khongRong($duLieu['ho_ten'])) {
            $loi['ho_ten'] = 'Vui lòng nhập họ và tên.';
        } else {
            [$hoDem, $ten] = MoHinh::tachHoTen($duLieu['ho_ten']);
            if (mb_strlen($hoDem) > 60 || mb_strlen($ten) > 30) {
                $loi['ho_ten'] = 'Họ và tên quá dài.';
            }
        }

        if (!KiemTra::khongRong($duLieu['email'])) {
            $loi['email'] = 'Vui lòng nhập email.';
        } elseif (!KiemTra::email($duLieu['email']) || mb_strlen($duLieu['email']) > 120) {
            $loi['email'] = 'Email không hợp lệ.';
        } elseif (MoHinh::emailDaDuocDung($duLieu['email'], $id)) {
            $loi['email'] = 'Email này đang được tài khoản khác sử dụng.';
        }

        if ($duLieu['sdt'] !== '' && preg_match('/^\+?[0-9 ]{9,14}$/', $duLieu['sdt']) !== 1) {
            $loi['sdt'] = 'Số điện thoại không hợp lệ.';
        }
        if (mb_strlen($duLieu['khoa_lop']) > 100) {
            $loi['khoa_lop'] = 'Khoa / Lớp tối đa 100 ký tự.';
        }

        if ($loi !== []) {
            $this->veTrang('thong-tin', ['duLieu' => $duLieu, 'loiHoSo' => $loi]);
            return;
        }

        try {
            MoHinh::capNhat($id, $duLieu);
        } catch (\PDOException $ngoaiLe) {
            // Hai người cùng đổi sang một email trong tích tắc: UNIQUE uq_users_email chặn ở DB.
            if (($ngoaiLe->errorInfo[1] ?? null) !== 1062) {
                throw $ngoaiLe;
            }
            $this->veTrang('thong-tin', [
                'duLieu'  => $duLieu,
                'loiHoSo' => ['email' => 'Email này đang được tài khoản khác sử dụng.'],
            ]);
            return;
        }

        // Giữ tên/email trên header đồng bộ với dữ liệu vừa lưu.
        $_SESSION['nguoi_dung']['ho_ten'] = trim(preg_replace('/\s+/u', ' ', $duLieu['ho_ten']) ?? $duLieu['ho_ten']);
        $_SESSION['nguoi_dung']['email']  = $duLieu['email'];

        $this->flash('thanh_cong', 'Đã lưu thay đổi hồ sơ.');
        $this->chuyenHuong('/ho-so#thong-tin');
    }

    public function doiMatKhau(): void
    {
        $id  = (int) PhienLamViec::nguoiDung()['id'];
        $cu  = $this->layChuoi('mat_khau_hien_tai');
        $moi = $this->layChuoi('mat_khau_moi');
        $xn  = $this->layChuoi('xac_nhan_mat_khau');
        $loi = [];

        $bam = MoHinh::layMatKhauBam($id);
        if ($cu === '') {
            $loi['mat_khau_hien_tai'] = 'Vui lòng nhập mật khẩu hiện tại.';
        } elseif ($bam === null || !password_verify($cu, $bam)) {
            $loi['mat_khau_hien_tai'] = 'Mật khẩu hiện tại không đúng.';
        }

        if (!KiemTra::matKhau($moi)) {
            $loi['mat_khau_moi'] = 'Mật khẩu phải có ít nhất ' . MAT_KHAU_TOI_THIEU . ' ký tự, gồm cả chữ và số.';
        }
        if ($xn === '') {
            $loi['xac_nhan_mat_khau'] = 'Vui lòng xác nhận mật khẩu mới.';
        } elseif ($xn !== $moi) {
            $loi['xac_nhan_mat_khau'] = 'Mật khẩu xác nhận không khớp.';
        }

        if ($loi !== []) {
            $this->veTrang('doi-mat-khau', ['loiMatKhau' => $loi]);
            return;
        }

        MoHinh::doiMatKhau($id, password_hash($moi, PASSWORD_DEFAULT));
        session_regenerate_id(true);   // đổi mật khẩu xong thì làm mới phiên

        $this->flash('thanh_cong', 'Đã cập nhật mật khẩu.');
        $this->chuyenHuong('/ho-so#doi-mat-khau');
    }

    public function giaHan(string $id): void
    {
        $nguoiDungId = (int) PhienLamViec::nguoiDung()['id'];
        $phieuId = (int) $id;

        try {
            MoHinh::giaHan($phieuId, $nguoiDungId);
        } catch (\DomainException $ngoaiLe) {
            $this->flash('loi', $ngoaiLe->getMessage());
            $this->chuyenHuong('/ho-so#lich-su');
        }

        $hanMoi = MoHinh::layHanTra($phieuId);
        $this->flash(
            'thanh_cong',
            'Gia hạn ' . DinhDang::maPhieuMuon($phieuId) . ' thành công: hạn trả mới ' . DinhDang::ngay($hanMoi) . '.'
        );
        $this->chuyenHuong('/ho-so#lich-su');
    }

    /* ------------------------------------------------------------------ */

    /**
     * Dựng trang hồ sơ (dùng cho GET và cho các lần POST bị lỗi kiểm tra).
     * @param array<string, mixed> $them duLieu / loiHoSo / loiMatKhau
     */
    private function veTrang(string $tabMo = 'thong-tin', array $them = []): void
    {
        $id = (int) PhienLamViec::nguoiDung()['id'];

        $this->hienThi('ho-so-ca-nhan', $them + [
            'hoSo'       => MoHinh::layHoSo($id) ?? [],
            'datCho'     => DatCho::layCuaNguoiDung($id),   // bảng "Đặt chỗ đang hoạt động"
            'lichSu'     => MoHinh::layLichSuMuon($id),     // bảng "Lịch sử mượn sách"
            'tabMo'      => $tabMo,
            'duLieu'     => null,
            'loiHoSo'    => [],
            'loiMatKhau' => [],
        ], 'nguoi-dung', 'Tài khoản của tôi');
    }

    private function layChuoi(string $ten): string
    {
        return is_string($_POST[$ten] ?? null) ? $_POST[$ten] : '';
    }
}
