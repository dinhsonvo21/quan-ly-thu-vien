<?php
$ma = 'SR-03'; $ten = 'Đăng nhập'; $nguoi = 'Võ Đình Sơn';
$viec = [
    '(1) Banner chào mừng  ·  (2) Email  ·  (3) Mật khẩu + hiện/ẩn',
    '(4) Ghi nhớ đăng nhập  ·  (5) Quên mật khẩu?  ·  (6) Nút Đăng nhập  ·  (7) Liên kết Đăng ký / Hỗ trợ',
    'Phân quyền sau đăng nhập: Bạn đọc -> Trang chủ; Thủ thư / Quản trị -> /quan-tri (SR-06)',
];
require UNG_DUNG . '/khung-giao-dien/dang-xay-dung.php';
?>
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
