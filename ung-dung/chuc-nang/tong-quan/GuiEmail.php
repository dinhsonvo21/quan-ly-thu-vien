<?php
declare(strict_types=1);

namespace ChucNang\TongQuan;

/** Gửi email nhắc trả sách quá hạn (nút "Nhắc trả"). Cũng được tac-vu-nen/gui-nhac-tra.php gọi. */
final class GuiEmail
{
    public static function nhacTraSach(int $phieuMuonId): bool
    {
        // TODO (Trạng): lấy email bạn đọc + thông tin phiếu -> gửi (mail()/PHPMailer + SMTP) theo mẫu email nhắc.
        return false;
    }
}
