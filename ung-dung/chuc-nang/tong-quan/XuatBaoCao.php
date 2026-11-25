<?php
declare(strict_types=1);

namespace ChucNang\TongQuan;

use Loi\DinhDang;

/** Xuất báo cáo thống kê theo tháng (nút "Xuất báo cáo"). */
final class XuatBaoCao
{
    /** @return string đường dẫn file CSV tạm (UTF-8 có BOM, mở được bằng Excel); '' nếu lỗi. */
    public static function taoFile(string $thang): string
    {
        $thang = MoHinh::chuanHoaThang($thang);
        $tk = MoHinh::thongKe($thang);
        $bc = MoHinh::baoCaoThang($thang);

        $tep = tempnam(sys_get_temp_dir(), 'bc');
        if ($tep === false || ($f = fopen($tep, 'wb')) === false) {
            return '';
        }
        fwrite($f, "\xEF\xBB\xBF");
        $ghi = static fn(array $dong) => fputcsv($f, $dong, ',', '"', '\\');

        [$nam, $th] = explode('-', $thang);
        $ghi(['BÁO CÁO THƯ VIỆN - THÁNG ' . $th . '/' . $nam]);
        $ghi(['Xuất lúc', date('d/m/Y H:i')]);
        $ghi([]);
        $ghi(['Chỉ số theo tháng', 'Giá trị']);
        $ghi(['Lượt mượn trong tháng', $bc['luot_muon']]);
        $ghi(['Lượt trả trong tháng', $bc['luot_tra']]);
        $ghi(['Phí phạt phát sinh (đ)', $bc['phi_phat_ghi']]);
        $ghi(['Phí phạt đã thu (đ)', $bc['phi_phat_da_thu']]);
        $ghi([]);
        $ghi(['Tình hình hiện tại', 'Giá trị']);
        $ghi(['Đầu sách đang lưu hành', $tk['dau_sach']]);
        $ghi(['Tổng số bản sao', $tk['ban_sao']]);
        $ghi(['Phiếu đang cho mượn', $tk['dang_cho_muon']]);
        $ghi(['Phiếu quá hạn', $tk['phieu_qua_han']]);
        $ghi(['Phí phạt chưa thu (đ)', $tk['phi_phat_chua_thu']]);
        $ghi([]);
        $ghi(['DANH SÁCH PHIẾU QUÁ HẠN HIỆN TẠI']);
        $ghi(['Mã phiếu', 'Sinh viên', 'Tên sách', 'Hạn trả', 'Quá hạn (ngày)', 'Phí tạm tính (đ)']);
        foreach (MoHinh::phieuQuaHan() as $p) {
            $ghi([
                DinhDang::maPhieuMuon($p['id']), $p['sinh_vien'], $p['ten_sach'],
                DinhDang::ngay((string) $p['han_tra']), $p['so_ngay_tre'], $p['phi_tam_tinh'],
            ]);
        }
        fclose($f);
        return $tep;
    }
}