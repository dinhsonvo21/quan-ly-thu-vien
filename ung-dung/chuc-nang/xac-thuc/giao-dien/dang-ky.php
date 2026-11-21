<?php
$duLieu = $duLieu ?? [];
$loi = $loi ?? [];
?>
<section style="display:flex; flex-wrap:wrap; gap:48px; align-items:flex-start; justify-content:space-between; max-width:1040px; margin:16px auto;">
    <!-- Cột trái: Giới thiệu & 3 tiện ích có icon -->
    <div style="flex:1 1 400px; min-width:0; padding-top:12px;">
        <p class="nhan" style="font-size:12px; margin-bottom:12px;"></p>
        <h1 style="font-size:36px; margin:0 0 16px; letter-spacing:-0.02em;">Thẻ thư viện số.<br>Khám phá vô hạn tri thức.</h1>
        <p style="color:var(--chu-phu); font-size:16px; margin:0 0 28px;">Tham gia Thư viện QNU để mượn sách, theo dõi lịch sử đọc và khám phá những tài liệu mới.</p>

        <hr style="border:0; border-top:1px solid var(--vien); margin:0 0 28px;">

        <div style="display:flex; flex-direction:column; gap:20px; margin-bottom:28px;">
            <div style="display:flex; gap:14px; align-items:flex-start;">
                <div style="width:36px; height:36px; border:1px solid var(--vien); border-radius:var(--ban-kinh); display:flex; align-items:center; justify-content:center; flex-shrink:0; background:var(--nen);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <div>
                    <div style="font-weight:600; font-size:14px; margin-bottom:2px;">Tìm kiếm sách hay</div>
                    <div style="color:var(--chu-phu); font-size:13px;">Khám phá hàng trăm đầu sách thuộc mọi chuyên ngành.</div>
                </div>
            </div>

            <div style="display:flex; gap:14px; align-items:flex-start;">
                <div style="width:36px; height:36px; border:1px solid var(--vien); border-radius:var(--ban-kinh); display:flex; align-items:center; justify-content:center; flex-shrink:0; background:var(--nen);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div>
                    <div style="font-weight:600; font-size:14px; margin-bottom:2px;">Đặt giữ chỗ trực tuyến</div>
                    <div style="color:var(--chu-phu); font-size:13px;">Chọn sách và nhận tài liệu trực tiếp tại thư viện trường.</div>
                </div>
            </div>

            <div style="display:flex; gap:14px; align-items:flex-start;">
                <div style="width:36px; height:36px; border:1px solid var(--vien); border-radius:var(--ban-kinh); display:flex; align-items:center; justify-content:center; flex-shrink:0; background:var(--nen);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </div>
                <div>
                    <div style="font-weight:600; font-size:14px; margin-bottom:2px;">Quản lý lịch sử mượn</div>
                    <div style="color:var(--chu-phu); font-size:13px;">Theo dõi danh sách mượn, hạn trả và các đơn đặt chỗ.</div>
                </div>
            </div>
        </div>

        <p style="color:var(--chu-phu); font-size:12px; margin:0;">Đăng ký tài khoản miễn phí để bắt đầu sử dụng các dịch vụ thư viện.</p>
    </div>

    <!-- Cột phải: Thẻ card đăng ký -->
    <div style="flex:1 1 440px; min-width:0; border:1px solid var(--vien); border-radius:10px; padding:32px; background:var(--nen);">
        <h2 style="margin:0 0 6px; font-size:24px;">Tạo tài khoản của bạn</h2>
        <p style="margin:0 0 20px; color:var(--chu-phu); font-size:13px;">Nhập thông tin chi tiết để bắt đầu. Tất cả các trường là bắt buộc.</p>

        <form action="<?= e(url('/dang-ky')) ?>" method="post">
            <?= csrf_field() ?>

            <!-- Họ đệm & Tên nằm cùng 1 hàng 2 cột -->
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <div style="flex:1 1 180px; min-width:0;">
                    <label for="ho_dem" style="margin-top:0;">Họ và tên đệm</label>
                    <input id="ho_dem" name="ho_dem" type="text" placeholder="Nhập họ và tên đệm" value="<?= e($duLieu['ho_dem'] ?? '') ?>" autocomplete="family-name" required>
                    <?php if (isset($loi['ho_dem'])): ?>
                        <p class="thong-bao-loi" role="alert" style="font-size:13px; margin:4px 0 0;"><?= e($loi['ho_dem']) ?></p>
                    <?php endif; ?>
                </div>
                <div style="flex:1 1 140px; min-width:0;">
                    <label for="ten" style="margin-top:0;">Tên</label>
                    <input id="ten" name="ten" type="text" placeholder="Nhập tên của bạn" value="<?= e($duLieu['ten'] ?? '') ?>" autocomplete="given-name" required>
                    <?php if (isset($loi['ten'])): ?>
                        <p class="thong-bao-loi" role="alert" style="font-size:13px; margin:4px 0 0;"><?= e($loi['ten']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <label for="email">Địa chỉ Email</label>
            <input id="email" name="email" type="email" placeholder="you@example.com" value="<?= e($duLieu['email'] ?? '') ?>" autocomplete="email" required>
            <?php if (isset($loi['email'])): ?>
                <p class="thong-bao-loi" role="alert" style="font-size:13px; margin:4px 0 0;"><?= e($loi['email']) ?></p>
            <?php endif; ?>

            <label for="mat_khau">Mật khẩu</label>
            <div style="position:relative; display:flex; align-items:center;">
                <input id="mat_khau" name="mat_khau" type="password" placeholder="Tạo mật khẩu" autocomplete="new-password" style="padding-right:40px;" required>
                <button type="button" class="nut-mat" data-target="mat_khau" aria-label="Hiện hoặc ẩn mật khẩu" aria-pressed="false" style="position:absolute; right:10px; background:none; border:0; padding:4px; cursor:pointer; color:var(--chu-phu); display:flex; align-items:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <p style="margin:6px 0 0; font-size:12px; color:var(--chu-phu);">Sử dụng tối thiểu 8 ký tự, bao gồm chữ cái và số.</p>
            <?php if (isset($loi['mat_khau'])): ?>
                <p class="thong-bao-loi" role="alert" style="font-size:13px; margin:4px 0 0;"><?= e($loi['mat_khau']) ?></p>
            <?php endif; ?>

            <label for="xac_nhan_mat_khau">Xác nhận mật khẩu</label>
            <div style="position:relative; display:flex; align-items:center;">
                <input id="xac_nhan_mat_khau" name="xac_nhan_mat_khau" type="password" placeholder="Nhập lại mật khẩu" autocomplete="new-password" style="padding-right:40px;" required>
                <button type="button" class="nut-mat" data-target="xac_nhan_mat_khau" aria-label="Hiện hoặc ẩn xác nhận mật khẩu" aria-pressed="false" style="position:absolute; right:10px; background:none; border:0; padding:4px; cursor:pointer; color:var(--chu-phu); display:flex; align-items:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <?php if (isset($loi['xac_nhan_mat_khau'])): ?>
                <p class="thong-bao-loi" role="alert" style="font-size:13px; margin:4px 0 0;"><?= e($loi['xac_nhan_mat_khau']) ?></p>
            <?php endif; ?>

            <label for="dong_y_dieu_khoan" style="display:flex; align-items:center; gap:8px; font-weight:400; font-size:13px; margin:16px 0 20px; cursor:pointer;">
                <input id="dong_y_dieu_khoan" name="dong_y_dieu_khoan" type="checkbox" value="1" style="width:16px; height:16px; margin:0;" <?= !empty($duLieu['dong_y_dieu_khoan']) ? 'checked' : '' ?> required>
                <span>Tôi đồng ý với <strong>Điều khoản dịch vụ</strong> và <strong>Chính sách bảo mật</strong>.</span>
            </label>
            <?php if (isset($loi['dong_y_dieu_khoan'])): ?>
                <p class="thong-bao-loi" role="alert" style="font-size:13px; margin:-12px 0 12px;"><?= e($loi['dong_y_dieu_khoan']) ?></p>
            <?php endif; ?>

            <button class="nut" type="submit" style="width:100%; padding:12px;">Tạo tài khoản</button>
        </form>

        <div style="margin-top:24px; padding-top:20px; border-top:1px solid var(--vien); text-align:center; font-size:13px; color:var(--chu-phu);">
            Đã có tài khoản? <a href="<?= e(url('/dang-nhap')) ?>" class="lien-ket-dam" style="color:var(--chu);">Đăng nhập</a>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('.nut-mat').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const input = document.getElementById(this.dataset.target);
        const dangHien = input.type === 'password';
        input.type = dangHien ? 'text' : 'password';
        this.setAttribute('aria-pressed', dangHien ? 'true' : 'false');
        this.style.color = dangHien ? 'var(--chu)' : 'var(--chu-phu)';
    });
});
</script>