<?php
declare(strict_types=1);

// Điểm vào duy nhất của ứng dụng: .htaccess đẩy mọi URL về file này.
require dirname(__DIR__) . '/ung-dung/loi/khoi-dong.php';

(new Loi\DinhTuyen())->chay();
