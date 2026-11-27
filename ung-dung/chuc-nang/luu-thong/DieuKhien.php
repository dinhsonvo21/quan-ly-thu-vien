<?php
declare(strict_types=1);

namespace ChucNang\LuuThong;

use ChucNang\DatCho\MoHinh as DatCho;
use ChucNang\KhoSach\BanSao;
use Loi\CoSoDuLieu;
use Loi\DieuKhienCoSo;
use Loi\KiemTra;
use Loi\PhienLamViec;

final class DieuKhien extends DieuKhienCoSo
{
    public function datCho(): void
    {
        $this->hienThi('cho-muon-tai-quay', [
            'datCho'   => DatCho::layDangCho($_GET['tu_khoa'] ?? null),  
            'sachMuon' => PhieuMuon::layDanhSachSachMuon(),           
        ], 'quan-tri', 'Đặt chỗ & cho mượn tại quầy');
    }

    public function giaoSach(string $id): void
    {
        $ve = '/quan-tri/dat-cho';
        if (!ctype_digit($id)) {
            $this->flash('loi', 'Không tìm thấy đơn đặt chỗ.');
            $this->chuyenHuong($ve);
        }
        $thuThuId = $this->thuThuHienTai();
        if ($thuThuId === null) {
            $this->flash('loi', 'Không xác định được thủ thư đang đăng nhập.');
            $this->chuyenHuong($ve);
        }

        try {
            $kq = CoSoDuLieu::giaoDich(static function () use ($id, $thuThuId): array {
                $don = PhieuMuon::layDonDeGiao((int) $id);
                if ($don === null) {
                    return ['loi' => 'Đơn đặt chỗ không tồn tại hoặc đã được giao / hủy.'];
                }
                if ($don['da_het_han']) {
                    return ['loi' => 'Đơn ' . $don['ma_dat_cho'] . ' đã hết hạn giữ chỗ lúc ' . \Loi\DinhDang::ngayGio($don['het_han'])
                        . '. Hệ thống sẽ tự hủy và giải phóng sách.'];
                }
                $dk = PhieuMuon::dieuKienMuonTheoId($don['nguoi_dung_id'], true);
                if ($dk === [] || !$dk['du_dieu_kien']) {
                    return ['loi' => 'Chưa thể giao sách cho ' . ($dk['ho_ten'] ?? 'sinh viên này') . ': ' . ($dk['ly_do'] ?? 'tài khoản không hợp lệ.')];
                }
                $phieuId = PhieuMuon::tao($don['nguoi_dung_id'], $don['ban_sao_id'], $thuThuId, $don['id']);
                if (!PhieuMuon::danhDauDonDaGiao($don['id'])) {
                    throw new \RuntimeException('Không đổi được trạng thái đơn đặt chỗ.');
                }
                BanSao::doiTrangThai($don['ban_sao_id'], BS_DANG_MUON);
                return ['ma_phieu' => 'PM-' . $phieuId, 'sinh_vien' => $dk['ho_ten'], 'ten_sach' => $don['ten_sach'],
                        'han_tra' => PhieuMuon::hanTra($phieuId)];
            });
        } catch (\Throwable $e) {
            error_log('Giao sách đặt trước: ' . $e->getMessage());
            $this->flash('loi', 'Không giao được sách, vui lòng thử lại.');
            $this->chuyenHuong($ve);
        }

        if (isset($kq['loi'])) {
            $this->flash('loi', $kq['loi']);
        } else {
            $this->flash('thanh_cong', 'Đã giao “' . $kq['ten_sach'] . '” cho ' . $kq['sinh_vien'] . '. Phiếu ' . $kq['ma_phieu']
                . ', hạn trả ' . ngay($kq['han_tra']) . '.');
        }
        $this->chuyenHuong($ve);
    }

    public function traCuuSinhVien(): void
    {
        $ma = trim((string) ($_GET['ma'] ?? ''));
        $dk = preg_match('/^[A-Za-z0-9_-]{1,20}$/', $ma) === 1 ? PhieuMuon::dieuKienMuon($ma) : [];
        if ($dk === []) {
            $this->json(['loi' => 'Không tìm thấy sinh viên.'], 404);
        }
        unset($dk['id']);  
        $this->json($dk);
    }

