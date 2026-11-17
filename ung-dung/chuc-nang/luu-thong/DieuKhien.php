<?php
declare(strict_types=1);

namespace ChucNang\LuuThong;

use ChucNang\DatCho\MoHinh as DatCho;
use Loi\DieuKhienCoSo;

/** Chủ sở hữu: Nguyễn Đình Quân. Báo cáo: 2.2.4, 4.8 (SR-08), 4.9 (SR-09). */
final class DieuKhien extends DieuKhienCoSo
{
    public function datCho(): void
    {
        $this->hienThi('cho-muon-tai-quay', [
            'datCho' => DatCho::layDangCho($_GET['tu_khoa'] ?? null),   // bảng "Đặt chỗ trực tuyến" (đang là dữ liệu giả)
        ], 'quan-tri', 'Đặt chỗ & cho mượn tại quầy');
    }

    public function giaoSach(string $id): void
    {
        // TODO (Quân): CoSoDuLieu::giaoDich(): kiểm tra điều kiện mượn -> tạo phiếu (HAN_MUON_NGAY ngày)
        //   -> BanSao::doiTrangThai(dang_muon) -> DatCho::danhDauDaGiao($id).
        $this->chuaLam('Giao sách đặt trước', '/quan-tri/dat-cho');
    }

    public function traCuuSinhVien(): void
    {
        // TODO (Quân): ?ma=SV2024025 -> MoHinh::dieuKienMuon(). STUB dưới đây để dựng giao diện.
        $this->json([
            'ho_ten' => 'Lê Minh Khoa', 'ma' => (string) ($_GET['ma'] ?? ''), 'khoa' => 'Công nghệ thông tin',
            'tai_khoan_hoat_dong' => true, 'dang_muon' => 1, 'toi_da' => SO_SACH_TOI_DA,
            'co_qua_han' => false, 'phi_chua_thanh_toan' => 0, 'du_dieu_kien' => true,
        ]);
    }

    public function choMuon(): void
    {
        // TODO (Quân): trong MỘT giao dịch: khóa bản sao san_co (BanSao::layBanSanCo) -> chặn nếu không đủ điều kiện
        //   -> INSERT phiếu (ngay_muon = hôm nay, han_tra = +HAN_MUON_NGAY) -> BanSao::doiTrangThai(dang_muon).
        $this->chuaLam('Xác nhận cho mượn', '/quan-tri/dat-cho');
    }

    public function muonTra(): void
    {
        $this->hienThi('nhan-tra-sach', [], 'quan-tri', 'Trả sách & xử lý phí phạt');
    }

    public function traSach(string $id): void
    {
        // TODO (Quân): server TỰ TÍNH LẠI phạt bằng TinhPhiPhat (không tin số từ trình duyệt); giao dịch:
        //   phiếu -> da_tra, ngay_tra; ghi PhieuPhat; BanSao -> san_co (hoặc hong/mat theo tình trạng).
        $this->chuaLam('Hoàn tất trả sách & thu phí', '/quan-tri/muon-tra');
    }
}
