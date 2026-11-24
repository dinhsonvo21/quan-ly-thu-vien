<?php
$duLieu = $duLieu ?? ['email' => ''];
$loi = $loi ?? [];
?>
<section style="max-width:480px; margin:40px auto; padding:32px; border:1px solid var(--vien); border-radius:10px; background:var(--nen); box-shadow:0 8px 24px rgba(0,0,0,.04)">
    <h1 style="margin:0 0 8px; font-size:28px">Khôi phục mật khẩu</h1>
    <p style="margin:0 0 22px; color:var(--chu-phu); font-size:14px">Nhập địa chỉ email đã đăng ký để nhận mã OTP đặt lại mật khẩu. Mã có hiệu lực trong 5 phút.</p>

    <form action="<?= e(url('/quen-mat-khau')) ?>" method="post">
        <?= csrf_field() ?>

        <label for="email">Địa chỉ Email</label>
        <input id="email" name="email" type="email" value="<?= e($duLieu['email'] ?? '') ?>" placeholder="you@example.com" autocomplete="email" required>
        <?php if (isset($loi['email'])): ?>
            <p class="thong-bao-loi" role="alert" style="margin:6px 0 0"><?= e($loi['email']) ?></p>
        <?php endif; ?>

        <button class="nut" type="submit" style="width:100%; margin-top:18px">Gửi yêu cầu khôi phục</button>
    </form>

    <div style="margin-top:22px; padding:14px 16px; border:1px solid var(--vien); border-radius:8px; background:var(--nen-xam); font-size:14px">
        <strong>Hỗ trợ trực tiếp</strong>
        <p style="margin:6px 0 0; color:var(--chu-phu)">Nếu không nhận được email hoặc quên địa chỉ email, vui lòng mang thẻ sinh viên đến quầy Thủ thư để được cấp lại mật khẩu.</p>
    </div>

    <p style="margin:20px 0 0; text-align:center; font-size:14px">
        <a href="<?= e(url('/dang-nhap')) ?>">Quay lại đăng nhập</a>
    </p>
</section>
