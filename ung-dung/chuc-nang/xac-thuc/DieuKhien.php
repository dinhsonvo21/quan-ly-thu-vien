<?php
declare(strict_types=1);

namespace ChucNang\XacThuc;

use Loi\CauHinh;
use Loi\DieuKhienCoSo;
use Loi\GuiMail;
use Loi\KiemTra;
use Loi\PhienLamViec;
use Loi\TrangLoi;

/**
 * Chủ sở hữu: Võ Đình Sơn.  Báo cáo: 2.2.2, 4.2 (SR-02), 4.3 (SR-03).
 */
final class DieuKhien extends DieuKhienCoSo
{
    public function hienDangKy(): void
    {
        $this->hienThi('dang-ky', [
            'duLieu' => ['ho_dem' => '', 'ten' => '', 'email' => ''],
            'loi' => [],
        ], 'nguoi-dung', 'Đăng ký');
    }

    public function xuLyDangKy(): void
    {
        if (!PhienLamViec::kiemTraCsrf()) {
            $this->flash('loi', 'Phiên làm việc đã hết hạn. Hãy tải lại trang rồi thử lại.');
            $this->chuyenHuong('/dang-ky');
        }

        $layChuoi = static fn (string $ten): string =>
            is_string($_POST[$ten] ?? null) ? $_POST[$ten] : '';

        $hoDem = trim($layChuoi('ho_dem'));
        $ten = trim($layChuoi('ten'));
        $email = trim($layChuoi('email'));
        $matKhau = $layChuoi('mat_khau');
        $xacNhanMatKhau = $layChuoi('xac_nhan_mat_khau');
        $duLieu = ['ho_dem' => $hoDem, 'ten' => $ten, 'email' => $email];
        $loi = [];

        if (!KiemTra::khongRong($hoDem)) {
            $loi['ho_dem'] = 'Vui lòng nhập họ và tên đệm.';
        }
        if (!KiemTra::khongRong($ten)) {
            $loi['ten'] = 'Vui lòng nhập tên.';
        }
        if (!KiemTra::khongRong($email)) {
            $loi['email'] = 'Vui lòng nhập email.';
        } elseif (!KiemTra::email($email)) {
            $loi['email'] = 'Email không hợp lệ.';
        }
        if (!KiemTra::matKhau($matKhau)) {
            $loi['mat_khau'] = 'Mật khẩu phải có ít nhất ' . MAT_KHAU_TOI_THIEU . ' ký tự, gồm cả chữ và số.';
        }
        if ($xacNhanMatKhau === '') {
            $loi['xac_nhan_mat_khau'] = 'Vui lòng xác nhận mật khẩu.';
        } elseif ($xacNhanMatKhau !== $matKhau) {
            $loi['xac_nhan_mat_khau'] = 'Mật khẩu xác nhận không khớp.';
        }
        if (($layChuoi('dong_y_dieu_khoan')) !== '1') {
            $loi['dong_y_dieu_khoan'] = 'Bạn cần đồng ý với điều khoản sử dụng.';
        }

        if ($loi === [] && MoHinh::emailDaTonTai($email)) {
            $loi['email'] = 'Email này đã được đăng ký.';
        }

        if ($loi !== []) {
            $this->hienThi('dang-ky', ['duLieu' => $duLieu, 'loi' => $loi], 'nguoi-dung', 'Đăng ký');
            return;
        }

        try {
            MoHinh::taoBanDoc($hoDem, $ten, $email, password_hash($matKhau, PASSWORD_DEFAULT));
        } catch (\PDOException $ngoaiLe) {
            $thongTin = $ngoaiLe->errorInfo;
            if (($thongTin[1] ?? null) !== 1062 || !str_contains((string) ($thongTin[2] ?? ''), 'uq_users_email')) {
                throw $ngoaiLe;
            }

            $loi['email'] = 'Email này đã được đăng ký.';
            $this->hienThi('dang-ky', ['duLieu' => $duLieu, 'loi' => $loi], 'nguoi-dung', 'Đăng ký');
            return;
        }

        $this->flash('thanh_cong', 'Tạo tài khoản thành công. Vui lòng đăng nhập.');
        $this->chuyenHuong('/dang-nhap');
    }

    public function hienDangNhap(): void
    {
        $this->hienThi('dang-nhap', [
            'duLieu' => ['email' => ''],
            'loi' => [],
        ], 'nguoi-dung', 'Đăng nhập');
    }

