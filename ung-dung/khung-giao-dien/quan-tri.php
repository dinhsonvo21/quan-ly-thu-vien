<?php
/** Khung cho Thủ thư / Quản trị viên. Biến: $noiDung, $tieuDe, $nguoiDung */
$hienTai = \Loi\DinhTuyen::duongDanHienTai();
$muc = [
    ['/quan-tri',           'Tổng quan',        [VAI_TRO_THU_THU, VAI_TRO_QUAN_TRI]],
    ['/quan-tri/kho-sach',  'Kho sách',         [VAI_TRO_THU_THU, VAI_TRO_QUAN_TRI]],
    ['/quan-tri/muon-tra',  'Mượn / Trả sách',  [VAI_TRO_THU_THU, VAI_TRO_QUAN_TRI]],
    ['/quan-tri/dat-cho',   'Đặt chỗ',          [VAI_TRO_THU_THU, VAI_TRO_QUAN_TRI]],
    ['/quan-tri/tai-khoan', 'Quản lý người dùng', [VAI_TRO_QUAN_TRI]],   // SR-10: chỉ Admin
];
$vaiTroHienThi = ['thu_thu' => 'Thủ thư', 'quan_tri' => 'Quản trị viên'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($tieuDe ?? '') !== '' ? $tieuDe . ' · Quản trị QNU Library' : 'Quản trị QNU Library') ?></title>
    <link rel="stylesheet" href="<?= e(tai_nguyen('css/giao-dien-chung.css')) ?>">
    <link rel="stylesheet" href="<?= e(tai_nguyen('css/giao-dien-quan-tri.css')) ?>">
</head>
<body>
<?php require __DIR__ . '/dau-trang.php'; ?>
<div class="bo-cuc-quan-tri">
    <aside class="thanh-ben">
        <p class="nhan">QUẢN TRỊ THƯ VIỆN</p>
        <p class="phu"><?= e($nguoiDung['ho_ten'] ?? '') ?> · <?= e($vaiTroHienThi[$nguoiDung['vai_tro'] ?? ''] ?? '') ?></p>
        <nav>
            <?php foreach ($muc as [$duongDan, $nhan, $vaiTro]): ?>
                <?php if (!in_array($nguoiDung['vai_tro'] ?? '', $vaiTro, true)) { continue; } ?>
                <a href="<?= e(url($duongDan)) ?>" class="<?= $hienTai === $duongDan ? 'dang-chon' : '' ?>"><?= e($nhan) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="quy-dinh">
            <strong>Quy định mượn sách</strong>
            <span>Giữ chỗ: <?= GIU_CHO_GIO ?> giờ</span>
            <span>Mượn sách: <?= HAN_MUON_NGAY ?> ngày</span>
            <span>Gia hạn: +<?= GIA_HAN_NGAY ?> ngày</span>
        </div>
    </aside>
    <main class="noi-dung-quan-tri">
        <?php require __DIR__ . '/thong-bao.php'; ?>
        <?= $noiDung ?>
    </main>
</div>
<footer class="cuoi-trang">© 2026 QNU Library</footer>
<script src="<?= e(tai_nguyen('js/xu-ly-chung.js')) ?>"></script>
<script src="<?= e(tai_nguyen('js/dem-nguoc.js')) ?>"></script>
<script src="<?= e(tai_nguyen('js/tinh-phi-phat.js')) ?>"></script>
</body>
</html>
