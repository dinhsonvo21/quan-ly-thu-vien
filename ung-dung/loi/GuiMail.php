<?php
declare(strict_types=1);

namespace Loi;

/** Gửi email khôi phục qua Gmail SMTP bằng STARTTLS. */
final class GuiMail
{
    private const MAY_CHU = 'smtp.gmail.com';
    private const CONG = 587;
    private const EMAIL_GUI = 'quanlyhoctap2026@gmail.com';

    public static function guiOtpKhoiPhuc(string $emailNhan, string $hoTen, string $maOtp): bool
    {
       $matKhauUngDung = getenv('GMAIL_APP_PASSWORD') ?: 'gziutbuucopmfttz';
        if (!is_string($matKhauUngDung) || trim($matKhauUngDung) === '') {
            error_log('Gmail SMTP is not configured: GMAIL_APP_PASSWORD is missing.');
            return false;
        }
        if (filter_var($emailNhan, FILTER_VALIDATE_EMAIL) === false || preg_match('/^\d{6}$/', $maOtp) !== 1) {
            error_log('Gmail SMTP message rejected: invalid recipient or OTP format.');
            return false;
        }

        $socket = null;
        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);
            $socket = @stream_socket_client(
                'tcp://' . self::MAY_CHU . ':' . self::CONG,
                $maLoi,
                $thongTinLoi,
                15,
                STREAM_CLIENT_CONNECT,
                $context
            );
            if ($socket === false) {
                throw new \RuntimeException('Unable to connect to the Gmail SMTP server.');
            }
            stream_set_timeout($socket, 15);

            self::docPhanHoi($socket, [220]);
            self::guiLenh($socket, 'EHLO localhost', [250]);
            self::guiLenh($socket, 'STARTTLS', [220]);
            $maHoa = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($maHoa !== true) {
                throw new \RuntimeException('Unable to establish TLS with the Gmail SMTP server.');
            }

            self::guiLenh($socket, 'EHLO localhost', [250]);
            self::guiLenh($socket, 'AUTH LOGIN', [334]);
            self::guiLenh($socket, base64_encode(self::EMAIL_GUI), [334]);
            self::guiLenh($socket, base64_encode((string) preg_replace('/\s+/', '', $matKhauUngDung)), [235]);
            self::guiLenh($socket, 'MAIL FROM:<' . self::EMAIL_GUI . '>', [250]);
            self::guiLenh($socket, 'RCPT TO:<' . $emailNhan . '>', [250, 251]);
            self::guiLenh($socket, 'DATA', [354]);

            $tenMaHoa = '=?UTF-8?B?' . base64_encode($hoTen) . '?=';
            $chuDe = '=?UTF-8?B?' . base64_encode('Mã OTP khôi phục mật khẩu - QNU Library') . '?=';
            $tenThuVien = '=?UTF-8?B?' . base64_encode('QNU Library') . '?=';
            $maOtpHtml = htmlspecialchars($maOtp, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $hoTenHtml = htmlspecialchars($hoTen, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $noiDung = '<!doctype html><html lang="vi"><body style="font-family:Arial,sans-serif;color:#222">'
                . '<h2>Khôi phục mật khẩu QNU Library</h2>'
                . '<p>Xin chào ' . $hoTenHtml . ',</p>'
                . '<p>Mã OTP để đặt lại mật khẩu của bạn là:</p>'
                . '<p style="font-size:30px;font-weight:bold;letter-spacing:8px">' . $maOtpHtml . '</p>'
                . '<p>Mã có hiệu lực trong 5 phút. Nếu bạn không yêu cầu khôi phục mật khẩu, hãy bỏ qua email này.</p>'
                . '</body></html>';
            $thongDiep = implode("\r\n", [
                'From: ' . $tenThuVien . ' <' . self::EMAIL_GUI . '>',
                'To: ' . $tenMaHoa . ' <' . $emailNhan . '>',
                'Subject: ' . $chuDe,
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
                '',
                $noiDung,
            ]);
            $thongDiep = preg_replace('/(?m)^\./', '..', $thongDiep) ?? $thongDiep;
            self::ghiDuLieu($socket, $thongDiep . "\r\n.\r\n");
            self::docPhanHoi($socket, [250]);
            self::guiLenh($socket, 'QUIT', [221]);

            return true;
        } catch (\RuntimeException $loi) {
            error_log('Gmail SMTP delivery failed: ' . $loi->getMessage());
            return false;
        } finally {
            if (is_resource($socket)) {
                fclose($socket);
            }
        }
    }

    /** @param resource $socket @param array<int, int> $maChapNhan */
    private static function guiLenh($socket, string $lenh, array $maChapNhan): void
    {
        self::ghiDuLieu($socket, $lenh . "\r\n");
        self::docPhanHoi($socket, $maChapNhan);
    }

    /** @param resource $socket */
    private static function ghiDuLieu($socket, string $duLieu): void
    {
        $doDai = strlen($duLieu);
        $daGhi = 0;
        while ($daGhi < $doDai) {
            $soByte = fwrite($socket, substr($duLieu, $daGhi));
            if ($soByte === false || $soByte === 0) {
                throw new \RuntimeException('Unable to write to the Gmail SMTP connection.');
            }
            $daGhi += $soByte;
        }
    }

    /** @param resource $socket @param array<int, int> $maChapNhan */
    private static function docPhanHoi($socket, array $maChapNhan): void
    {
        for ($soDong = 0; $soDong < 100; $soDong++) {
            $dong = fgets($socket, 515);
            if ($dong === false) {
                throw new \RuntimeException('No response from the Gmail SMTP server.');
            }
            if (preg_match('/^(\d{3})([ -])/', $dong, $khop) !== 1) {
                throw new \RuntimeException('Invalid response from the Gmail SMTP server.');
            }
            if ($khop[2] === ' ') {
                if (!in_array((int) $khop[1], $maChapNhan, true)) {
                    throw new \RuntimeException('Gmail SMTP server rejected a mail transaction step.');
                }
                return;
            }
        }

        throw new \RuntimeException('Gmail SMTP server response exceeded the expected limit.');
    }
}
