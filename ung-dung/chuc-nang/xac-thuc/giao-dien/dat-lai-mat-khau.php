<?php
$duLieu = $duLieu ?? ['ma_otp' => ''];
$loi = $loi ?? [];
?>
<section style="max-width:480px; margin:40px auto; padding:32px; border:1px solid var(--vien); border-radius:10px; background:var(--nen); box-shadow:0 8px 24px rgba(0,0,0,.04)">
    <h1 style="margin:0 0 8px; font-size:28px">Đặt lại mật khẩu</h1>
    <p style="margin:0 0 22px; color:var(--chu-phu); font-size:14px">Nhập mã OTP đã gửi đến email và tạo mật khẩu mới. Mã có hiệu lực trong 5 phút.</p>

    <form action="<?= e(url('/dat-lai-mat-khau')) ?>" method="post">
        <?= csrf_field() ?>

        <label for="ma_otp">Mã OTP</label>
        <input id="ma_otp" name="ma_otp" type="text" value="<?= e($duLieu['ma_otp'] ?? '') ?>" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="000000" style="text-align:center; letter-spacing:8px; font-size:22px" required>
        <?php if (isset($loi['ma_otp'])): ?>
            <p class="thong-bao-loi" role="alert" style="margin:6px 0 0"><?= e($loi['ma_otp']) ?></p>
        <?php endif; ?>

        <label for="mat_khau_moi">Mật khẩu mới</label>
        <div style="position:relative">
            <input id="mat_khau_moi" name="mat_khau_moi" type="password" autocomplete="new-password" placeholder="Ít nhất 8 ký tự, gồm chữ và số" style="padding-right:48px" required>
            <button type="button" class="nut-hien-an-mat-khau" aria-controls="mat_khau_moi" aria-label="Hiện mật khẩu" aria-pressed="false"
                    style="position:absolute; right:8px; top:50%; transform:translateY(-50%); width:34px; height:34px; padding:0; border:0; border-radius:4px; background:transparent; color:var(--chu); cursor:pointer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2.5 12s3.5-5 9.5-5 9.5 5 9.5 5-3.5 5-9.5 5-9.5-5-9.5-5Z"></path>
                    <circle cx="12" cy="12" r="2.5"></circle>
                </svg>
            </button>
        </div>
        <?php if (isset($loi['mat_khau_moi'])): ?>
            <p class="thong-bao-loi" role="alert" style="margin:6px 0 0"><?= e($loi['mat_khau_moi']) ?></p>
        <?php endif; ?>

        <label for="xac_nhan_mat_khau">Xác nhận mật khẩu mới</label>
        <div style="position:relative">
            <input id="xac_nhan_mat_khau" name="xac_nhan_mat_khau" type="password" autocomplete="new-password" placeholder="Nhập lại mật khẩu mới" style="padding-right:48px" required>
            <button type="button" class="nut-hien-an-mat-khau" aria-controls="xac_nhan_mat_khau" aria-label="Hiện mật khẩu" aria-pressed="false"
                    style="position:absolute; right:8px; top:50%; transform:translateY(-50%); width:34px; height:34px; padding:0; border:0; border-radius:4px; background:transparent; color:var(--chu); cursor:pointer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2.5 12s3.5-5 9.5-5 9.5 5 9.5 5-3.5 5-9.5 5-9.5-5-9.5-5Z"></path>
                    <circle cx="12" cy="12" r="2.5"></circle>
                </svg>
            </button>
        </div>
        <?php if (isset($loi['xac_nhan_mat_khau'])): ?>
            <p class="thong-bao-loi" role="alert" style="margin:6px 0 0"><?= e($loi['xac_nhan_mat_khau']) ?></p>
        <?php endif; ?>

        <button class="nut" type="submit" style="width:100%; margin-top:20px">Cập nhật mật khẩu</button>
    </form>

    <p style="margin:20px 0 0; text-align:center; font-size:14px">
        <a href="<?= e(url('/quen-mat-khau')) ?>">Yêu cầu mã OTP mới</a>
    </p>
</section>

<script>
document.querySelectorAll('.nut-hien-an-mat-khau').forEach(function (nut) {
    nut.addEventListener('click', function () {
        const matKhau = document.getElementById(this.getAttribute('aria-controls'));
        const dangHien = matKhau.type === 'password';
        matKhau.type = dangHien ? 'text' : 'password';
        this.setAttribute('aria-label', dangHien ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        this.setAttribute('aria-pressed', dangHien ? 'true' : 'false');
    });
});
</script>