    public function choMuon(): void
    {
        $ve = '/quan-tri/dat-cho#cho-muon-truc-tiep';
        $ma = trim((string) ($_POST['ma_sv'] ?? ''));
        $sachId = (string) ($_POST['sach_id'] ?? '');
        if (preg_match('/^[A-Za-z0-9_-]{1,20}$/', $ma) !== 1) {
            $this->flash('loi', 'Mã sinh viên không hợp lệ.');
            $this->chuyenHuong($ve);
        }
        if (!ctype_digit($sachId) || (int) $sachId < 1) {
            $this->flash('loi', 'Vui lòng chọn sách.');
            $this->chuyenHuong($ve);
        }
        $thuThuId = $this->thuThuHienTai();
        if ($thuThuId === null) {
            $this->flash('loi', 'Không xác định được thủ thư đang đăng nhập.');
            $this->chuyenHuong($ve);
        }

        try {
            $kq = CoSoDuLieu::giaoDich(static function () use ($ma, $sachId, $thuThuId): array {
                $dk = PhieuMuon::dieuKienMuon($ma, true);
                if ($dk === []) {
                    return ['loi' => 'Không tìm thấy sinh viên mã ' . $ma . '.'];
                }
                if (!$dk['du_dieu_kien']) {
                    return ['loi' => $dk['ho_ten'] . ' chưa đủ điều kiện mượn: ' . $dk['ly_do']];
                }
                if (!PhieuMuon::sachDangLuuHanh((int) $sachId)) {
                    return ['loi' => 'Sách không tồn tại hoặc đã ngừng phục vụ.'];
                }
                if (PhieuMuon::dangMuonDauSach($dk['id'], (int) $sachId)) {
                    return ['loi' => $dk['ho_ten'] . ' đang mượn đầu sách này, chưa thể mượn thêm.'];
                }
                $banSao = BanSao::layBanSanCo((int) $sachId);   // khóa dòng để không ai lấy trùng
                if ($banSao === null) {
                    return ['loi' => 'Sách đã hết bản sẵn có.'];
                }
                $phieuId = PhieuMuon::tao($dk['id'], $banSao['id'], $thuThuId);
                BanSao::doiTrangThai($banSao['id'], BS_DANG_MUON);
                return ['ma_phieu' => 'PM-' . $phieuId, 'sinh_vien' => $dk['ho_ten'], 'ma_ban_sao' => $banSao['ma_ban_sao'],
                        'han_tra' => PhieuMuon::hanTra($phieuId)];
            });
        } catch (\Throwable $e) {
            error_log('Cho mượn trực tiếp: ' . $e->getMessage());
            $this->flash('loi', 'Không lập được phiếu mượn, vui lòng thử lại.');
            $this->chuyenHuong($ve);
        }

        if (isset($kq['loi'])) {
            $this->flash('loi', $kq['loi']);
        } else {
            $this->flash('thanh_cong', 'Đã cho ' . $kq['sinh_vien'] . ' mượn bản sao ' . $kq['ma_ban_sao'] . '. Phiếu ' . $kq['ma_phieu']
                . ', hạn trả ' . ngay($kq['han_tra']) . '.');
        }
        $this->chuyenHuong($ve);
    }

    public function muonTra(): void
    {
        $this->hienThi('nhan-tra-sach', [
            'phieuMuon' => PhieuMuon::layDangMuon($_GET['tu_khoa'] ?? null, $_GET['trang_thai'] ?? null, $_SESSION['vua_tra'] ?? []),
            'phatChuaThu' => PhieuPhat::layChuaThu(),   // bảng "Phí phạt chưa thu" (thanh toán sau)
        ], 'quan-tri', 'Trả sách & xử lý phí phạt');
    }

