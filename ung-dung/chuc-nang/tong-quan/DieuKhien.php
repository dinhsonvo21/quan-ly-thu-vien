<?php
declare(strict_types=1);

namespace ChucNang\TongQuan;

use Loi\DieuKhienCoSo;

/** Chủ sở hữu: Nguyễn Minh Trạng. Báo cáo: 2.2.4 (Theo dõi quá hạn và thống kê), 4.6 (SR-06). */
final class DieuKhien extends DieuKhienCoSo
{
    private const SO_DONG_TONG_QUAN = 4;
    private const SO_DONG_MOI_TRANG = 10;

    public function tongQuan(): void
    {
        $thang = MoHinh::chuanHoaThang($_GET['thang'] ?? null);
        $this->hienThi('tong-quan', [
            'thang'      => $thang,
            'cacThang'   => MoHinh::cacThangChon(),
            'thongKe'    => MoHinh::thongKe($thang),
            'quaHan'     => MoHinh::phieuQuaHan(self::SO_DONG_TONG_QUAN),
            'tongQuaHan' => MoHinh::demPhieuQuaHan(),
            'datCho'     => MoHinh::datChoHomNay(),
            'muonTra'    => MoHinh::muonTraHomNay(),
        ], 'quan-tri', 'Tổng quan thư viện');
    }

    public function quaHan(): void
    {
        $tong = MoHinh::demPhieuQuaHan();
        $soTrang = max(1, (int) ceil($tong / self::SO_DONG_MOI_TRANG));
        $trang = min($soTrang, max(1, (int) ($_GET['trang'] ?? 1)));

        $this->hienThi('qua-han', [
            'ds'      => MoHinh::phieuQuaHan(self::SO_DONG_MOI_TRANG, ($trang - 1) * self::SO_DONG_MOI_TRANG),
            'tong'    => $tong,
            'trang'   => $trang,
            'soTrang' => $soTrang,
        ], 'quan-tri', 'Phiếu mượn quá hạn');
    }

    public function nhacTra(string $id): void
    {
        $quayVe = $this->duongDanQuayVe();
        try {
            $ok = GuiEmail::nhacTraSach((int) $id);
        } catch (\Throwable $loi) {
            error_log('Nhắc trả #' . $id . ': ' . $loi->getMessage());
            $ok = false;
        }
        $this->flash(
            $ok ? 'thanh_cong' : 'loi',
            $ok ? 'Đã gửi email nhắc trả sách.' : 'Không gửi được email nhắc (phiếu đã trả, không quá hạn, hoặc lỗi máy chủ thư).'
        );
        $this->chuyenHuong($quayVe);
    }

    public function xuatBaoCao(): void
    {
        $thang = MoHinh::chuanHoaThang($_GET['thang'] ?? null);
        $tep = XuatBaoCao::taoFile($thang);
        if ($tep === '' || !is_file($tep)) {
            $this->flash('loi', 'Không tạo được file báo cáo.');
            $this->chuyenHuong('/quan-tri');
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="bao-cao-thu-vien-' . $thang . '.csv"');
        header('Content-Length: ' . filesize($tep));
        readfile($tep);
        unlink($tep);
        exit;
    }

    /** Chỉ cho quay về 2 trang nội bộ (tránh open-redirect qua trường form). */
    private function duongDanQuayVe(): string
    {
        return ($_POST['quay_ve'] ?? '') === 'qua-han' ? '/quan-tri/qua-han' : '/quan-tri';
    }
}