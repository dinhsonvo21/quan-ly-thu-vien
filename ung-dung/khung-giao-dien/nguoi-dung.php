<?php /** Khung cho Khách / Bạn đọc. Biến: $noiDung, $tieuDe, $nguoiDung */ ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($tieuDe ?? '') !== '' ? $tieuDe . ' · QNU Library' : 'QNU Library') ?></title>
    <link rel="stylesheet" href="<?= e(tai_nguyen('css/giao-dien-chung.css')) ?>">
</head>
<body>
<?php require __DIR__ . '/dau-trang.php'; ?>
<main class="noi-dung<?= ($tieuDe ?? '') === 'Chi tiết sách' ? ' noi-dung-chi-tiet-sach' : '' ?>">
    <?php require __DIR__ . '/thong-bao.php'; ?>
    <?= $noiDung ?>
</main>
<footer class="cuoi-trang">© 2026 QNU Library</footer>
<script src="<?= e(tai_nguyen('js/xu-ly-chung.js')) ?>"></script>
<script src="<?= e(tai_nguyen('js/dem-nguoc.js')) ?>"></script>
</body>
</html>
