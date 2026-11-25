<?php
$datCho = $datCho ?? [];
$sachMuon = $sachMuon ?? [];
$tuKhoa = trim((string) ($_GET['tu_khoa'] ?? ''));
$bayGio = new DateTimeImmutable('now');
$hanTra = $bayGio->modify('+' . HAN_MUON_NGAY . ' days');
$iconKhoa = '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 7.5-2"/></svg>';
?>
<style>
/* Dùng lại .khoi / bảng / .nut như trang Tổng quan; ở đây chỉ thêm phần riêng của tab và form. */
.ct-tab-bar { display: flex; gap: 4px; border-bottom: 1px solid var(--vien); margin-bottom: -1px; flex-wrap: wrap; }
.ct-tab { appearance: none; border: 1px solid transparent; border-bottom: 0; background: none; font: inherit; color: var(--chu-phu); padding: 12px 24px; cursor: pointer; border-radius: var(--ban-kinh) var(--ban-kinh) 0 0; white-space: nowrap; }
.ct-tab:hover { color: var(--chu); }
.ct-tab[aria-selected="true"] { background: var(--nen-xam); border-color: var(--vien); color: var(--chu); font-weight: 600; margin-bottom: -1px; }
.ct-tab:focus-visible, .ct input:focus-visible, .ct select:focus-visible, .ct .nut:focus-visible { outline: 2px solid var(--nhan); outline-offset: 2px; }
.ct .khoi { border-radius: 0 0 var(--ban-kinh) var(--ban-kinh); }
.ct .khoi[hidden] { display: none; }
.ct .nut:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }
.ct .nut-nho { padding: 7px 16px; }
.ct-ghi-chu { margin: 0 0 16px; color: var(--chu-phu); }
.ct label { display: block; margin: 16px 0 6px; font-size: 14px; font-weight: 600; }
.ct input[readonly] { background: var(--nen-xam-dam); color: var(--chu-phu); cursor: not-allowed; padding-right: 36px; }

.ct-thanh-loc { display: grid; grid-template-columns: 1fr 200px; gap: 12px; margin-bottom: 16px; }
.ct-tim { position: relative; }
.ct-tim svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; stroke: var(--chu-phu); fill: none; stroke-width: 1.8; pointer-events: none; }
.ct-tim input { padding-left: 38px; }

.cuon-ngang { overflow-x: auto; }
.ct table { min-width: 760px; }
.ct td form { margin: 0; }
.ct td small { display: block; color: var(--chu-phu); font-size: 13px; }
.ct-rong { text-align: center; color: var(--chu-phu); padding: 28px 14px !important; }
.ct [data-het-han] { font-variant-numeric: tabular-nums; }
.ct [data-het-han].het-han { color: var(--canh-bao); }
.ct .chan-bang { margin-top: 14px; }

.ct-hang-ma { display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: start; }
.ct-the-sv { display: grid; grid-template-columns: 1fr 200px 200px; gap: 20px; margin: 16px 0 4px; padding: 18px 22px; background: var(--nen); border: 1px solid var(--vien); border-radius: var(--ban-kinh); color: var(--chu-phu); }
.ct-the-sv strong { display: block; font-size: 16px; color: var(--chu); }
.ct-the-sv .so { display: block; margin: 2px 0; font-size: 24px; font-weight: 700; color: var(--chu); }
.ct-the-sv p { margin: 2px 0 0; font-size: 14px; }
.ct-the-sv .loi { color: var(--canh-bao); }
.ct-hai-cot { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 20px; }
.ct-khoa { position: relative; }
.ct-khoa svg { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; stroke: var(--chu-phu); fill: none; stroke-width: 1.8; }
.ct-gia-tri-phu { margin: 6px 0 0; font-size: 13px; color: var(--chu-phu); }
.ct-hang-cuoi { display: flex; justify-content: space-between; align-items: flex-end; gap: 20px; margin-top: 20px; }
.ct-hang-cuoi p { margin: 0; font-size: 14px; color: var(--chu-phu); }

