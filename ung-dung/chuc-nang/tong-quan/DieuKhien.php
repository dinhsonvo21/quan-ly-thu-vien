<?php
declare(strict_types=1);

namespace ChucNang\TongQuan;

use Loi\DieuKhienCoSo;

/** Chủ sở hữu: Nguyễn Minh Trạng. Báo cáo: 2.2.4 (Theo dõi quá hạn và thống kê), 4.6 (SR-06). */
final class DieuKhien extends DieuKhienCoSo
{
    public function tongQuan(): void
    {
        // TODO (Trạng): MoHinh::thongKe($thang) -> 4 thẻ; MoHinh::phieuQuaHan(4) -> bảng cảnh báo; 2 khối tóm tắt trong ngày.
        $this->hienThi('tong-quan', [], 'quan-tri', 'Tổng quan thư viện');
    }

    public function quaHan(): void
    {
        $this->hienThi('qua-han', [], 'quan-tri', 'Phiếu mượn quá hạn');
    }

    public function nhacTra(string $id): void
    {
        // TODO (Trạng): GuiEmail::nhacTraSach((int) $id).
        $this->chuaLam('Nhắc trả sách', '/quan-tri');
    }

    public function xuatBaoCao(): void
    {
        // TODO (Trạng): XuatBaoCao::taoFile($thang) -> tải về Excel/PDF.
        $this->chuaLam('Xuất báo cáo', '/quan-tri');
    }
}
