<?php
/**
 * SR-10 — Tài khoản & phân quyền.
 * @var array $boLoc
 * @var array $ds
 * @var int   $tong
 * @var int   $trang
 * @var int   $soTrang
 * @var array $thongKe
 * @var array|null $cu
 */
/** SR-10 — Tài khoản & phân quyền. Biến: $boLoc, $ds, $tong, $trang, $soTrang, $thongKe, $cu */
$so = static fn(int $n): string => number_format($n, 0, ',', '.');
$nhanVaiTro = ['ban_doc' => 'Sinh viên', 'thu_thu' => 'Thủ thư', 'quan_tri' => 'Quản trị viên'];
$nhanTrangThai = ['hoat_dong' => 'Hoạt động', 'tam_khoa' => 'Tạm khóa'];
$hienTai = Loi\PhienLamViec::nguoiDung() ?? [];

$lienKet = static function (int $t) use ($boLoc): string {
    $q = array_filter([
        'q'          => $boLoc['tu_khoa'],
        'vai_tro'    => $boLoc['vai_tro'],
        'trang_thai' => $boLoc['trang_thai'],
        'trang'      => $t > 1 ? $t : null,
    ], static fn($v) => $v !== null && $v !== '');
    return url('/quan-tri/tai-khoan' . ($q ? '?' . http_build_query($q) : ''));
};

$soDong = ChucNang\TaiKhoan\MoHinh::SO_DONG_MOI_TRANG;
$tu = ($trang - 1) * $soDong + 1;
$den = $tu + count($ds) - 1;
?>
<link rel="stylesheet" href="<?= e(tai_nguyen('css/tai-khoan.css')) ?>">

<p class="nhan">SR-10 / User Account &amp; Role Management</p>
<div class="tk-tieu-de">
    <div>
        <h1>Tài khoản &amp; phân quyền</h1>
        <p class="phu">Quản lý tài khoản sinh viên, thủ thư và quyền truy cập hệ thống thư viện.</p>
    </div>
    <button type="button" class="nut" id="nut-tao-thu-thu">+ Tạo tài khoản thủ thư</button>
</div>

<div class="the-thong-ke">
    <div><strong><?= e($so($thongKe['tong'])) ?></strong><span class="phu">Tổng tài khoản</span></div>
    <div><strong><?= e($so($thongKe['ban_doc'])) ?></strong><span class="phu">Sinh viên</span></div>
    <div><strong><?= e($so($thongKe['thu_thu'])) ?></strong><span class="phu">Thủ thư</span></div>
    <div><strong><?= e($so($thongKe['quan_tri'])) ?></strong><span class="phu">Quản trị viên</span></div>
</div>

<form method="get" action="<?= e(url('/quan-tri/tai-khoan')) ?>" class="tk-bo-loc" data-tu-gui>
    <input type="search" name="q" value="<?= e($boLoc['tu_khoa']) ?>" placeholder="Tìm theo ID, họ tên hoặc email..." aria-label="Tìm tài khoản">
    <select name="vai_tro" aria-label="Lọc theo vai trò">
        <option value="">Tất cả vai trò</option>
        <?php foreach ($nhanVaiTro as $gt => $nhan): ?>
            <option value="<?= e($gt) ?>" <?= $boLoc['vai_tro'] === $gt ? 'selected' : '' ?>><?= e($nhan) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="trang_thai" aria-label="Lọc theo trạng thái">
        <option value="">Tất cả trạng thái</option>
        <?php foreach ($nhanTrangThai as $gt => $nhan): ?>
            <option value="<?= e($gt) ?>" <?= $boLoc['trang_thai'] === $gt ? 'selected' : '' ?>><?= e($nhan) ?></option>
        <?php endforeach; ?>
    </select>
    <noscript><button type="submit" class="nut nut-vien">Lọc</button></noscript>
</form>

<div class="tk-cuon">
<table class="tk-bang">
    <thead>
        <tr><th>ID</th><th>Họ và tên</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th>Thao tác</th></tr>
    </thead>
    <tbody>
    <?php if ($ds === []): ?>
        <tr><td colspan="6" class="tk-trong">Không tìm thấy tài khoản nào.</td></tr>
    <?php endif; ?>
    <?php foreach ($ds as $t): ?>
        <?php $laMinh = ($hienTai['ma'] ?? '') !== '' && ($t['ma_nguoi_dung'] ?? '') !== ''
            ? $hienTai['ma'] === $t['ma_nguoi_dung']
            : (int) $t['id'] === (int) ($hienTai['id'] ?? 0); $khoa = $t['trang_thai'] === 'tam_khoa'; ?>
        <tr>
            <td><?= e($t['ma_nguoi_dung'] ?? '—') ?></td>
            <td><?= e($t['ho_ten']) ?></td>
            <td><?= e($t['email']) ?></td>
            <td><?= e($nhanVaiTro[$t['vai_tro']] ?? $t['vai_tro']) ?></td>
            <td><span class="tk-nhan-tt <?= $khoa ? 'khoa' : 'hoat-dong' ?>"><?= e($nhanTrangThai[$t['trang_thai']] ?? $t['trang_thai']) ?></span></td>
            <td class="tk-thao-tac">
                <?php if ($laMinh): ?>
                    <a class="tk-hanh-dong" href="<?= e(url('/ho-so')) ?>">Xem tài khoản</a>
                    <span class="phu nho">Tài khoản hiện tại</span>
                <?php else: ?>
                    <button type="button" class="tk-hanh-dong tk-nut-sua"
                            data-id="<?= e($t['id']) ?>" data-ma="<?= e($t['ma_nguoi_dung'] ?? '') ?>"
                            data-ten="<?= e($t['ho_ten']) ?>" data-email="<?= e($t['email']) ?>"
                            data-vai-tro="<?= e($t['vai_tro']) ?>">Sửa / Đổi vai trò</button>
                    <form method="post" action="<?= e(url('/quan-tri/tai-khoan/' . $t['id'] . ($khoa ? '/mo-khoa' : '/khoa'))) ?>"
                          data-xac-nhan="<?= e(($khoa ? 'Mở khóa' : 'Khóa') . ' tài khoản ' . $t['ho_ten'] . '?') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="tk-hanh-dong phu"><?= $khoa ? 'Mở khóa tài khoản' : 'Khóa tài khoản' ?></button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<div class="tk-chan-bang">
    <span class="phu nho"><?= $tong > 0 ? 'Hiển thị ' . e($so($tu)) . '–' . e($so($den)) . ' / ' . e($so($tong)) . ' tài khoản' : 'Không có kết quả' ?></span>
    <?php if ($soTrang > 1): ?>
        <nav class="tk-phan-trang" aria-label="Phân trang">
            <?php if ($trang > 1): ?><a href="<?= e($lienKet($trang - 1)) ?>">Trang trước</a><?php endif; ?>
            <?php for ($i = max(1, $trang - 2); $i <= min($soTrang, $trang + 2); $i++): ?>
                <a class="<?= $i === $trang ? 'dang-chon' : '' ?>" href="<?= e($lienKet($i)) ?>" <?= $i === $trang ? 'aria-current="page"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($trang < $soTrang): ?><a href="<?= e($lienKet($trang + 1)) ?>">Trang sau</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</div>

