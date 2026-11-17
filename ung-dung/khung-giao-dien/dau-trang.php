<?php
/** Header dùng chung. Biến có sẵn: $nguoiDung (mảng hoặc null). */
$laNhanVien = $nguoiDung !== null && in_array($nguoiDung['vai_tro'], \Loi\PhanQuyen::NHAN_VIEN, true);
?>
<header class="dau-trang">
    <a class="logo" href="<?= e(url('/')) ?>">
        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 6c-2-1.5-5-2-8-2v14c3 0 6 .5 8 2 2-1.5 5-2 8-2V4c-3 0-6 .5-8 2zM12 6v14"/></svg>
        QNU Library
    </a>
    <nav class="menu-chinh">
        <a href="<?= e(url('/')) ?>">Trang chủ</a>
        <a href="<?= e(url('/#danh-muc')) ?>">Danh mục sách</a>
        <a href="<?= e(url('/ho-so#lich-su')) ?>">Lịch sử mượn</a>
        <a href="<?= e(url('/noi-quy')) ?>">Nội quy</a>
    </nav>
    <div class="menu-tai-khoan">
        <?php if ($nguoiDung === null): ?>
            <a class="lien-ket-dam" href="<?= e(url('/dang-nhap')) ?>">Đăng nhập</a>
            <a class="nut nut-vien" href="<?= e(url('/dang-ky')) ?>">Đăng ký</a>
        <?php else: ?>
            <details class="nguoi-dung">
                <summary><?= e($nguoiDung['ho_ten']) ?></summary>
                <div class="danh-sach-chon">
                    <a href="<?= e(url('/ho-so')) ?>">Hồ sơ của tôi</a>
                    <?php if ($laNhanVien): ?><a href="<?= e(url('/quan-tri')) ?>">Trang quản trị</a><?php endif; ?>
                    <form method="post" action="<?= e(url('/dang-xuat')) ?>">
                        <?= csrf_field() ?>
                        <button type="submit">Đăng xuất</button>
                    </form>
                </div>
            </details>
        <?php endif; ?>
    </div>
</header>
