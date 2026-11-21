<?php
$duLieu = $duLieu ?? [];
$loi = $loi ?? [];
?>
<section style="display:flex; flex-wrap:wrap; gap:32px; align-items:center">
    <div style="flex:1 1 420px; min-width:0">
        <p class="nhan">Thẻ thư viện số</p>
        <h1>Tri thức luôn trong tầm tay</h1>
        <p>Tạo tài khoản bạn đọc để bắt đầu hành trình khám phá kho tài liệu của QNU Library.</p>
        <ul>
            <li>Tra cứu sách và tài liệu mọi lúc, mọi nơi.</li>
            <li>Đặt giữ sách và theo dõi thời hạn nhận sách.</li>
            <li>Quản lý danh sách đọc và lịch sử mượn của bạn.</li>
        </ul>
    </div>

    <div style="flex:1 1 420px; min-width:0">
        <h2>Đăng ký tài khoản</h2>
        <form action="<?= e(url('/dang-ky')) ?>" method="post">
            <?= csrf_field() ?>

            <label for="ho_dem">Họ và tên đệm</label>
            <input id="ho_dem" name="ho_dem" type="text" value="<?= e($duLieu['ho_dem'] ?? '') ?>" autocomplete="family-name" required>
            <?php if (isset($loi['ho_dem'])): ?>
                <p class="thong-bao-loi" role="alert"><?= e($loi['ho_dem']) ?></p>
            <?php endif; ?>

            <label for="ten">Tên</label>
            <input id="ten" name="ten" type="text" value="<?= e($duLieu['ten'] ?? '') ?>" autocomplete="given-name" required>
            <?php if (isset($loi['ten'])): ?>
                <p class="thong-bao-loi" role="alert"><?= e($loi['ten']) ?></p>
            <?php endif; ?>

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= e($duLieu['email'] ?? '') ?>" autocomplete="email" required>
            <?php if (isset($loi['email'])): ?>
                <p class="thong-bao-loi" role="alert"><?= e($loi['email']) ?></p>
            <?php endif; ?>

            <label for="mat_khau">Mật khẩu</label>
            <div style="display:flex; gap:8px; align-items:center">
                <input id="mat_khau" name="mat_khau" type="password" autocomplete="new-password" required>
                <button class="nut nut-vien" type="button" id="hien_an_mat_khau" aria-controls="mat_khau" aria-pressed="false">Hiện</button>
            </div>
            <?php if (isset($loi['mat_khau'])): ?>
                <p class="thong-bao-loi" role="alert"><?= e($loi['mat_khau']) ?></p>
            <?php endif; ?>

            <label for="xac_nhan_mat_khau">Xác nhận mật khẩu</label>
            <input id="xac_nhan_mat_khau" name="xac_nhan_mat_khau" type="password" autocomplete="new-password" required>
            <?php if (isset($loi['xac_nhan_mat_khau'])): ?>
                <p class="thong-bao-loi" role="alert"><?= e($loi['xac_nhan_mat_khau']) ?></p>
            <?php endif; ?>

            <label for="dong_y_dieu_khoan">
                <input id="dong_y_dieu_khoan" name="dong_y_dieu_khoan" type="checkbox" value="1" style="width:auto" <?= !empty($duLieu['dong_y_dieu_khoan']) ? 'checked' : '' ?> required>
                Tôi đồng ý với điều khoản sử dụng thư viện.
            </label>
            <?php if (isset($loi['dong_y_dieu_khoan'])): ?>
                <p class="thong-bao-loi" role="alert"><?= e($loi['dong_y_dieu_khoan']) ?></p>
            <?php endif; ?>

            <p><button class="nut" type="submit">Tạo tài khoản</button></p>
        </form>
        <p>Đã có tài khoản? <a href="<?= e(url('/dang-nhap')) ?>">Đăng nhập</a></p>
    </div>
</section>

<script>
document.getElementById('hien_an_mat_khau').addEventListener('click', function () {
    const matKhau = document.getElementById(this.getAttribute('aria-controls'));
    const dangHien = matKhau.type === 'password';
    matKhau.type = dangHien ? 'text' : 'password';
    this.textContent = dangHien ? 'Ẩn' : 'Hiện';
    this.setAttribute('aria-pressed', dangHien ? 'true' : 'false');
});
</script>