    public function xuLyDangNhap(): void
    {
        if (!PhienLamViec::kiemTraCsrf()) {
            $this->flash('loi', 'Phiên làm việc đã hết hạn. Hãy tải lại trang rồi thử lại.');
            $this->chuyenHuong('/dang-nhap');
        }

        $layChuoi = static fn (string $ten): string =>
            is_string($_POST[$ten] ?? null) ? $_POST[$ten] : '';

        $email = trim($layChuoi('email'));
        $matKhau = $layChuoi('mat_khau');
        $duLieu = ['email' => $email];
        $loi = [];

        if ($email === '') {
            $loi['email'] = 'Vui lòng nhập email.';
        } elseif (!KiemTra::email($email)) {
            $loi['email'] = 'Email không hợp lệ.';
        }
        if ($matKhau === '') {
            $loi['mat_khau'] = 'Vui lòng nhập mật khẩu.';
        }

        if ($loi !== []) {
            $this->hienThi('dang-nhap', ['duLieu' => $duLieu, 'loi' => $loi], 'nguoi-dung', 'Đăng nhập');
            return;
        }

        $nguoiDung = MoHinh::timTheoEmail($email);
        if ($nguoiDung === null) {
            $loi['chung'] = 'Email chưa được đăng ký.';
            $this->hienThi('dang-nhap', ['duLieu' => $duLieu, 'loi' => $loi], 'nguoi-dung', 'Đăng nhập');
            return;
        }
        if (!password_verify($matKhau, (string) $nguoiDung['mat_khau_bam'])) {
            $loi['chung'] = 'Mật khẩu không đúng.';
            $this->hienThi('dang-nhap', ['duLieu' => $duLieu, 'loi' => $loi], 'nguoi-dung', 'Đăng nhập');
            return;
        }

        if (($nguoiDung['trang_thai'] ?? '') === 'tam_khoa') {
            $loi['chung'] = 'Tài khoản của bạn đang bị tạm khóa.';
            $this->hienThi('dang-nhap', ['duLieu' => $duLieu, 'loi' => $loi], 'nguoi-dung', 'Đăng nhập');
            return;
        }

        PhienLamViec::dangNhap([
            'id' => (int) $nguoiDung['id'],
            'ma' => (string) ($nguoiDung['ma_nguoi_dung'] ?? ''),
            'ho_ten' => trim((string) $nguoiDung['ho_dem'] . ' ' . (string) $nguoiDung['ten']),
            'email' => (string) $nguoiDung['email'],
            'vai_tro' => (string) $nguoiDung['vai_tro'],
        ]);

        if ($layChuoi('ghi_nho') === '1') {
            $thoiHan = time() + 30 * 24 * 60 * 60;
            session_set_cookie_params([
                'lifetime' => 30 * 24 * 60 * 60,
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => !empty($_SERVER['HTTPS']),
            ]);
            setcookie(session_name(), session_id(), [
                'expires' => $thoiHan,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        $this->flash('thanh_cong', 'Chào mừng bạn đã đăng nhập.');
        $vaiTro = (string) $nguoiDung['vai_tro'];
        $this->chuyenHuong($vaiTro === VAI_TRO_BAN_DOC ? '/' : '/quan-tri');
    }

    public function dangXuat(): void
    {
        PhienLamViec::dangXuat();
        $this->chuyenHuong('/');
    }

    public function hienQuenMatKhau(): void
    {
        $this->hienThi('quen-mat-khau', [
            'duLieu' => ['email' => ''],
            'loi' => [],
        ], 'nguoi-dung', 'Khôi phục mật khẩu');
    }

    public function xuLyQuenMatKhau(): void
    {
        if (!PhienLamViec::kiemTraCsrf()) {
            $this->flash('loi', 'Phiên làm việc đã hết hạn. Hãy tải lại trang rồi thử lại.');
            $this->chuyenHuong('/quen-mat-khau');
        }

        $emailNhap = $_POST['email'] ?? '';
        $email = is_string($emailNhap) ? trim($emailNhap) : '';
        if ($email === '' || !KiemTra::email($email)) {
            $this->hienThi('quen-mat-khau', [
                'duLieu' => ['email' => $email],
                'loi' => ['email' => $email === '' ? 'Vui lòng nhập email.' : 'Email không hợp lệ.'],
            ], 'nguoi-dung', 'Khôi phục mật khẩu');
            return;
        }

        $nguoiDung = MoHinh::timTheoEmail($email);
        if ($nguoiDung === null) {
            $this->flash('thong_tin', 'Nếu email được đăng ký, hướng dẫn khôi phục sẽ được gửi đến hộp thư của bạn.');
            $this->chuyenHuong('/dang-nhap');
        }

        $maOtp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        if (!MoHinh::luuMaOtp($email, $maOtp)) {
            throw new \RuntimeException('Không thể lưu mã OTP khôi phục mật khẩu.');
        }

        $_SESSION['email_khoi_phuc'] = $email;
        $cheDoGoLoi = (bool) CauHinh::lay('ung-dung', 'che_do_go_loi', false);
        $emailMau = preg_match('/@(example\.com|qnu\.edu\.vn)$/i', $email) === 1;

        if ($cheDoGoLoi && $emailMau) {
            $this->flash('thong_tin', 'Email mẫu dùng khi phát triển; mã OTP kiểm thử của bạn là ' . $maOtp . ' (hiệu lực 5 phút).');
            $this->chuyenHuong('/dat-lai-mat-khau');
        }

        $hoTen = trim((string) $nguoiDung['ho_dem'] . ' ' . (string) $nguoiDung['ten']);
        if (!GuiMail::guiOtpKhoiPhuc($email, $hoTen, $maOtp)) {
            if ($cheDoGoLoi) {
                $this->flash('thong_tin', 'Không gửi được email trong chế độ phát triển. Mã OTP kiểm thử của bạn là ' . $maOtp . ' (hiệu lực 5 phút).');
                $this->chuyenHuong('/dat-lai-mat-khau');
            }

            unset($_SESSION['email_khoi_phuc']);
            $this->flash('loi', 'Hiện chưa thể gửi email khôi phục. Vui lòng thử lại sau.');
            $this->chuyenHuong('/quen-mat-khau');
        }

        $this->flash('thanh_cong', 'Mã OTP đã được gửi đến email của bạn. Mã có hiệu lực trong 5 phút.');
        $this->chuyenHuong('/dat-lai-mat-khau');
    }

    public function hienDatLaiMatKhau(): void
    {
        if (!is_string($_SESSION['email_khoi_phuc'] ?? null)) {
            $this->flash('thong_tin', 'Vui lòng yêu cầu mã OTP khôi phục trước.');
            $this->chuyenHuong('/quen-mat-khau');
        }

        $this->hienThi('dat-lai-mat-khau', [
            'duLieu' => ['ma_otp' => ''],
            'loi' => [],
        ], 'nguoi-dung', 'Đặt lại mật khẩu');
    }

    public function xuLyDatLaiMatKhau(): void
    {
        if (!PhienLamViec::kiemTraCsrf()) {
            $this->flash('loi', 'Phiên làm việc đã hết hạn. Hãy tải lại trang rồi thử lại.');
            $this->chuyenHuong('/dat-lai-mat-khau');
        }
        if (!is_string($_SESSION['email_khoi_phuc'] ?? null)) {
            $this->flash('thong_tin', 'Vui lòng yêu cầu mã OTP khôi phục trước.');
            $this->chuyenHuong('/quen-mat-khau');
        }

        $email = $_SESSION['email_khoi_phuc'];
        $maOtp = is_string($_POST['ma_otp'] ?? null) ? trim($_POST['ma_otp']) : '';
        $matKhau = is_string($_POST['mat_khau_moi'] ?? null) ? $_POST['mat_khau_moi'] : '';
        $xacNhanMatKhau = is_string($_POST['xac_nhan_mat_khau'] ?? null) ? $_POST['xac_nhan_mat_khau'] : '';
        $loi = [];

        if (preg_match('/^\d{6}$/', $maOtp) !== 1) {
            $loi['ma_otp'] = 'Vui lòng nhập mã OTP gồm đúng 6 chữ số.';
        } elseif (!MoHinh::xacThucOtp($email, $maOtp)) {
            $loi['ma_otp'] = 'Mã OTP không đúng hoặc đã hết hạn. Vui lòng yêu cầu mã mới.';
        }
        if (!KiemTra::matKhau($matKhau)) {
            $loi['mat_khau_moi'] = 'Mật khẩu phải có ít nhất ' . MAT_KHAU_TOI_THIEU . ' ký tự, gồm cả chữ và số.';
        }
        if ($xacNhanMatKhau === '') {
            $loi['xac_nhan_mat_khau'] = 'Vui lòng xác nhận mật khẩu mới.';
        } elseif ($xacNhanMatKhau !== $matKhau) {
            $loi['xac_nhan_mat_khau'] = 'Mật khẩu xác nhận không khớp.';
        }

        if ($loi !== []) {
            $this->hienThi('dat-lai-mat-khau', [
                'duLieu' => ['ma_otp' => $maOtp],
                'loi' => $loi,
            ], 'nguoi-dung', 'Đặt lại mật khẩu');
            return;
        }

        $matKhauBam = password_hash($matKhau, PASSWORD_DEFAULT);
        if (!MoHinh::capNhatMatKhauMoi($email, $maOtp, $matKhauBam)) {
            $this->hienThi('dat-lai-mat-khau', [
                'duLieu' => ['ma_otp' => $maOtp],
                'loi' => ['ma_otp' => 'Mã OTP không đúng hoặc đã hết hạn. Vui lòng yêu cầu mã mới.'],
            ], 'nguoi-dung', 'Đặt lại mật khẩu');
            return;
        }

        unset($_SESSION['email_khoi_phuc']);
        $this->flash('thanh_cong', 'Mật khẩu đã được cập nhật. Vui lòng đăng nhập.');
        $this->chuyenHuong('/dang-nhap');
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
