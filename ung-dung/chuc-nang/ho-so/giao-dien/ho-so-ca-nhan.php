<?php
/**
 * SR-05 — Tài khoản của tôi & Lịch sử mượn - trả.
 * Biến: $hoSo, $datCho, $lichSu, $tabMo, $duLieu (?array, dữ liệu vừa nhập khi lỗi), $loiHoSo, $loiMatKhau
 */
use Loi\DinhDang;

$hoSo = $hoSo ?? [];
$datCho = $datCho ?? [];
$lichSu = $lichSu ?? [];
$loiHoSo = $loiHoSo ?? [];
$loiMatKhau = $loiMatKhau ?? [];
$tabMo = $tabMo ?? 'thong-tin';

// Giá trị ô nhập: ưu tiên dữ liệu vừa gõ (khi lỗi), nếu không thì lấy từ CSDL.
$giaTri = static fn (string $khoa): string => (string) (($duLieu ?? [])[$khoa] ?? $hoSo[$khoa] ?? '');
?>
<style>
.ho-so { max-width: 980px; margin: 0 auto; }
.ho-so h1 { margin: 4px 0 6px; font-size: 28px; }
.ho-so .mo-ta { margin: 0 0 24px; font-size: 13px; color: var(--chu-phu); }
.ho-so .nhan { font-size: 11px; }

.hs-tab-bar { display: flex; gap: 4px; border-bottom: 1px solid var(--vien); margin-bottom: -1px; overflow-x: auto; }
.hs-tab { appearance: none; border: 1px solid transparent; border-bottom: 0; background: none; font: inherit; font-size: 13px; color: var(--chu-phu); padding: 12px 22px; cursor: pointer; border-radius: var(--ban-kinh) var(--ban-kinh) 0 0; white-space: nowrap; }
.hs-tab:hover { color: var(--chu); }
.hs-tab[aria-selected="true"] { background: var(--nen-xam); border-color: var(--vien); color: var(--chu); font-weight: 600; margin-bottom: -1px; }
.hs-tab:focus-visible, .hs-nut:focus-visible { outline: 2px solid var(--nhan); outline-offset: 2px; }

.hs-bang-dieu-khien { background: var(--nen-xam); border: 1px solid var(--vien); border-radius: 0 0 var(--ban-kinh) var(--ban-kinh); padding: 24px 28px; }
.hs-bang-dieu-khien[hidden] { display: none; }
.hs-bang-dieu-khien h2 { margin: 0 0 4px; font-size: 17px; }
.hs-ghi-chu { margin: 0 0 20px; font-size: 12px; color: var(--chu-phu); }

