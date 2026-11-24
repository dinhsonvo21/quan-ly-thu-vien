<?php
use Loi\DinhDang;

$sach = $sach ?? [];
$nguoiDung = $nguoiDung ?? null;
$dieuKienDatCho = $dieuKienDatCho ?? null;
$daTrongDanhSachDoc = $daTrongDanhSachDoc ?? false;
$sachId = (int) ($sach['id'] ?? 0);
$conSan = (int) ($sach['ban_sao_san_co'] ?? 0);
$tongBanSao = (int) ($sach['tong_ban_sao'] ?? 0);
$anhBia = trim((string) ($sach['anh_bia'] ?? ''));
$laBanDoc = $nguoiDung !== null && ($nguoiDung['vai_tro'] ?? '') === VAI_TRO_BAN_DOC;
$datChoHienTai = $dieuKienDatCho['dat_cho'] ?? null;
$coTheDatCho = $laBanDoc
    && $conSan > 0
    && !is_array($datChoHienTai)
    && !empty($dieuKienDatCho['du_dieu_kien']);
$urlAnhBia = $anhBia !== ''
    ? url('/tep-tai-len/anh-bia-sach/' . rawurlencode($anhBia))
    : '';
$theTags = array_filter([
    (string) ($sach['ten_the_loai'] ?? ''),
    (string) ($sach['loai_tai_lieu'] ?? ''),
]);
$thongTinSach = [
    ['Nhà xuất bản', $sach['nha_xuat_ban'] ?: 'Đang cập nhật'],
    ['Năm xuất bản', $sach['nam_xuat_ban'] ?? '—'],
    ['Ngôn ngữ', $sach['ngon_ngu'] ?? '—'],
    ['Mã ISBN', $sach['isbn'] ?: '—'],
];
?>
<style>
.ct-sach {
    --ct-vien: var(--vien);
    --ct-xam: var(--nen-xam);
    width: 100%;
    max-width: 1240px;
    margin: 0 auto;
    padding: 44px 24px 64px;
}
.ct-breadcrumb { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin: 0 0 24px; color: var(--chu-phu); font-size: 13px; }
.ct-breadcrumb a { text-decoration: none; }
.ct-breadcrumb a:hover { text-decoration: underline; }
.ct-luoi-chinh { display: grid; grid-template-columns: 280px minmax(0, 1fr) 340px; gap: 40px; align-items: start; }
.ct-cot-trai, .ct-cot-giua { min-width: 0; }
.ct-bia { width: 100%; aspect-ratio: 2 / 3; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid var(--ct-vien); border-radius: 12px; background: linear-gradient(145deg, #eff6ff, #e2e8f0); }
.ct-bia img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s ease; }
.ct-cot-trai:hover .ct-bia img { transform: scale(1.03); }
.ct-bia span { color: var(--chu-phu); font-size: 14px; }
.ct-nut-phu { display: flex; width: 100%; height: 44px; align-items: center; justify-content: center; margin-top: 12px; padding: 0 12px; border: 1px solid var(--ct-vien); border-radius: 8px; background: #fff; color: var(--nhan); font: inherit; font-size: .95rem; font-weight: 600; text-align: center; text-decoration: none; cursor: pointer; transition: background-color .18s ease, border-color .18s ease, transform .18s ease; }
.ct-nut-phu:hover { border-color: #bfdbfe; background: var(--nhan-mem); }
.ct-nut-phu:active { transform: scale(.98); }
.ct-nut-phu:disabled { color: var(--chu-phu); cursor: default; }
.ct-ma-sach { margin: 9px 0 0; color: var(--chu-phu); font-size: 12px; text-align: center; }
.ct-the-loai { display: inline-block; margin: 2px 0 8px; color: var(--nhan); font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
.ct-tieu-de { margin: 0 0 8px; font-size: 2.1rem; line-height: 1.25; }
.ct-tac-gia { margin: 0 0 20px; color: var(--chu-phu); font-size: 1.05rem; }
.ct-duong-ke { height: 1px; border: 0; margin: 20px 0; background: var(--ct-vien); }
.ct-gioi-thieu { font-size: 1rem; line-height: 1.7; }
.ct-gioi-thieu h2 { margin: 0 0 8px; font-size: 1.15rem; }
.ct-gioi-thieu p { margin: 0; color: #4b5563; white-space: normal; }
.ct-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
.ct-tags span { padding: 5px 10px; border: 1px solid #bfdbfe; border-radius: 8px; background: var(--nhan-mem); color: var(--nhan); font-size: 12px; }
.ct-thuoc-tinh { margin: 0; font-size: 1rem; }
.ct-thuoc-tinh div { display: grid; grid-template-columns: minmax(115px, .75fr) minmax(0, 1.25fr); gap: 12px; padding: 14px 0; border-bottom: 1px solid var(--ct-vien); }
.ct-thuoc-tinh div:last-child { border-bottom: 0; }
.ct-thuoc-tinh dt, .ct-thuoc-tinh dd { font-size: 1rem; }
.ct-thuoc-tinh dt { color: var(--chu-phu); }
.ct-thuoc-tinh dd { margin: 0; overflow-wrap: anywhere; font-weight: 600; }
.ct-the-muon { padding: 28px; border: 1px solid var(--ct-vien); border-radius: 12px; background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, .04); }
.ct-the-muon h2 { margin: 0 0 18px; font-size: 1.25rem; }
.ct-trang-thai { display: flex; align-items: center; gap: 9px; margin: 0 0 6px; font-size: .98rem; font-weight: 600; }
.ct-cham { width: 8px; height: 8px; flex: 0 0 8px; border-radius: 2px; background: #9ca3af; }
.ct-trang-thai.con .ct-cham { background: var(--trang-thai-con-chu); }
.ct-trang-thai.het .ct-cham { background: var(--trang-thai-cho-chu); }
.ct-so-ban, .ct-vi-tri { margin: 0; color: var(--chu-phu); font-size: .98rem; }
.ct-vi-tri { margin-top: 4px; }
.ct-quy-dinh { display: grid; gap: 7px; margin: 0; color: #4b5563; font-size: .98rem; }
.ct-quy-dinh strong { color: var(--chu); font-weight: 600; }
.ct-dat-cho { display: flex; width: 100%; height: 46px; align-items: center; justify-content: center; margin-top: 18px; padding: 0 14px; border: 1px solid var(--nhan); border-radius: 8px; background: var(--nhan); color: #fff; font: inherit; font-size: 1rem; font-weight: 600; text-align: center; text-decoration: none; cursor: pointer; transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease, transform .18s ease; }
.ct-dat-cho:hover { border-color: var(--nhan-hover); background: var(--nhan-hover); box-shadow: 0 4px 12px rgba(30, 58, 138, .18); }
.ct-dat-cho:active { transform: scale(.98); }
.ct-dat-cho:disabled { border-color: #9ca3af; background: #9ca3af; cursor: not-allowed; }
.ct-ghi-chu { margin: 10px 0 0; color: var(--chu-phu); font-size: 12px; line-height: 1.55; }
.ct-hien-tai { padding: 12px; border: 1px solid var(--ct-vien); border-radius: 8px; background: var(--ct-xam); font-size: 13px; }
.ct-hien-tai p { margin: 0 0 6px; }
.ct-hien-tai form { margin-top: 10px; }
.ct-nut-huy { padding: 7px 10px; border: 1px solid var(--ct-vien); border-radius: 6px; background: #fff; color: var(--chu); font: inherit; font-size: 12px; cursor: pointer; }
.ct-nut-huy:hover { border-color: #bfdbfe; background: var(--nhan-mem); color: var(--nhan); }
.ct-khong-du-dieu-kien { margin: 14px 0 0; color: var(--chu-phu); font-size: 13px; }
.ct-modal-phu { position: fixed; inset: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; overflow-y: auto; padding: 20px; background: rgba(15, 23, 42, .5); }
.ct-modal-phu[hidden] { display: none; }
.ct-modal-phu:not([hidden]) { animation: ct-modal-phu-mo .18s ease both; }
.ct-modal-phu:not([hidden]) .ct-modal { animation: ct-modal-hop-thoai-mo .22s cubic-bezier(.2, .75, .25, 1) both; }
.ct-modal-phu.dang-dong { animation: ct-modal-phu-dong .18s ease both; }
.ct-modal-phu.dang-dong .ct-modal { animation: ct-modal-hop-thoai-dong .18s ease both; }
.ct-modal { width: min(100%, 520px); padding: 24px; border: 1px solid var(--ct-vien); border-radius: 14px; background: #fff; box-shadow: 0 24px 60px rgba(15, 23, 42, .2); }
.ct-modal-dau { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
.ct-modal-dau h2 { margin: 0; font-size: 20px; }
.ct-modal-dong { width: 32px; height: 32px; flex: 0 0 32px; padding: 0; border: 0; border-radius: 6px; background: transparent; color: #4b5563; font: inherit; font-size: 26px; line-height: 1; cursor: pointer; }
.ct-modal-dong:hover { background: var(--ct-xam); }
.ct-modal-huong-dan { margin: 10px 0 18px; color: #4b5563; font-size: 14px; }
.ct-tom-tat { display: flex; gap: 14px; align-items: center; padding: 12px; border: 1px solid var(--vien); border-radius: 10px; background: var(--nhan-mem); }
.ct-tom-tat-bia { width: 52px; height: 78px; flex: 0 0 52px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid var(--ct-vien); border-radius: 6px; background: linear-gradient(145deg, #eff6ff, #e2e8f0); color: var(--chu-phu); font-size: 10px; text-align: center; }
.ct-tom-tat-bia img { width: 100%; height: 100%; object-fit: cover; }
.ct-tom-tat-noi-dung { min-width: 0; }
.ct-tom-tat-ten { margin: 0 0 4px; font-size: 14px; font-weight: 700; overflow-wrap: anywhere; }
.ct-tom-tat-tac-gia, .ct-tom-tat-vi-tri { margin: 0; color: #4b5563; font-size: 12px; }
.ct-tom-tat-vi-tri { margin-top: 5px; }
.ct-modal-hanh-dong { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
.ct-modal-hanh-dong button { min-height: 40px; padding: 9px 14px; border: 1px solid var(--ct-vien); border-radius: 6px; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; }
.ct-modal-hanh-dong .ct-huy-modal { background: #fff; color: var(--chu); }
.ct-modal-hanh-dong .ct-xac-nhan { border-color: var(--nhan); background: var(--nhan); color: #fff; }
.ct-modal-hanh-dong .ct-xac-nhan:hover { border-color: var(--nhan-hover); background: var(--nhan-hover); box-shadow: 0 4px 12px rgba(30, 58, 138, .18); }
.ct-modal-hanh-dong .ct-xac-nhan:active { transform: scale(.98); }
@keyframes ct-modal-phu-mo { from { opacity: 0; } to { opacity: 1; } }
@keyframes ct-modal-hop-thoai-mo { from { opacity: 0; transform: scale(.96); } to { opacity: 1; transform: scale(1); } }
@keyframes ct-modal-phu-dong { from { opacity: 1; } to { opacity: 0; } }
@keyframes ct-modal-hop-thoai-dong { from { opacity: 1; transform: scale(1); } to { opacity: 0; transform: scale(.96); } }
@media (max-width: 992px) {
    .ct-luoi-chinh { grid-template-columns: minmax(0, 1fr); gap: 28px; }
    .ct-cot-trai { width: min(100%, 280px); margin: 0 auto; }
}
@media (max-width: 650px) {
    .ct-sach { padding: 32px 16px 48px; }
    .ct-luoi-chinh { gap: 20px; }
    .ct-cot-trai { width: min(100%, 240px); margin: 0 auto; }
    .ct-modal { padding: 20px; }
}
</style>

<section class="ct-sach">
    <nav class="ct-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= e(url('/')) ?>">Trang chủ</a>
        <span aria-hidden="true">›</span>
        <a href="<?= e(url('/#danh-muc')) ?>">Danh mục sách</a>
        <span aria-hidden="true">›</span>
        <span aria-current="page"><?= e($sach['ten_sach'] ?? '') ?></span>
    </nav>

    <div class="ct-luoi-chinh">
        <aside class="ct-cot-trai">
            <div class="ct-bia">
                <?php if ($urlAnhBia !== ''): ?>
                    <img src="<?= e($urlAnhBia) ?>" alt="Bìa sách <?= e($sach['ten_sach']) ?>">
                <?php else: ?>
                    <span>Ảnh bìa sách</span>
                <?php endif; ?>
            </div>

            <?php if ($nguoiDung === null): ?>
                <a class="ct-nut-phu" href="<?= e(url('/dang-nhap')) ?>">Thêm vào danh sách đọc</a>
            <?php elseif ($laBanDoc && $daTrongDanhSachDoc): ?>
                <button class="ct-nut-phu" type="button" disabled>Đã có trong danh sách đọc</button>
            <?php elseif ($laBanDoc): ?>
                <form action="<?= e(url('/danh-sach-doc/' . $sachId)) ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="ct-nut-phu" type="submit">Thêm vào danh sách đọc</button>
                </form>
            <?php else: ?>
                <p class="ct-ma-sach">Danh sách đọc dành cho bạn đọc.</p>
            <?php endif; ?>

            <p class="ct-ma-sach">Mã sách: <?= e($sach['ma_sach'] ?? '—') ?> • Sách in</p>
        </aside>

        <article class="ct-cot-giua">
            <div class="ct-the-loai"><?= e($sach['ten_the_loai'] ?? 'Danh mục sách') ?></div>
            <h1 class="ct-tieu-de"><?= e($sach['ten_sach'] ?? '') ?></h1>
            <p class="ct-tac-gia">Tác giả: <?= e($sach['tac_gia'] ?? 'Đang cập nhật') ?></p>

            <hr class="ct-duong-ke">

            <section class="ct-gioi-thieu">
                <h2>Giới thiệu về sách</h2>
                <p><?= nl2br(e($sach['mo_ta'] ?: 'Chưa có mô tả cho sách này.')) ?></p>
                <div class="ct-tags" aria-label="Từ khóa">
                    <?php foreach ($theTags as $theTag): ?>
                        <span><?= e($theTag) ?></span>
                    <?php endforeach; ?>
                </div>
            </section>

            <hr class="ct-duong-ke">

            <dl class="ct-thuoc-tinh">
                <?php foreach ($thongTinSach as [$nhan, $giaTri]): ?>
                    <div><dt><?= e($nhan) ?></dt><dd><?= e((string) $giaTri) ?></dd></div>
                <?php endforeach; ?>
            </dl>
        </article>

        <aside class="ct-the-muon">
            <h2>Mượn sách này</h2>
            <p class="ct-trang-thai <?= $conSan > 0 ? 'con' : 'het' ?>">
                <span class="ct-cham" aria-hidden="true"></span>
                <?= $conSan > 0 ? 'Có thể đặt trước' : 'Tạm hết bản sẵn có' ?>
            </p>
            <p class="ct-so-ban">Còn <?= $conSan ?>/<?= $tongBanSao ?> bản</p>
            <p class="ct-vi-tri">Vị trí kệ: <?= e($sach['vi_tri_ke'] ?? '—') ?></p>

            <hr class="ct-duong-ke">

            <div class="ct-quy-dinh">
                <div>Thời hạn mượn: <strong><?= (int) HAN_MUON_NGAY ?> ngày</strong></div>
                <div>Phí đặt trước: <strong>Miễn phí</strong></div>
            </div>

            <?php if ($nguoiDung === null): ?>
                <a class="ct-dat-cho" href="<?= e(url('/dang-nhap')) ?>">Đăng nhập để đặt trước</a>
            <?php elseif (!$laBanDoc): ?>
                <p class="ct-khong-du-dieu-kien">Chức năng đặt trước dành cho tài khoản bạn đọc.</p>
            <?php elseif (is_array($datChoHienTai)): ?>
                <div class="ct-hien-tai" style="margin-top:16px">
                    <p><strong>Đang giữ chỗ · <?= e(DinhDang::maDatCho((int) $datChoHienTai['id'])) ?></strong></p>
                    <p class="ct-ghi-chu">Nhận trước <?= e(DinhDang::ngayGio((string) $datChoHienTai['het_han'])) ?></p>
                    <form action="<?= e(url('/dat-cho/' . (int) $datChoHienTai['id'] . '/huy')) ?>" method="post" onsubmit="return confirm('Bạn chắc chắn muốn hủy đặt trước này?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="sach_id" value="<?= $sachId ?>">
                        <button class="ct-nut-huy" type="submit">Hủy đặt trước</button>
                    </form>
                </div>
            <?php elseif ($coTheDatCho): ?>
                <button class="ct-dat-cho" id="ct-mo-modal" type="button">Đặt trước sách</button>
            <?php else: ?>
                <p class="ct-khong-du-dieu-kien">
                    <?= e($conSan < 1 ? 'Hiện chưa có bản sẵn có để đặt trước.' : ($dieuKienDatCho['ly_do'] ?? 'Bạn chưa đủ điều kiện đặt trước.')) ?>
                </p>
            <?php endif; ?>

            <p class="ct-ghi-chu">Hệ thống giữ sách trong <?= (int) GIU_CHO_GIO ?> giờ kể từ khi duyệt. Chúng tôi sẽ thông báo khi sách đã sẵn sàng.</p>
        </aside>
    </div>
</section>

<?php if ($coTheDatCho): ?>
    <div class="ct-modal-phu" id="ct-modal" hidden>
        <section class="ct-modal" role="dialog" aria-modal="true" aria-labelledby="ct-modal-tieu-de" aria-describedby="ct-modal-mo-ta" tabindex="-1">
            <header class="ct-modal-dau">
                <h2 id="ct-modal-tieu-de">Đặt trước sách này</h2>
                <button class="ct-modal-dong" type="button" data-ct-dong-modal aria-label="Đóng">×</button>
            </header>
            <p class="ct-modal-huong-dan" id="ct-modal-mo-ta">Xác nhận thông tin đặt trước sách của bạn bên dưới. Việc đặt trước là hoàn toàn miễn phí.</p>

            <div class="ct-tom-tat">
                <div class="ct-tom-tat-bia">
                    <?php if ($urlAnhBia !== ''): ?>
                        <img src="<?= e($urlAnhBia) ?>" alt="">
                    <?php else: ?>
                        Ảnh bìa sách
                    <?php endif; ?>
                </div>
                <div class="ct-tom-tat-noi-dung">
                    <p class="ct-tom-tat-ten"><?= e($sach['ten_sach'] ?? '') ?></p>
                    <p class="ct-tom-tat-tac-gia"><?= e($sach['tac_gia'] ?? 'Đang cập nhật') ?></p>
                    <p class="ct-tom-tat-vi-tri">Vị trí kệ: <?= e($sach['vi_tri_ke'] ?? '—') ?> • Thời hạn mượn <?= (int) HAN_MUON_NGAY ?> ngày</p>
                </div>
            </div>

            <form action="<?= e(url('/dat-giu-cho')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="sach_id" value="<?= $sachId ?>">
                <footer class="ct-modal-hanh-dong">
                    <button class="ct-huy-modal" type="button" data-ct-dong-modal>Hủy</button>
                    <button class="ct-xac-nhan" type="submit">Xác nhận đặt trước</button>
                </footer>
            </form>
        </section>
    </div>
    <script>
    (function () {
        'use strict';
        var modal = document.getElementById('ct-modal');
        var nutMo = document.getElementById('ct-mo-modal');
        if (!modal || !nutMo) { return; }

        var nutDong = Array.prototype.slice.call(modal.querySelectorAll('[data-ct-dong-modal]'));
        var nutFocusCuoi = null;
        var overflowTruocDo = '';
        var dongTimer = null;
        var dangDong = false;

        function dongModal() {
            if (modal.hidden || dangDong) { return; }
            dangDong = true;
            modal.classList.add('dang-dong');
            dongTimer = window.setTimeout(function () {
                modal.hidden = true;
                modal.classList.remove('dang-dong');
                document.body.style.overflow = overflowTruocDo;
                dangDong = false;
                dongTimer = null;
                if (nutFocusCuoi) { nutFocusCuoi.focus(); }
            }, 180);
        }

        nutMo.addEventListener('click', function () {
            if (dongTimer !== null) {
                window.clearTimeout(dongTimer);
                dongTimer = null;
            }
            dangDong = false;
            modal.classList.remove('dang-dong');
            nutFocusCuoi = document.activeElement;
            overflowTruocDo = document.body.style.overflow;
            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            modal.querySelector('[data-ct-dong-modal]').focus();
        });
        nutDong.forEach(function (nut) { nut.addEventListener('click', dongModal); });
        modal.addEventListener('click', function (suKien) {
            if (suKien.target === modal) { dongModal(); }
        });
        document.addEventListener('keydown', function (suKien) {
            if (modal.hidden || dangDong) { return; }
            if (suKien.key === 'Escape') {
                suKien.preventDefault();
                dongModal();
            } else if (suKien.key === 'Tab') {
                var cacNut = Array.prototype.slice.call(modal.querySelectorAll('button:not([disabled])'));
                var dau = cacNut[0];
                var cuoi = cacNut[cacNut.length - 1];
                if (suKien.shiftKey && document.activeElement === dau) {
                    suKien.preventDefault();
                    cuoi.focus();
                } else if (!suKien.shiftKey && document.activeElement === cuoi) {
                    suKien.preventDefault();
                    dau.focus();
                }
            }
        });
    })();
    </script>
<?php endif; ?>
