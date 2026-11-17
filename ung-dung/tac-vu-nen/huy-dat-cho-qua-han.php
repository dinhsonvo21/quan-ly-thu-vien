<?php
declare(strict_types=1);

/**
 * Tự hủy đơn giữ chỗ quá GIU_CHO_GIO (24h) và trả sách về kho (báo cáo 2.2.4).
 * Chạy MỖI 5-10 PHÚT:
 *   Windows (Laragon/XAMPP): Task Scheduler -> php.exe  <đường-dẫn>\ung-dung\tac-vu-nen\huy-dat-cho-qua-han.php
 *   Linux (crontab, mỗi 5 phút): xem lệnh mẫu trong README, mục tác vụ nền.
 * Phương án dự phòng: gọi DatCho\MoHinh::huyQuaHan() mỗi lần tải trang quản trị / SR-08.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/loi/khoi-dong.php';

$soDon = ChucNang\DatCho\MoHinh::huyQuaHan();
echo date('Y-m-d H:i:s') . " — đã hủy {$soDon} đơn quá hạn giữ chỗ\n";
