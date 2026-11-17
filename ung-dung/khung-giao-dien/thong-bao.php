<?php /** Thông báo flash: thanh_cong | loi | thong_tin */ ?>
<?php foreach (\Loi\PhienLamViec::layFlash() as $tb): ?>
    <div class="thong-bao thong-bao-<?= e($tb['loai']) ?>" role="status"><?= e($tb['noi_dung']) ?></div>
<?php endforeach; ?>
