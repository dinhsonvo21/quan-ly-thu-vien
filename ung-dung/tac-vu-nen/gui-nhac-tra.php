<?php
declare(strict_types=1);

/**
 * (Tuỳ chọn) Mỗi ngày gửi email nhắc các phiếu mượn quá hạn / sắp đến hạn.
 * Chạy 1 lần/ngày bằng Task Scheduler hoặc cron, giống huy-dat-cho-qua-han.php.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/loi/khoi-dong.php';

// TODO (Trạng): foreach (TongQuan\MoHinh::phieuQuaHan() as $phieu) ChucNang\TongQuan\GuiEmail::nhacTraSach($phieu['id']);
echo "Chưa cài đặt.\n";
