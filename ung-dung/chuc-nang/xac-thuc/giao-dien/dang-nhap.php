<?php
$duLieu = $duLieu ?? ['email' => ''];
$loi = $loi ?? [];
?>
<section style="max-width:1050px; margin:12px auto 28px; display:grid; grid-template-columns:minmax(0,1fr) minmax(360px,440px); gap:56px; align-items:center">
    <div style="min-width:0">
        <h1 style="margin:0 0 14px; font-size:36px">Chào mừng bạn quay lại thư viện.</h1>
        <p style="margin:0 0 28px; max-width:520px; color:var(--chu-phu)">Hành trình đọc sách của bạn tiếp tục tại đây. Đăng nhập để quản lý lịch sử mượn, đặt giữ chỗ và tiếp tục khám phá.</p>

        <div style="height:220px; padding:24px 28px 20px; border-radius:10px; background:var(--nen-xam); display:flex; align-items:flex-end; justify-content:center; gap:18px">
            <div aria-hidden="true" style="width:96px; height:150px; border:1px solid var(--vien); border-radius:5px; background:#dfe3e8; box-shadow:0 5px 12px rgba(0,0,0,.12)">
                <div style="height:18px; border-bottom:1px solid var(--vien)"></div>
            </div>
            <div aria-hidden="true" style="width:118px; height:180px; border:1px solid #bfc3c8; border-radius:5px; background:#aeb4bb; box-shadow:0 7px 16px rgba(0,0,0,.16)">
                <div style="height:20px; border-bottom:1px solid #9fa4a9"></div>
            </div>
            <div aria-hidden="true" style="width:96px; height:150px; border:1px solid var(--vien); border-radius:5px; background:#dfe3e8; box-shadow:0 5px 12px rgba(0,0,0,.12)">
                <div style="height:18px; border-bottom:1px solid var(--vien)"></div>
            </div>
        </div>
        <p style="margin:18px 0 0; text-align:center; color:var(--chu-phu); font-size:13px">Luôn có những câu chuyện hay chờ đón bạn trên kệ sách.</p>
    </div>

    <div style="border:1px solid var(--vien); border-radius:10px; background:var(--nen); padding:32px; box-shadow:0 8px 24px rgba(0,0,0,.04)">
        <h2 style="margin:0 0 6px">Đăng nhập vào tài khoản</h2>
        <p style="margin:0 0 18px; color:var(--chu-phu); font-size:14px">Nhập email và mật khẩu mà bạn đã đăng ký.</p>

        <?php if (isset($loi['chung'])): ?>
            <div class="thong-bao thong-bao-loi" role="alert"><?= e($loi['chung']) ?></div>
        <?php endif; ?>

        <form action="<?= e(url('/dang-nhap')) ?>" method="post">
            <?= csrf_field() ?>

            <label for="email">Địa chỉ Email</label>
            <input id="email" name="email" type="email" value="<?= e($duLieu['email'] ?? '') ?>" placeholder="you@example.com" autocomplete="email" required>
            <?php if (isset($loi['email'])): ?>
                <p class="thong-bao-loi" role="alert" style="margin:6px 0 0"><?= e($loi['email']) ?></p>
            <?php endif; ?>

            <label for="mat_khau">Mật khẩu</label>
            <div style="position:relative">
                <input id="mat_khau" name="mat_khau" type="password" placeholder="Nhập mật khẩu của bạn" autocomplete="current-password" style="padding-right:48px" required>
                <button type="button" id="hien_an_mat_khau" aria-controls="mat_khau" aria-label="Hiện mật khẩu" aria-pressed="false"
                        style="position:absolute; right:8px; top:50%; transform:translateY(-50%); width:34px; height:34px; padding:0; border:0; border-radius:4px; background:transparent; color:var(--chu); cursor:pointer">
                    <svg id="mat_khau_bieu_tuong" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2.5 12s3.5-5 9.5-5 9.5 5 9.5 5-3.5 5-9.5 5-9.5-5-9.5-5Z"></path>
                        <circle cx="12" cy="12" r="2.5"></circle>
                    </svg>
                </button>
            </div>
            <?php if (isset($loi['mat_khau'])): ?>
                <p class="thong-bao-loi" role="alert" style="margin:6px 0 0"><?= e($loi['mat_khau']) ?></p>
            <?php endif; ?>

            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; margin:14px 0 18px">
                <label for="ghi_nho" style="display:flex; align-items:center; gap:8px; margin:0; font-weight:400">
                    <input id="ghi_nho" name="ghi_nho" type="checkbox" value="1" style="width:auto; margin:0">
                    Ghi nhớ đăng nhập
                </label>
                <a href="<?= e(url('/quen-mat-khau')) ?>" style="font-size:14px; white-space:nowrap">Quên mật khẩu?</a>
            </div>

            <button class="nut" type="submit" style="width:100%">Đăng nhập</button>
        </form>

        <div style="text-align:center; margin-top:18px; font-size:14px">
            <p style="margin:0 0 6px">Chưa có tài khoản QNU Library? <a href="<?= e(url('/dang-ky')) ?>" style="font-weight:600">Tạo tài khoản</a></p>
            <p style="margin:0; color:var(--chu-phu)">Gặp sự cố đăng nhập? Liên hệ thư viện để được hỗ trợ.</p>
        </div>
    </div>
</section>

<style>
@media (max-width: 820px) {
    section > div:first-child { text-align: center; }
    section[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
}
</style>

<script>
document.getElementById('hien_an_mat_khau').addEventListener('click', function () {
    const matKhau = document.getElementById(this.getAttribute('aria-controls'));
    const dangHien = matKhau.type === 'password';
    matKhau.type = dangHien ? 'text' : 'password';
    this.setAttribute('aria-label', dangHien ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
    this.setAttribute('aria-pressed', dangHien ? 'true' : 'false');
});
</script>

<?php if (\Loi\CauHinh::lay('ung-dung', 'che_do_go_loi', false)): ?>
    <section class="khoi" style="margin-top:24px">
        <strong>Chế độ phát triển — đăng nhập nhanh để xem thử giao diện:</strong>
        <p>
            <a class="nut nut-vien" href="<?= e(url('/dev/dang-nhap-nhanh/ban_doc')) ?>">Bạn đọc</a>
            <a class="nut nut-vien" href="<?= e(url('/dev/dang-nhap-nhanh/thu_thu')) ?>">Thủ thư</a>
            <a class="nut nut-vien" href="<?= e(url('/dev/dang-nhap-nhanh/quan_tri')) ?>">Quản trị viên</a>
        </p>
    </section>
<?php endif; ?>
