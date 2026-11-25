<?php
declare(strict_types=1);

namespace ChucNang\TaiKhoan;

use Loi\DieuKhienCoSo;
use Loi\KiemTra;
use Loi\PhienLamViec;

/** Chủ sở hữu: Nguyễn Đình Quân. Báo cáo: 2.1, 4.10 (SR-10). Chỉ Quản trị viên. */
final class DieuKhien extends DieuKhienCoSo
{
    private const VE = '/quan-tri/tai-khoan';

    public function danhSach(): void
    {
        $vaiTro = (string) ($_GET['vai_tro'] ?? '');
        $trangThai = (string) ($_GET['trang_thai'] ?? '');
        $boLoc = [
            'tu_khoa'    => trim((string) ($_GET['q'] ?? '')),
            'vai_tro'    => in_array($vaiTro, MoHinh::VAI_TRO, true) ? $vaiTro : '',
            'trang_thai' => in_array($trangThai, MoHinh::TRANG_THAI, true) ? $trangThai : '',
        ];
        $kq = MoHinh::timKiem($boLoc, max(1, (int) ($_GET['trang'] ?? 1)));

        // Dữ liệu form tạo thủ thư khi lưu lỗi -> mở lại popup với giá trị đã nhập (không giữ mật khẩu).
        $cu = $_SESSION['tai_khoan_cu'] ?? null;
        unset($_SESSION['tai_khoan_cu']);

        $this->hienThi('quan-ly-tai-khoan', [
            'boLoc'   => $boLoc,
            'ds'      => $kq['danh_sach'],
            'tong'    => $kq['tong'],
            'trang'   => $kq['trang'],
            'soTrang' => $kq['so_trang'],
            'thongKe' => MoHinh::thongKe(),
            'cu'      => $cu,
        ], 'quan-tri', 'Tài khoản & phân quyền');
    }

    public function taoThuThu(): void
    {
        $hoTen = preg_replace('/\s+/u', ' ', trim((string) ($_POST['ho_ten'] ?? ''))) ?? '';
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $matKhau = (string) ($_POST['mat_khau'] ?? '');

        $loi = [];
        if (!KiemTra::khongRong($hoTen)) {
            $loi[] = 'Vui lòng nhập họ và tên.';
        } elseif (mb_strlen($hoTen) > 90 || count(explode(' ', $hoTen)) < 2) {
            $loi[] = 'Họ và tên cần gồm họ và tên (tối đa 90 ký tự).';
        }
        if (!KiemTra::email($email) || mb_strlen($email) > 120) {
            $loi[] = 'Email không hợp lệ.';
        } elseif (MoHinh::emailDaDung($email)) {
            $loi[] = 'Email này đã được dùng cho một tài khoản khác.';
        }
        if (!KiemTra::matKhau($matKhau)) {
            $loi[] = 'Mật khẩu tạm tối thiểu ' . MAT_KHAU_TOI_THIEU . ' ký tự, gồm cả chữ và số.';
        }
        if ($loi !== []) {
            $this->baoLoiTao($loi, $hoTen, $email);
        }

        try {
            MoHinh::taoThuThu($hoTen, $email, password_hash($matKhau, PASSWORD_DEFAULT));
        } catch (\PDOException $e) {
            // Trùng email do hai người tạo cùng lúc: chỉ báo trùng, còn lại là lỗi hệ thống.
            if ($e->getCode() === '23000') {
                $this->baoLoiTao(['Email này đã được dùng cho một tài khoản khác.'], $hoTen, $email);
            }
            error_log('Tạo thủ thư: ' . $e->getMessage());
            $this->baoLoiTao(['Không tạo được tài khoản, vui lòng thử lại.'], $hoTen, $email);
        }
        $this->flash('thanh_cong', 'Đã tạo tài khoản thủ thư cho ' . $hoTen . '.');
        $this->chuyenHuong(self::VE);
    }