.hs-luoi { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 40px; }
.hs-luoi.ba { grid-template-columns: repeat(3, 1fr); gap: 4px 20px; }
.ho-so label { font-size: 12px; font-weight: 500; margin: 12px 0 6px; }
.ho-so input { font-size: 13px; padding: 9px 12px; background: var(--nen); }
.ho-so input[readonly] { background: var(--nen-xam-dam); color: #555; cursor: not-allowed; padding-right: 36px; }
.hs-khoa { position: relative; }
.hs-khoa svg { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; stroke: #444; fill: none; stroke-width: 1.8; }
.hs-hanh-dong { display: flex; justify-content: flex-end; align-items: flex-end; }
.hs-hanh-dong.rieng { margin-top: 18px; }
.hs-loi { margin: 6px 0 0; font-size: 12px; color: var(--canh-bao); }

.hs-nut { display: inline-block; padding: 9px 18px; border: 1px solid var(--nhan); border-radius: var(--ban-kinh); background: var(--nhan); color: #fff; font: inherit; font-size: 12px; font-weight: 600; cursor: pointer; }
.hs-nut:hover { opacity: .88; }
.hs-nut.vien { background: var(--nen); color: var(--chu); border-color: var(--vien); font-weight: 500; padding: 7px 14px; }
.hs-nut.nho { padding: 7px 14px; }

.hs-bang-boc { background: var(--nen); border: 1px solid var(--vien); border-radius: var(--ban-kinh); overflow-x: auto; }
.hs-bang-boc table { min-width: 660px; font-size: 13px; }
.hs-bang-boc th { font-size: 12px; font-weight: 600; }
.hs-bang-boc tbody tr:last-child td { border-bottom: 0; }
.hs-bang-boc form { margin: 0; }
.hs-rong { text-align: center; color: var(--chu-phu); padding: 28px 14px !important; }
.hs-chan { margin: 14px 0 0; font-size: 12px; color: var(--chu-phu); }
[data-het-han] { font-variant-numeric: tabular-nums; }
[data-het-han].het-han { color: var(--canh-bao); }

@media (max-width: 720px) {
    .hs-luoi, .hs-luoi.ba { grid-template-columns: 1fr; }
    .hs-bang-dieu-khien { padding: 18px 16px; }
}
</style>

<div class="ho-so" id="ho-so" data-tab-mac-dinh="<?= e($tabMo) ?>">
   
    <h1>Tài khoản của tôi</h1>
    <p class="mo-ta">Cập nhật hồ sơ, theo dõi đặt chỗ và quản lý những cuốn sách bạn đang mượn.</p>

    <div class="hs-tab-bar" role="tablist" aria-label="Tài khoản của tôi">
        <button type="button" class="hs-tab" role="tab" id="tab-thong-tin" data-tab="thong-tin" aria-controls="thong-tin" aria-selected="true">Thông tin cá nhân</button>
        <button type="button" class="hs-tab" role="tab" id="tab-dat-cho" data-tab="dat-cho" aria-controls="dat-cho" aria-selected="false" tabindex="-1">Đặt chỗ đang hoạt động · <?= count($datCho) ?></button>
        <button type="button" class="hs-tab" role="tab" id="tab-lich-su" data-tab="lich-su" aria-controls="lich-su" aria-selected="false" tabindex="-1">Lịch sử mượn</button>
        <button type="button" class="hs-tab" role="tab" id="tab-doi-mat-khau" data-tab="doi-mat-khau" aria-controls="doi-mat-khau" aria-selected="false" tabindex="-1">Đổi mật khẩu</button>
    </div>

    <!-- (2) Thông tin cá nhân -->
    <section class="hs-bang-dieu-khien" id="thong-tin" role="tabpanel" aria-labelledby="tab-thong-tin">
        <h2>Thông tin cá nhân</h2>
        <p class="hs-ghi-chu">Mã sinh viên được đồng bộ từ hệ thống và không thể chỉnh sửa.</p>
        <form action="<?= e(url('/ho-so/cap-nhat')) ?>" method="post" novalidate>
            <?= csrf_field() ?>
            <div class="hs-luoi">
                <div>
                    <label for="ma_sinh_vien">Mã sinh viên</label>
                    <div class="hs-khoa">
                        <input id="ma_sinh_vien" type="text" value="<?= e($hoSo['ma_nguoi_dung'] ?? '') ?>" readonly aria-readonly="true">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                    </div>
                </div>
                <div>
                    <label for="ho_ten">Họ và tên</label>
                    <input id="ho_ten" name="ho_ten" type="text" value="<?= e($giaTri('ho_ten')) ?>" autocomplete="name" required>
                    <?php if (isset($loiHoSo['ho_ten'])): ?><p class="hs-loi" role="alert"><?= e($loiHoSo['ho_ten']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="email">Địa chỉ Email</label>
                    <input id="email" name="email" type="email" value="<?= e($giaTri('email')) ?>" autocomplete="email" required>
                    <?php if (isset($loiHoSo['email'])): ?><p class="hs-loi" role="alert"><?= e($loiHoSo['email']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="sdt">Số điện thoại</label>
                    <input id="sdt" name="sdt" type="tel" value="<?= e($giaTri('sdt')) ?>" autocomplete="tel">
                    <?php if (isset($loiHoSo['sdt'])): ?><p class="hs-loi" role="alert"><?= e($loiHoSo['sdt']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="khoa_lop">Khoa / Lớp</label>
                    <input id="khoa_lop" name="khoa_lop" type="text" value="<?= e($giaTri('khoa_lop')) ?>">
                    <?php if (isset($loiHoSo['khoa_lop'])): ?><p class="hs-loi" role="alert"><?= e($loiHoSo['khoa_lop']) ?></p><?php endif; ?>
                </div>
                <div class="hs-hanh-dong"><button type="submit" class="hs-nut">Lưu thay đổi</button></div>
            </div>
        </form>
    </section>

    <!-- (3) Đặt chỗ đang hoạt động -->
    <section class="hs-bang-dieu-khien" id="dat-cho" role="tabpanel" aria-labelledby="tab-dat-cho">
        <h2>Đặt chỗ đang hoạt động</h2>
        <p class="hs-ghi-chu">Giữ sách trong <?= (int) GIU_CHO_GIO ?> giờ kể từ khi xác nhận. Thời điểm xem: <?= e(date('d/m/Y, H:i')) ?>.</p>
        <div class="hs-bang-boc">
            <table>
                <thead>
                    <tr><th>Mã đặt chỗ</th><th>Tên sách</th><th>Nơi nhận</th><th>Hết hạn nhận</th><th>Còn lại (<?= (int) GIU_CHO_GIO ?>h)</th><th>Thao tác</th></tr>
                </thead>
                <tbody>
                <?php if ($datCho === []): ?>
                    <tr><td colspan="6" class="hs-rong">Bạn chưa có đặt chỗ nào đang hoạt động.</td></tr>
                <?php else: foreach ($datCho as $d): ?>
                    <tr>
                        <td><?= e($d['ma_dat_cho'] ?? DinhDang::maDatCho((int) $d['id'])) ?></td>
                        <td><?= e($d['ten_sach'] ?? '') ?></td>
                        <td><?= e($d['noi_nhan'] ?? NOI_NHAN_MAC_DINH) ?></td>
                        <td><?= e(DinhDang::ngayGio($d['het_han'] ?? null)) ?></td>
                        <td>
                            <?php if (!empty($d['het_han'])): ?>
                                <span data-het-han="<?= e((new DateTimeImmutable((string) $d['het_han']))->format('c')) ?>"><?= e(DinhDang::conLai((string) $d['het_han'])) ?></span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td>
                            <form action="<?= e(url('/dat-cho/' . (int) $d['id'] . '/huy')) ?>" method="post" onsubmit="return confirm('Bạn chắc chắn muốn hủy đặt chỗ này?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="hs-nut vien">Hủy đặt chỗ</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- (4)(5) Lịch sử mượn sách -->
    <section class="hs-bang-dieu-khien" id="lich-su" role="tabpanel" aria-labelledby="tab-lich-su">
        <h2>Lịch sử mượn sách</h2>
        <p class="hs-ghi-chu">Thời hạn mượn <?= (int) HAN_MUON_NGAY ?> ngày. Sách đủ điều kiện được gia hạn thêm <?= (int) GIA_HAN_NGAY ?> ngày.</p>
        <div class="hs-bang-boc">
            <table>
                <thead>
                    <tr><th>Mã phiếu</th><th>Tên sách</th><th>Ngày mượn</th><th>Hạn trả</th><th>Trạng thái</th><th>Thao tác</th></tr>
                </thead>
                <tbody>
                <?php if ($lichSu === []): ?>
                    <tr><td colspan="6" class="hs-rong">Bạn chưa mượn cuốn sách nào.</td></tr>
                <?php else: foreach ($lichSu as $p): ?>
                    <tr>
                        <td><?= e($p['ma_phieu']) ?></td>
                        <td><?= e($p['ten_sach']) ?></td>
                        <td><?= e(DinhDang::ngay($p['ngay_muon'])) ?></td>
                        <td><?= e(DinhDang::ngay($p['han_tra'])) ?></td>
                        <td><?= e($p['trang_thai_chu']) ?></td>
                        <td>
                            <?php if ($p['co_the_gia_han']): ?>
                                <form action="<?= e(url('/gia-han/' . (int) $p['id'])) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="hs-nut nho">Gia hạn (+<?= (int) GIA_HAN_NGAY ?> ngày)</button>
                                </form>
                            <?php elseif ($p['da_tra']): ?>
                                —
                            <?php else: ?>
                                <span title="<?= e($p['ly_do_khong_gia_han']) ?>">Không thể gia hạn</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <p class="hs-chan">Vui lòng hoàn tất phí quá hạn trước khi mượn thêm sách.</p>
    </section>

    <!-- (6) Đổi mật khẩu -->
    <section class="hs-bang-dieu-khien" id="doi-mat-khau" role="tabpanel" aria-labelledby="tab-doi-mat-khau">
        <h2>Đổi mật khẩu</h2>
        <p class="hs-ghi-chu">Dùng ít nhất <?= (int) MAT_KHAU_TOI_THIEU ?> ký tự, bao gồm chữ và số.</p>
        <form action="<?= e(url('/ho-so/doi-mat-khau')) ?>" method="post" novalidate>
            <?= csrf_field() ?>
            <div class="hs-luoi ba">
                <div>
                    <label for="mat_khau_hien_tai">Mật khẩu hiện tại</label>
                    <input id="mat_khau_hien_tai" name="mat_khau_hien_tai" type="password" autocomplete="current-password" required>
                    <?php if (isset($loiMatKhau['mat_khau_hien_tai'])): ?><p class="hs-loi" role="alert"><?= e($loiMatKhau['mat_khau_hien_tai']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="mat_khau_moi">Mật khẩu mới</label>
                    <input id="mat_khau_moi" name="mat_khau_moi" type="password" autocomplete="new-password" required>
                    <?php if (isset($loiMatKhau['mat_khau_moi'])): ?><p class="hs-loi" role="alert"><?= e($loiMatKhau['mat_khau_moi']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="xac_nhan_mat_khau">Xác nhận mật khẩu mới</label>
                    <input id="xac_nhan_mat_khau" name="xac_nhan_mat_khau" type="password" autocomplete="new-password" required>
                    <?php if (isset($loiMatKhau['xac_nhan_mat_khau'])): ?><p class="hs-loi" role="alert"><?= e($loiMatKhau['xac_nhan_mat_khau']) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="hs-hanh-dong rieng"><button type="submit" class="hs-nut">Cập nhật mật khẩu</button></div>
        </form>
    </section>
</div>

<script>
// Chuyển tab (không cần tải lại trang). Không có JS thì cả 4 phần vẫn hiện liên tiếp.
(function () {
    'use strict';
    var goc = document.getElementById('ho-so');
    var tabs = Array.prototype.slice.call(goc.querySelectorAll('.hs-tab'));
    var khung = {};
    tabs.forEach(function (t) { khung[t.getAttribute('data-tab')] = document.getElementById(t.getAttribute('data-tab')); });

    function mo(ten, ghiHash) {
        if (!khung[ten]) { ten = 'thong-tin'; }
        tabs.forEach(function (t) {
            var dung = t.getAttribute('data-tab') === ten;
            t.setAttribute('aria-selected', dung ? 'true' : 'false');
            t.tabIndex = dung ? 0 : -1;
            khung[t.getAttribute('data-tab')].hidden = !dung;
        });
        if (ghiHash) { history.replaceState(null, '', '#' + ten); }
    }

    tabs.forEach(function (t, i) {
        t.addEventListener('click', function () { mo(t.getAttribute('data-tab'), true); });
        t.addEventListener('keydown', function (ev) {
            var j = ev.key === 'ArrowRight' ? i + 1 : (ev.key === 'ArrowLeft' ? i - 1 : null);
            if (j === null) { return; }
            j = (j + tabs.length) % tabs.length;
            tabs[j].focus();
            mo(tabs[j].getAttribute('data-tab'), true);
        });
    });

    window.addEventListener('hashchange', function () { mo(location.hash.slice(1), false); });
    // Ưu tiên #hash trên URL (vd /ho-so#lich-su); không có thì dùng tab do server chỉ định (khi form báo lỗi).
    mo(location.hash.slice(1) || goc.getAttribute('data-tab-mac-dinh'), false);
})();
</script>