<section class="khoi tk-quyen">
    <h2>Quyền truy cập theo vai trò</h2>
    <div class="tk-quyen-luoi">
        <div><strong>Sinh viên</strong><span class="phu">Tra cứu, đặt chỗ và quản lý mượn sách cá nhân.</span></div>
        <div><strong>Thủ thư</strong><span class="phu">Quản lý kho, giao sách, mượn trả và thu phí.</span></div>
        <div><strong>Quản trị viên</strong><span class="phu">Toàn quyền hệ thống và phân quyền tài khoản.</span></div>
    </div>
</section>

<?php /* Popup 1 — tạo tài khoản thủ thư */ ?>
<dialog id="hop-tao-thu-thu" class="hop-thoai tk-hop" data-cu="<?= e(json_encode($cu, JSON_UNESCAPED_UNICODE)) ?>">
    <form method="post" action="<?= e(url('/quan-tri/tai-khoan/tao')) ?>" autocomplete="off">
        <?= csrf_field() ?>
        <div class="hop-dau">
            <h2>Tạo tài khoản thủ thư</h2>
            <button type="button" class="nut-dong" data-dong aria-label="Đóng">&times;</button>
        </div>
        <p class="phu">Mã thủ thư (TT-xxx) được tạo tự động. Thủ thư nên đổi mật khẩu ở lần đăng nhập đầu tiên.</p>
        <div class="luoi-form">
            <label class="cot-2">Họ và tên *
                <input type="text" name="ho_ten" maxlength="90" placeholder="Nguyễn Văn A" required>
            </label>
            <label class="cot-2">Email *
                <input type="email" name="email" maxlength="120" placeholder="ten@qnu.edu.vn" required>
            </label>
            <label class="cot-2">Mật khẩu tạm *
                <input type="password" name="mat_khau" minlength="<?= MAT_KHAU_TOI_THIEU ?>" autocomplete="new-password" required>
                <span class="phu nho">Tối thiểu <?= MAT_KHAU_TOI_THIEU ?> ký tự, gồm cả chữ và số.</span>
            </label>
        </div>
        <div class="hop-chan">
            <button type="button" class="nut nut-vien" data-dong>Hủy</button>
            <button type="submit" class="nut">Tạo tài khoản</button>
        </div>
    </form>
</dialog>

<?php /* Popup 2 — sửa / đổi vai trò */ ?>
<dialog id="hop-doi-vai-tro" class="hop-thoai tk-hop" data-mau-url="<?= e(url('/quan-tri/tai-khoan/__ID__/doi-vai-tro')) ?>">
    <form method="post" action="">
        <?= csrf_field() ?>
        <div class="hop-dau">
            <h2>Sửa / Đổi vai trò</h2>
            <button type="button" class="nut-dong" data-dong aria-label="Đóng">&times;</button>
        </div>
        <p class="phu">Chỉ vai trò được thay đổi; thông tin cá nhân do chủ tài khoản tự cập nhật.</p>
        <div class="luoi-form">
            <label>ID
                <input type="text" data-o="ma" disabled>
            </label>
            <label>Họ và tên
                <input type="text" data-o="ten" disabled>
            </label>
            <label class="cot-2">Email
                <input type="text" data-o="email" disabled>
            </label>
            <label class="cot-2">Vai trò *
                <select name="vai_tro" required>
                    <?php foreach ($nhanVaiTro as $gt => $nhan): ?>
                        <option value="<?= e($gt) ?>"><?= e($nhan) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <p class="cot-2 phu nho" data-canh-bao hidden>Quản trị viên có toàn quyền hệ thống, gồm cả phân quyền tài khoản. Chỉ cấp cho người thật sự cần.</p>
        </div>
        <div class="hop-chan">
            <button type="button" class="nut nut-vien" data-dong>Hủy</button>
            <button type="submit" class="nut">Lưu vai trò</button>
        </div>
    </form>
</dialog>

<script src="<?= e(tai_nguyen('js/tai-khoan.js')) ?>"></script>