@media (max-width: 720px) {
    .ct-thanh-loc, .ct-hang-ma, .ct-the-sv, .ct-hai-cot { grid-template-columns: 1fr; }
    .ct-hang-cuoi { flex-direction: column; align-items: stretch; }
}
</style>

<div class="ct" id="ct">
    <div class="tieu-de-trang">
        <div>
            <h1>Đặt chỗ &amp; cho mượn tại quầy</h1>
            <p class="phu">Giao sách đã đặt trước hoặc tạo phiếu mượn trực tiếp bằng mã sinh viên.</p>
        </div>
    </div>

    <div class="ct-tab-bar" role="tablist" aria-label="Cho mượn tại quầy">
        <button type="button" class="ct-tab" role="tab" id="tab-cho-muon" data-tab="cho-muon" aria-controls="cho-muon" aria-selected="true">Cho mượn trực tuyến · <?= count($datCho) ?></button>
        <button type="button" class="ct-tab" role="tab" id="tab-cho-muon-truc-tiep" data-tab="cho-muon-truc-tiep" aria-controls="cho-muon-truc-tiep" aria-selected="false" tabindex="-1">Cho mượn trực tiếp tại quầy</button>
    </div>

    <!-- (1) Cho mượn trực tuyến: giao sách đã đặt chỗ trực tuyến -->
    <section class="khoi" id="cho-muon" role="tabpanel" aria-labelledby="tab-cho-muon">
        <p class="ct-ghi-chu">Thời điểm xem: <?= e($bayGio->format('d/m/Y, H:i')) ?> · Tự động hủy và giải phóng sách sau <?= GIU_CHO_GIO ?> giờ chưa nhận.</p>

        <form method="get" action="<?= e(url('/quan-tri/dat-cho')) ?>" class="ct-thanh-loc" role="search">
            <div class="ct-tim">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" name="tu_khoa" id="ct-tim-dat-cho" value="<?= e($tuKhoa) ?>" placeholder="Tìm mã đặt chỗ, sinh viên hoặc tên sách..." aria-label="Tìm đặt chỗ" autocomplete="off">
            </div>
            <select id="ct-loc-dat-cho" aria-label="Lọc đặt chỗ">
                <option value="cho">Chờ nhận sách</option>
                <option value="sap-het">Sắp hết hạn (&lt; 6 giờ)</option>
            </select>
        </form>

        <div class="cuon-ngang">
            <table class="bang-du-lieu">
                <thead>
                    <tr>
                        <th scope="col">Mã đặt chỗ</th>
                        <th scope="col">Sinh viên</th>
                        <th scope="col">Tên sách</th>
                        <th scope="col">Hết hạn nhận</th>
                        <th scope="col">Còn lại</th>
                        <th scope="col">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="ct-tbody">
                <?php foreach ($datCho as $dc): ?>
                    <?php $hetHan = new DateTimeImmutable((string) $dc['het_han']); ?>
                    <tr data-tim="<?= e(mb_strtolower($dc['ma_dat_cho'] . ' ' . $dc['sinh_vien'] . ' ' . $dc['ma_sv'] . ' ' . $dc['ten_sach'])) ?>"
                        data-moc="<?= e((string) $hetHan->getTimestamp()) ?>">
                        <td><?= e($dc['ma_dat_cho']) ?></td>
                        <td><?= e($dc['sinh_vien']) ?><small><?= e($dc['ma_sv']) ?></small></td>
                        <td><?= e($dc['ten_sach']) ?></td>
                        <td><?= e($hetHan->format('d/m/Y')) ?><small><?= e($hetHan->format('H:i')) ?></small></td>
                        <td><span data-het-han="<?= e($hetHan->format('c')) ?>"><?= e(\Loi\DinhDang::conLai((string) $dc['het_han'])) ?></span></td>
                        <td>
                            <form method="post" action="<?= e(url('/quan-tri/dat-cho/' . (int) $dc['id'] . '/giao')) ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="nut nut-nho" aria-label="Giao sách cho đơn <?= e($dc['ma_dat_cho']) ?>">Giao sách</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                    <tr id="ct-khong-co" <?= $datCho === [] ? '' : 'hidden' ?>>
                        <td colspan="6" class="ct-rong"><?= $tuKhoa !== '' ? 'Không có đặt chỗ nào khớp từ khóa.' : 'Hiện chưa có đặt chỗ nào đang chờ nhận.' ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="chan-bang">
            <span class="phu">Khi giao sách: đối chiếu thẻ, kiểm tra phí còn nợ và lập phiếu mượn <?= HAN_MUON_NGAY ?> ngày từ ngày nhận.</span>
        </div>
    </section>

    <!-- (3)–(7) Cho mượn trực tiếp -->
    <section class="khoi" id="cho-muon-truc-tiep" role="tabpanel" aria-labelledby="tab-cho-muon-truc-tiep">
        <p class="ct-ghi-chu">Ví dụ dữ liệu đã tra cứu tự động · Sinh viên đủ điều kiện mượn sách.</p>

        <form method="post" action="<?= e(url('/quan-tri/muon/xac-nhan')) ?>" id="ct-form-muon" novalidate>
            <?= csrf_field() ?>

            <label for="ct-ma-sv">Mã sinh viên *</label>
            <div class="ct-hang-ma">
                <input type="text" id="ct-ma-sv" name="ma_sv" placeholder="Ví dụ: SV2024025" maxlength="20" autocomplete="off" required aria-describedby="ct-ma-sv-goi-y">
                <button type="button" class="nut nut-vien" id="ct-tra-cuu">Tra cứu sinh viên</button>
            </div>
            <p class="ct-gia-tri-phu" id="ct-ma-sv-goi-y">Tự động lấy thông tin hồ sơ, phiếu đang mượn và phí chưa trả.</p>

            <div class="ct-the-sv" id="ct-the-sv" aria-live="polite">
                <div>
                    <strong id="ct-sv-ten">—</strong>
                    <p id="ct-sv-phu">Nhập mã sinh viên rồi bấm “Tra cứu sinh viên”.</p>
                    <p id="ct-sv-tk"></p>
                </div>
                <div>
                    Phiếu đang mượn
                    <span class="so" id="ct-sv-muon">—</span>
                    <p id="ct-sv-qua-han"></p>
                </div>
                <div>
                    Phí chưa thanh toán
                    <span class="so" id="ct-sv-phi">—</span>
                    <p id="ct-sv-dieu-kien"></p>
                </div>
            </div>

            <label for="ct-sach">Tìm &amp; chọn sách *</label>
            <select id="ct-sach" name="sach_id" required aria-describedby="ct-sach-goi-y">
                <option value="">— Chọn sách —</option>
                <?php foreach ($sachMuon as $s): ?>
                    <option value="<?= (int) $s['id'] ?>"
                            data-goi-y="<?= e('Bản sao ' . ($s['ban_sao_goi_y'] ?? '—') . ' · Còn ' . (int) $s['san_co'] . ' / ' . (int) $s['tong'] . ' bản · Kệ ' . ($s['ke'] ?? '—')) ?>"
                            <?= (int) $s['san_co'] < 1 ? 'disabled' : '' ?>>
                        <?= e($s['ma'] . ' · ' . $s['ten'] . (($s['tac_gia'] ?? '') !== '' ? ' — ' . $s['tac_gia'] : '')) ?><?= (int) $s['san_co'] < 1 ? ' (hết bản)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="ct-gia-tri-phu" id="ct-sach-goi-y">Tìm theo mã hoặc tên sách. Hệ thống tự chọn một bản sao còn sẵn khi xác nhận.</p>

            <div class="ct-hai-cot">
                <div>
                    <label for="ct-ngay-muon">Ngày mượn</label>
                    <div class="ct-khoa"><input type="text" id="ct-ngay-muon" value="<?= e($bayGio->format('d/m/Y')) ?>" readonly><?= $iconKhoa ?></div>
                    <p class="ct-gia-tri-phu">Tự động: ngày hiện tại.</p>
                </div>
                <div>
                    <label for="ct-han-tra">Hạn trả dự kiến</label>
                    <div class="ct-khoa"><input type="text" id="ct-han-tra" value="<?= e($hanTra->format('d/m/Y')) ?>" readonly><?= $iconKhoa ?></div>
                    <p class="ct-gia-tri-phu">Tự động: ngày mượn +<?= HAN_MUON_NGAY ?> ngày.</p>
                </div>
            </div>

            <div class="ct-hang-cuoi">
                <p>Mượn <?= HAN_MUON_NGAY ?> ngày · Có thể gia hạn +<?= GIA_HAN_NGAY ?> ngày khi đủ điều kiện.<br>Kiểm tra tình trạng bản sao và thẻ sinh viên trước khi xác nhận.</p>
                <button type="submit" class="nut" id="ct-xac-nhan" disabled>Xác nhận cho mượn</button>
            </div>
        </form>
    </section>