    public function traSach(string $id): void
    {
        $ve = '/quan-tri/muon-tra';
        $ngayTra = trim((string) ($_POST['ngay_tra'] ?? ''));
        $tinhTrang = (string) ($_POST['tinh_trang'] ?? '');
        $hinhThuc = (string) ($_POST['hinh_thuc'] ?? 'thu_ngay');
        $khachDua = trim((string) ($_POST['tien_khach_dua'] ?? ''));
        $trangThaiBanSao = ['binh_thuong' => BS_SAN_CO, 'hong' => BS_HONG, 'mat' => BS_MAT];

        $loi = null;
        if (!ctype_digit($id)) {
            $loi = 'Không tìm thấy phiếu mượn.';
        } elseif (!KiemTra::ngay($ngayTra)) {
            $loi = 'Ngày trả không hợp lệ.';
        } elseif ($ngayTra > date('Y-m-d')) {
            $loi = 'Không được chọn ngày trả trong tương lai.';
        } elseif (!isset($trangThaiBanSao[$tinhTrang])) {
            $loi = 'Tình trạng sách không hợp lệ.';
        } elseif (!in_array($hinhThuc, ['thu_ngay', 'ghi_no'], true)) {
            $loi = 'Hình thức thanh toán không hợp lệ.';
        } elseif ($khachDua !== '' && !ctype_digit($khachDua)) {
            $loi = 'Số tiền khách đưa không hợp lệ.';
        }
        if ($loi !== null) {
            $this->flash('loi', $loi);
            $this->chuyenHuong($ve);
        }

        try {
            $kq = CoSoDuLieu::giaoDich(static function () use ($id, $ngayTra, $tinhTrang, $hinhThuc, $khachDua, $trangThaiBanSao): array {
                $p = PhieuMuon::layDeTra((int) $id);
                if ($p === null) {
                    return ['loi' => 'Phiếu không tồn tại hoặc đã được trả trước đó.'];
                }
                if ($ngayTra < $p['ngay_muon']) {
                    return ['loi' => 'Ngày trả không được trước ngày mượn (' . ngay($p['ngay_muon']) . ').'];
                }

                $soNgayTre = TinhPhiPhat::soNgayTre($p['han_tra'], $ngayTra);
                $phiTre = TinhPhiPhat::phiQuaHan($p['han_tra'], $ngayTra);
                $phiBoiHoan = TinhPhiPhat::phiBoiHoan($tinhTrang, $p['gia_bia']);
                $tong = $phiTre + $phiBoiHoan;
                $thuNgay = $tong > 0 && $hinhThuc === 'thu_ngay';

                if ($thuNgay && $khachDua !== '' && (int) $khachDua < $tong) {
                    return ['loi' => 'Khách đưa ' . tien((int) $khachDua) . ' chưa đủ ' . tien($tong) . '.'];
                }

                if (!PhieuMuon::danhDauDaTra($p['id'], $ngayTra)) {
                    return ['loi' => 'Không ghi nhận được việc trả sách, vui lòng thử lại.'];
                }
                BanSao::doiTrangThai($p['ban_sao_id'], $trangThaiBanSao[$tinhTrang]);
                if ($tong > 0) {
                    PhieuPhat::ghi([
                        'phieu_muon_id' => $p['id'], 'so_ngay_tre' => $soNgayTre, 'phi_tre' => $phiTre,
                        'tinh_trang_sach' => $tinhTrang, 'phi_boi_hoan' => $phiBoiHoan, 'da_thu' => $thuNgay,
                    ]);
                }
                return ['ma_phieu' => $p['ma_phieu'], 'sinh_vien' => $p['sinh_vien'], 'tong' => $tong, 'da_thu' => $thuNgay,
                        'tien_thoi' => $thuNgay && $khachDua !== '' ? (int) $khachDua - $tong : 0];
            });
        } catch (\Throwable $e) {
            error_log('Trả sách: ' . $e->getMessage());
            $this->flash('loi', 'Không hoàn tất được việc trả sách, vui lòng thử lại.');
            $this->chuyenHuong($ve);
        }

        if (isset($kq['loi'])) {
            $this->flash('loi', $kq['loi']);
            $this->chuyenHuong($ve);
        }

        $_SESSION['vua_tra'] = array_slice(array_merge($_SESSION['vua_tra'] ?? [], [(int) $id]), -20);

        if ($kq['tong'] === 0) {
            $this->flash('thanh_cong', 'Đã nhận trả phiếu ' . $kq['ma_phieu'] . ' của ' . $kq['sinh_vien'] . '. Không phát sinh phí.');
        } elseif ($kq['da_thu']) {
            $this->flash('thanh_cong', 'Đã nhận trả phiếu ' . $kq['ma_phieu'] . ' và thu ' . tien($kq['tong']) . ' của ' . $kq['sinh_vien']
                . ($kq['tien_thoi'] > 0 ? '. Tiền thối lại: ' . tien($kq['tien_thoi']) : '') . '.');
        } else {
            $this->flash('thong_tin', 'Đã nhận trả phiếu ' . $kq['ma_phieu'] . '. Ghi nợ ' . tien($kq['tong']) . ' của ' . $kq['sinh_vien']
                . ' — sinh viên chưa thể mượn thêm cho đến khi thanh toán.');
        }
        $this->chuyenHuong($ve);
    }

    public function thuPhi(string $id): void
    {
        $phat = ctype_digit($id) ? PhieuPhat::timChuaThu((int) $id) : null;
        if ($phat === null) {
            $this->flash('loi', 'Khoản phí không tồn tại hoặc đã được thu.');
        } elseif (PhieuPhat::danhDauDaThu((int) $phat['id'])) {
            $this->flash('thanh_cong', 'Đã thu ' . tien((int) $phat['tong']) . ' của ' . $phat['sinh_vien'] . ' (phiếu ' . $phat['ma_phieu'] . ').');
        } else {
            $this->flash('loi', 'Khoản phí này vừa được thu bởi người khác.');
        }
        $this->chuyenHuong('/quan-tri/muon-tra');
    }

    private function thuThuHienTai(): ?int
    {
        $nd = PhienLamViec::nguoiDung() ?? [];
        $ma = (string) ($nd['ma'] ?? '');
        if ($ma !== '') {
            $id = CoSoDuLieu::truyVan('SELECT id FROM users WHERE ma_nguoi_dung = ?', [$ma])->fetchColumn();
            if ($id !== false) {
                return (int) $id;
            }
        }
        $id = (int) ($nd['id'] ?? 0);
        return $id > 0 ? $id : null;
    }
}