    public function doiVaiTro(string $id): void
    {
        $tk = $this->layTaiKhoan($id);
        $vaiTroMoi = (string) ($_POST['vai_tro'] ?? '');

        if (!in_array($vaiTroMoi, MoHinh::VAI_TRO, true)) {
            $this->flash('loi', 'Vai trò không hợp lệ.');
        } elseif ($this->laChinhMinh($tk)) {
            $this->flash('loi', 'Bạn không thể đổi vai trò của chính mình.');
        } elseif ($tk['vai_tro'] === $vaiTroMoi) {
            $this->flash('thong_tin', 'Vai trò không thay đổi.');
        } elseif ($tk['vai_tro'] === VAI_TRO_QUAN_TRI && $tk['trang_thai'] === 'hoat_dong' && MoHinh::demQuanTriHoatDong() <= 1) {
            $this->flash('loi', 'Phải còn ít nhất một Quản trị viên đang hoạt động.');
        } elseif (MoHinh::doiVaiTro((int) $tk['id'], $vaiTroMoi)) {
            $this->flash('thanh_cong', 'Đã đổi vai trò của ' . $tk['ho_ten'] . ' thành ' . self::nhanVaiTro($vaiTroMoi) . '.');
        } else {
            $this->flash('loi', 'Không đổi được vai trò, vui lòng thử lại.');
        }
        $this->chuyenHuong(self::VE);
    }

    public function khoa(string $id): void
    {
        $tk = $this->layTaiKhoan($id);

        if ($this->laChinhMinh($tk)) {
            $this->flash('loi', 'Bạn không thể khóa tài khoản đang đăng nhập.');
        } elseif ($tk['trang_thai'] === 'tam_khoa') {
            $this->flash('thong_tin', 'Tài khoản này đã bị khóa trước đó.');
        } elseif ($tk['vai_tro'] === VAI_TRO_QUAN_TRI && MoHinh::demQuanTriHoatDong() <= 1) {
            $this->flash('loi', 'Phải còn ít nhất một Quản trị viên đang hoạt động.');
        } elseif (MoHinh::doiTrangThai((int) $tk['id'], 'tam_khoa')) {
            $this->flash('thanh_cong', 'Đã khóa tài khoản ' . $tk['ma_nguoi_dung'] . ' · ' . $tk['ho_ten'] . '.');
        } else {
            $this->flash('loi', 'Không khóa được tài khoản, vui lòng thử lại.');
        }
        $this->chuyenHuong(self::VE);
    }

    public function moKhoa(string $id): void
    {
        $tk = $this->layTaiKhoan($id);

        if ($tk['trang_thai'] === 'hoat_dong') {
            $this->flash('thong_tin', 'Tài khoản này đang hoạt động.');
        } elseif (MoHinh::doiTrangThai((int) $tk['id'], 'hoat_dong')) {
            $this->flash('thanh_cong', 'Đã mở khóa tài khoản ' . $tk['ma_nguoi_dung'] . ' · ' . $tk['ho_ten'] . '.');
        } else {
            $this->flash('loi', 'Không mở khóa được tài khoản, vui lòng thử lại.');
        }
        $this->chuyenHuong(self::VE);
    }

    /** @return array<string, mixed> tài khoản theo id trên URL; không có -> quay về danh sách kèm lỗi. */
    private function layTaiKhoan(string $id): array
    {
        $tk = ctype_digit($id) ? MoHinh::timTheoId((int) $id) : null;
        if ($tk === null) {
            $this->flash('loi', 'Không tìm thấy tài khoản.');
            $this->chuyenHuong(self::VE);
        }
        return $tk;
    }

    /** @param array<string, mixed> $tk */
    private function laChinhMinh(array $tk): bool
    {
        return (int) $tk['id'] === (int) (PhienLamViec::nguoiDung()['id'] ?? 0);
    }

    private static function nhanVaiTro(string $vaiTro): string
    {
        return ['ban_doc' => 'Sinh viên', 'thu_thu' => 'Thủ thư', 'quan_tri' => 'Quản trị viên'][$vaiTro] ?? $vaiTro;
    }

    /** @param array<int, string> $loi */
    private function baoLoiTao(array $loi, string $hoTen, string $email): never
    {
        $_SESSION['tai_khoan_cu'] = ['ho_ten' => $hoTen, 'email' => $email];
        foreach ($loi as $l) {
            $this->flash('loi', $l);
        }
        $this->chuyenHuong(self::VE);
    }
}