</div>

<script>
(function () {
    'use strict';
    var $ = function (id) { return document.getElementById(id); };

    /* Lọc bảng đặt chỗ ngay trên trình duyệt (server vẫn hỗ trợ ?tu_khoa=). */
    var oTim = $('ct-tim-dat-cho'), oLoc = $('ct-loc-dat-cho'), dongKhongCo = $('ct-khong-co');
    var dong = Array.prototype.slice.call(document.querySelectorAll('#ct-tbody tr[data-tim]'));
    function loc() {
        var tuKhoa = oTim.value.trim().toLowerCase(), sapHet = oLoc.value === 'sap-het', thay = 0;
        var bayGio = Date.now() / 1000;
        dong.forEach(function (tr) {
            var khop = tr.getAttribute('data-tim').indexOf(tuKhoa) !== -1;
            if (sapHet) { khop = khop && (Number(tr.getAttribute('data-moc')) - bayGio) < 6 * 3600; }
            tr.hidden = !khop;
            if (khop) { thay++; }
        });
        dongKhongCo.hidden = thay > 0;
        if (thay === 0 && dong.length > 0) { dongKhongCo.firstElementChild.textContent = 'Không có đặt chỗ nào khớp bộ lọc.'; }
    }
    oTim.addEventListener('input', loc);
    oLoc.addEventListener('change', loc);
    oTim.form.addEventListener('submit', function (e) { e.preventDefault(); loc(); });

    /* Tra cứu sinh viên (GET JSON) -> điền thẻ điều kiện. */
    var nutTraCuu = $('ct-tra-cuu'), oMa = $('ct-ma-sv'), oSach = $('ct-sach'), nutXacNhan = $('ct-xac-nhan');
    var duDieuKien = false, maDaTra = '';
    function datChu(id, chu, loi) { var el = $(id); el.textContent = chu; el.classList.toggle('loi', !!loi); }
    function capNhatNut() { nutXacNhan.disabled = !(duDieuKien && oMa.value.trim() === maDaTra && oSach.value !== ''); }
    function xoaThe(thongBao) {
        duDieuKien = false; maDaTra = '';
        datChu('ct-sv-ten', '—'); datChu('ct-sv-phu', thongBao || ''); datChu('ct-sv-tk', '');
        datChu('ct-sv-muon', '—'); datChu('ct-sv-qua-han', ''); datChu('ct-sv-phi', '—'); datChu('ct-sv-dieu-kien', '');
        capNhatNut();
    }
    oMa.addEventListener('input', function () { if (oMa.value.trim() !== maDaTra) { xoaThe('Nhập mã sinh viên rồi bấm “Tra cứu sinh viên”.'); } });
    oMa.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); nutTraCuu.click(); } });
    oSach.addEventListener('change', function () {
        var op = oSach.options[oSach.selectedIndex];
        $('ct-sach-goi-y').textContent = (op && op.getAttribute('data-goi-y')) || 'Tìm theo mã hoặc tên sách. Hệ thống tự chọn một bản sao còn sẵn khi xác nhận.';
        capNhatNut();
    });

    nutTraCuu.addEventListener('click', function () {
        var ma = oMa.value.trim();
        if (ma === '') { oMa.focus(); xoaThe('Vui lòng nhập mã sinh viên.'); return; }
        nutTraCuu.disabled = true; datChu('ct-sv-phu', 'Đang tra cứu...');
        fetch(<?= json_encode(url('/quan-tri/muon/tra-cuu-sinh-vien'), JSON_UNESCAPED_SLASHES) ?> + '?ma=' + encodeURIComponent(ma), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { if (!r.ok) { throw new Error(r.status === 404 ? 'Không tìm thấy sinh viên.' : 'Không tra cứu được (mã lỗi ' + r.status + ').'); } return r.json(); })
            .then(function (d) {
                maDaTra = ma;
                duDieuKien = d.du_dieu_kien === true;
                datChu('ct-sv-ten', d.ho_ten || '—');
                datChu('ct-sv-phu', (d.ma || ma) + (d.khoa ? ' · ' + d.khoa : ''));
                datChu('ct-sv-tk', d.tai_khoan_hoat_dong ? 'Tài khoản đang hoạt động' : 'Tài khoản bị khóa', !d.tai_khoan_hoat_dong);
                datChu('ct-sv-muon', d.dang_muon + ' / ' + d.toi_da + ' cuốn', d.dang_muon >= d.toi_da);
                datChu('ct-sv-qua-han', d.co_qua_han ? 'Có phiếu quá hạn' : 'Không có phiếu quá hạn', d.co_qua_han);
                datChu('ct-sv-phi', Number(d.phi_chua_thanh_toan || 0).toLocaleString('vi-VN') + ' đ', d.phi_chua_thanh_toan > 0);
                datChu('ct-sv-dieu-kien', duDieuKien ? 'Đủ điều kiện mượn' : 'Chưa đủ điều kiện mượn', !duDieuKien);
                capNhatNut();
            })
            .catch(function (err) { xoaThe(err.message || 'Không tra cứu được.'); datChu('ct-sv-phu', err.message || 'Không tra cứu được.', true); })
            .then(function () { nutTraCuu.disabled = false; });
    });

    /* Server luôn kiểm tra lại điều kiện; đây chỉ chặn bấm nhầm và gửi đúp. */
    $('ct-form-muon').addEventListener('submit', function (e) {
        if (nutXacNhan.disabled) { e.preventDefault(); return; }
        nutXacNhan.disabled = true;
    });
    capNhatNut();

    /* Chuyển tab (giống trang hồ sơ cá nhân). Không có JS thì cả 2 phần vẫn hiện liên tiếp. */
    var goc = $('ct'), tabs = Array.prototype.slice.call(goc.querySelectorAll('.ct-tab')), khung = {};
    tabs.forEach(function (t) { khung[t.getAttribute('data-tab')] = $(t.getAttribute('data-tab')); });
    function moTab(ten, ghiHash) {
        if (!khung[ten]) { ten = 'cho-muon'; }
        tabs.forEach(function (t) {
            var dung = t.getAttribute('data-tab') === ten;
            t.setAttribute('aria-selected', dung ? 'true' : 'false');
            t.tabIndex = dung ? 0 : -1;
            khung[t.getAttribute('data-tab')].hidden = !dung;
        });
        if (ghiHash) { history.replaceState(null, '', '#' + ten); }
    }
    tabs.forEach(function (t, i) {
        t.addEventListener('click', function () { moTab(t.getAttribute('data-tab'), true); });
        t.addEventListener('keydown', function (ev) {
            var j = ev.key === 'ArrowRight' ? i + 1 : (ev.key === 'ArrowLeft' ? i - 1 : null);
            if (j === null) { return; }
            j = (j + tabs.length) % tabs.length;
            tabs[j].focus();
            moTab(tabs[j].getAttribute('data-tab'), true);
        });
    });
    window.addEventListener('hashchange', function () { moTab(location.hash.slice(1), false); });
    moTab(location.hash.slice(1), false);
})();
</script>
