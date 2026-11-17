<?php
/**
 * Khung "đang xây dựng" cho màn hình chưa làm. XÓA lời gọi này khi cài đặt giao diện thật.
 * Biến cần có: $ma, $ten, $nguoi, $viec (mảng các thành phần cần làm theo báo cáo)
 */
?>
<section class="dang-xay-dung">
    <p class="nhan"><?= e($ma) ?> · phụ trách: <?= e($nguoi) ?></p>
    <h1><?= e($ten) ?></h1>
    <p>Màn hình này chưa được cài đặt. Thành phần cần làm theo báo cáo:</p>
    <ul>
        <?php foreach ($viec as $v): ?><li><?= e($v) ?></li><?php endforeach; ?>
    </ul>
</section>
