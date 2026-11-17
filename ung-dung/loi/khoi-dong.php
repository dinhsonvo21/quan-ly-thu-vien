<?php
declare(strict_types=1);

/**
 * Khởi động chung cho web (public/index.php) và tác vụ nền (tac-vu-nen/*.php).
 */
define('GOC_DU_AN', dirname(__DIR__, 2));
define('UNG_DUNG', GOC_DU_AN . '/ung-dung');
define('THU_MUC_TAI_LEN', GOC_DU_AN . '/public/tep-tai-len');

require UNG_DUNG . '/cau-hinh/quy-dinh.php';

// Tự nạp lớp:
//   Loi\TenLop                -> ung-dung/loi/TenLop.php
//   ChucNang\XacThuc\TenLop   -> ung-dung/chuc-nang/xac-thuc/TenLop.php
spl_autoload_register(static function (string $lop): void {
    $phan = explode('\\', $lop);
    if ($phan[0] === 'Loi' && count($phan) === 2) {
        $tep = UNG_DUNG . '/loi/' . $phan[1] . '.php';
    } elseif ($phan[0] === 'ChucNang' && count($phan) === 3) {
        $thuMuc = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $phan[1]));
        $tep = UNG_DUNG . '/chuc-nang/' . $thuMuc . '/' . $phan[2] . '.php';
    } else {
        return;
    }
    if (is_file($tep)) {
        require $tep;
    }
});

require UNG_DUNG . '/loi/ham.php';

date_default_timezone_set((string) Loi\CauHinh::lay('ung-dung', 'mui_gio', 'Asia/Ho_Chi_Minh'));
error_reporting(E_ALL);
ini_set('display_errors', Loi\CauHinh::lay('ung-dung', 'che_do_go_loi', false) ? '1' : '0');
