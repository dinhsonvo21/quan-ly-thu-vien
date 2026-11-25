<?php
declare(strict_types=1);

namespace ChucNang\TongQuan;

use ChucNang\LuuThong\TinhPhiPhat;
use Loi\DinhDang;

/** Gửi email nhắc trả sách quá hạn (nút "Nhắc trả"). Cũng được tac-vu-nen/gui-nhac-tra.php gọi. */
final class GuiEmail
{
    /** true nếu mail() nhận gửi. Phiếu đã trả / chưa quá hạn / không tồn tại -> false. */
    public static function nhacTraSach(int $phieuMuonId): bool
    {
        $p = MoHinh::phieuDeNhac($phieuMuonId);
        if ($p === null || !filter_var($p['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $homNay = date('Y-m-d');
        $ngayTre = TinhPhiPhat::soNgayTre((string) $p['han_tra'], $homNay);
        $phi = TinhPhiPhat::phiQuaHan((string) $p['han_tra'], $homNay);
        $maPhieu = DinhDang::maPhieuMuon((int) $p['id']);

        $tieuDe = '=?UTF-8?B?' . base64_encode("[QNU Library] Nhắc trả sách quá hạn - {$maPhieu}") . '?=';
        $noiDung = "Chào {$p['sinh_vien']},\r\n\r\n"
            . "Phiếu mượn {$maPhieu} (sách \"{$p['ten_sach']}\") đã quá hạn {$ngayTre} ngày "
            . "(hạn trả: " . DinhDang::ngay((string) $p['han_tra']) . ").\r\n"
            . 'Phí quá hạn tạm tính đến hôm nay: ' . DinhDang::tien($phi) . ' (' . PHI_PHAT_MOI_NGAY . " đ/ngày/cuốn).\r\n\r\n"
            . 'Vui lòng mang sách đến ' . NOI_NHAN_MAC_DINH . " để trả sớm nhất có thể.\r\n\r\n"
            . "Trân trọng,\r\nQNU Library";

        $dau = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit";
        return @mail($p['email'], $tieuDe, $noiDung, $dau);
    }
}