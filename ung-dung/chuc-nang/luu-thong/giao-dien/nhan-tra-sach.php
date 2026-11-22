<?php
/**
 * SR-09 — Trả sách & xử lý phí phạt (Check-in).
 * Biến: $phieuMuon (PhieuMuon::layDangMuon). Popup + JS: public/tai-nguyen/js/tinh-phi-phat.js
 * Số tiền hiển thị trên popup chỉ để xem trước — server tự tính lại bằng TinhPhiPhat khi POST.
 */
use ChucNang\LuuThong\TinhPhiPhat;

$phieuMuon = $phieuMuon ?? [];
$tuKhoa = trim((string) ($_GET['tu_khoa'] ?? ''));
$trangThai = (string) ($_GET['trang_thai'] ?? 'tat-ca');
if (!in_array($trangThai, ['tat-ca', 'dang-muon', 'qua-han'], true)) { $trangThai = 'tat-ca'; }
$homNay = date('Y-m-d');
?>
<style>
.tr { max-width: 1000px; margin: 0 auto; }
.tr h1 { margin: 4px 0 6px; font-size: 28px; }
.tr .nhan { font-size: 11px; }
.tr .mo-ta { margin: 0 0 20px; font-size: 13px; color: var(--chu-phu); }
.tr label { font-size: 12px; font-weight: 500; margin: 14px 0 6px; }
.tr input, .tr select, .tr textarea { font-size: 13px; padding: 9px 12px; background: var(--nen); }
.tr input:focus-visible, .tr select:focus-visible, .tr textarea:focus-visible, .tr-nut:focus-visible, .tr-dong:focus-visible, .tr-chuyen a:focus-visible { outline: 2px solid var(--nhan); outline-offset: 2px; }

.tr-chuyen { display: flex; gap: 10px; margin-bottom: 20px; }
.tr-chuyen a { padding: 9px 18px; border: 1px solid var(--vien); border-radius: var(--ban-kinh); background: var(--nen); font-size: 13px; font-weight: 600; text-decoration: none; }
.tr-chuyen a:hover { background: var(--nen-xam); }
.tr-chuyen a[aria-current="page"] { background: var(--nhan); border-color: var(--nhan); color: #fff; }

.tr .khoi { padding: 22px 24px; }
.tr .khoi h2 { margin: 0 0 4px; font-size: 17px; }
.tr-ghi-chu { margin: 0 0 14px; font-size: 12px; color: var(--chu-phu); }
.tr-loc { display: grid; grid-template-columns: 1fr 170px auto; gap: 10px; }
.tr-tim { position: relative; }
.tr-tim svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; stroke: #444; fill: none; stroke-width: 1.8; pointer-events: none; }
.tr-tim input { padding-left: 36px; }

.tr-bang { margin-top: 20px; background: var(--nen); border: 1px solid var(--vien); border-radius: var(--ban-kinh); overflow-x: auto; }
.tr-bang table { min-width: 760px; font-size: 13px; }
.tr-bang th { font-size: 12px; font-weight: 600; }
.tr-bang tbody tr:last-child td { border-bottom: 0; }
.tr-bang small { display: block; color: var(--chu-phu); font-size: 12px; }
.tr-bang .qua-han { color: var(--canh-bao); font-weight: 600; }
.tr-rong { text-align: center; color: var(--chu-phu); padding: 28px 14px !important; }
.tr-chan { margin: 14px 0 0; font-size: 12px; color: var(--chu-phu); }

.tr-nut { display: inline-block; padding: 9px 18px; border: 1px solid var(--nhan); border-radius: var(--ban-kinh); background: var(--nhan); color: #fff; font: inherit; font-size: 12px; font-weight: 600; cursor: pointer; white-space: nowrap; }
.tr-nut:hover { opacity: .88; }
.tr-nut.nho { padding: 6px 12px; }
.tr-nut.vien { background: var(--nen); color: var(--chu); border-color: var(--vien); font-weight: 500; }
.tr-nut:disabled { opacity: .45; cursor: not-allowed; }

/* Popup: <dialog>.showModal() => nằm GIỮA màn hình, nền phía sau tối đi và không cuộn được. */
.tr-popup { width: min(560px, calc(100vw - 32px)); max-height: calc(100vh - 32px); margin: auto; padding: 0; border: 1px solid var(--vien); border-radius: 10px; background: var(--nen); color: var(--chu); box-shadow: 0 24px 60px rgba(0,0,0,.28); overflow: auto; }
.tr-popup::backdrop { background: rgba(0,0,0,.55); }
.tr-popup > form { padding: 24px 28px 22px; margin: 0; }
.tr-popup h2 { margin: 0 0 6px; font-size: 20px; }
.tr-popup-dau { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
.tr-dong { appearance: none; border: 0; background: none; padding: 4px; cursor: pointer; color: var(--chu); line-height: 0; border-radius: 4px; }
.tr-dong:hover { background: var(--nen-xam); }
.tr-dong svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 1.8; }
.tr-phu { margin: 0 0 14px; font-size: 12px; color: var(--chu-phu); }
.tr-sach { padding: 12px 16px; margin-bottom: 4px; background: var(--nen-xam); border: 1px solid var(--vien); border-radius: var(--ban-kinh); }
.tr-sach strong { display: block; font-size: 14px; }
.tr-sach span { font-size: 12px; color: var(--chu-phu); }
.tr-hai-cot { display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px; }
.tr-khoa { position: relative; }
.tr-khoa svg { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; stroke: #444; fill: none; stroke-width: 1.8; }
.tr input[readonly] { background: var(--nen-xam-dam); color: #555; cursor: not-allowed; padding-right: 36px; }
.tr-goi-y { margin: 6px 0 0; font-size: 11px; color: var(--chu-phu); }
.tr-phi { margin-top: 18px; padding: 14px 18px; background: var(--nen-xam); border-radius: var(--ban-kinh); font-size: 12px; color: var(--chu-phu); }
.tr-phi > div { display: flex; justify-content: space-between; gap: 16px; padding: 3px 0; }
.tr-phi b, .tr-phi .dam { color: var(--chu); }
.tr-phi .lon { font-size: 15px; font-weight: 700; color: var(--chu); }
.tr-tong { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; margin-top: 16px; font-size: 12px; color: var(--chu-phu); }
.tr-tong p { margin: 0; }
.tr-tong .so { text-align: right; }
.tr-tong .so strong { display: block; font-size: 24px; color: var(--chu); }
.tr-hanh-dong { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }
.tr-hanh-dong .tr-nut { padding: 10px 18px; font-size: 13px; }
.tr-canh-bao { margin: 0 0 14px; padding: 10px 14px; border: 1px solid var(--canh-bao); border-radius: var(--ban-kinh); background: #fff5f4; color: var(--canh-bao); font-size: 12px; }
.tr-popup textarea { resize: vertical; min-height: 72px; }
.tr-khoa-cuon { overflow: hidden; }

@media (max-width: 720px) {
    .tr-loc, .tr-hai-cot { grid-template-columns: 1fr; }
    .tr-popup > form { padding: 18px 16px; }
    .tr-tong { flex-direction: column; align-items: stretch; }
    .tr-tong .so { text-align: left; }
}
</style>

<div class="tr" id="tra-sach"
     data-don-gia="<?= (int) PHI_PHAT_MOI_NGAY ?>"
     data-ty-le-hong="<?= e((string) TY_LE_BOI_HOAN_HONG) ?>"
     data-ty-le-mat="<?= e((string) TY_LE_BOI_HOAN_MAT) ?>"
     data-hom-nay="<?= e($homNay) ?>">
    <p class="nhan">SR-09 / Book Return &amp; Fine Processing — Check-in</p>
    <h1>Trả sách &amp; xử lý phí phạt</h1>
    <p class="mo-ta">Tra cứu phiếu đang mượn, ghi nhận tình trạng sách và thu phí quá hạn tại quầy.</p>

    <nav class="tr-chuyen" aria-label="Chọn nghiệp vụ tại quầy">
        <a href="<?= e(url('/quan-tri/dat-cho')) ?>">Cho mượn tại quầy</a>
        <a href="<?= e(url('/quan-tri/muon-tra')) ?>" aria-current="page">Trả sách / Check-in</a>
    </nav>

    <!-- (1) Tra cứu + bảng phiếu đang mượn -->
    <section class="khoi" aria-labelledby="tr-tieu-de">
        <h2 id="tr-tieu-de">Tra cứu phiếu đang mượn</h2>
        <p class="tr-ghi-chu">Nhập mã phiếu, mã sinh viên, tên người mượn hoặc mã bản sao.</p>
        <form method="get" action="<?= e(url('/quan-tri/muon-tra')) ?>" class="tr-loc" role="search">
            <div class="tr-tim">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" name="tu_khoa" value="<?= e($tuKhoa) ?>" placeholder="Tất cả phiếu đang mượn" aria-label="Tìm phiếu đang mượn" autocomplete="off">
            </div>
            <select name="trang_thai" aria-label="Lọc theo trạng thái">
                <option value="tat-ca" <?= $trangThai === 'tat-ca' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                <option value="dang-muon" <?= $trangThai === 'dang-muon' ? 'selected' : '' ?>>Còn hạn</option>
                <option value="qua-han" <?= $trangThai === 'qua-han' ? 'selected' : '' ?>>Quá hạn</option>
            </select>
            <button type="submit" class="tr-nut" style="padding:10px 22px">Tìm kiếm</button>
        </form>
    </section>

    <div class="tr-bang">
        <table>
            <thead>
                <tr>
                    <th scope="col">Mã phiếu</th>
                    <th scope="col">Sinh viên</th>
                    <th scope="col">Tên sách</th>
                    <th scope="col">Ngày mượn</th>
                    <th scope="col">Hạn trả</th>
                    <th scope="col">Quá hạn</th>
                    <th scope="col">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($phieuMuon as $p): ?>
                <?php $soNgayTre = TinhPhiPhat::soNgayTre((string) $p['han_tra'], $homNay); ?>
                <tr>
                    <td><?= e($p['ma_phieu']) ?></td>
                    <td><?= e($p['sinh_vien']) ?><small><?= e($p['ma_sv']) ?></small></td>
                    <td><?= e($p['ten_sach']) ?></td>
                    <td><?= e(ngay($p['ngay_muon'])) ?></td>
                    <td><?= e(ngay($p['han_tra'])) ?></td>
                    <td class="<?= $soNgayTre > 0 ? 'qua-han' : '' ?>"><?= $soNgayTre ?> ngày</td>
                    <td>
                        <button type="button" class="tr-nut nho <?= $soNgayTre > 0 ? '' : 'vien' ?>" data-mo-tra
                                data-hanh-dong="<?= e(url('/quan-tri/muon-tra/' . (int) $p['id'] . '/tra')) ?>"
                                data-ma-phieu="<?= e($p['ma_phieu']) ?>"
                                data-ma-ban-sao="<?= e($p['ma_ban_sao']) ?>"
                                data-sinh-vien="<?= e($p['sinh_vien']) ?>"
                                data-ma-sv="<?= e($p['ma_sv']) ?>"
                                data-ten-sach="<?= e($p['ten_sach']) ?>"
                                data-tac-gia="<?= e($p['tac_gia']) ?>"
                                data-gia-bia="<?= (int) $p['gia_bia'] ?>"
                                data-ngay-muon="<?= e(substr((string) $p['ngay_muon'], 0, 10)) ?>"
                                data-han-tra="<?= e(substr((string) $p['han_tra'], 0, 10)) ?>"
                                aria-label="Trả sách cho phiếu <?= e($p['ma_phieu']) ?>">Trả sách</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($phieuMuon === []): ?>
                <tr><td colspan="7" class="tr-rong">Không có phiếu đang mượn nào khớp.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="tr-chan">Hiển thị <?= count($phieuMuon) ?> phiếu · Ngày xử lý: <?= e(ngay($homNay)) ?>.<br>Chọn “Trả sách” để đối chiếu ngày trả, kiểm tra tình trạng và tính phí tự động.</p>

<!-- (2)–(7) Popup "Trả sách & xử lý phí phạt" -->
<dialog class="tr-popup" id="tra-popup" aria-labelledby="tra-popup-tieu-de">
    <form method="post" id="tra-form" action="">
        <?= csrf_field() ?>
        <input type="hidden" name="ghi_chu" id="tra-ghi-chu-an" value="">
        <div class="tr-popup-dau">
            <h2 id="tra-popup-tieu-de">Trả sách &amp; xử lý phí phạt</h2>
            <button type="button" class="tr-dong" data-dong="tra-popup" aria-label="Đóng">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg>
            </button>
        </div>
        <p class="tr-phu" id="tra-thong-tin-phieu">—</p>

        <div class="tr-sach">
            <strong id="tra-ten-sach">—</strong>
            <span id="tra-phu-sach">—</span>
        </div>

        <div class="tr-hai-cot">
            <div>
                <label for="tra-han-tra">Hạn trả theo phiếu</label>
                <div class="tr-khoa">
                    <input type="text" id="tra-han-tra" readonly>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 7.5-2"/></svg>
                </div>
            </div>
            <div>
                <label for="tra-ngay-tra">Ngày trả thực tế *</label>
                <input type="date" id="tra-ngay-tra" name="ngay_tra" required aria-describedby="tra-ngay-tra-goi-y">
                <p class="tr-goi-y" id="tra-ngay-tra-goi-y">Ngày ghi nhận trả sách tại quầy (không chọn ngày tương lai).</p>
            </div>
        </div>

        <div class="tr-phi" aria-live="polite">
            <div><span>Số ngày quá hạn</span><b id="tra-so-ngay-dien-giai">—</b></div>
            <div><span>Mức phạt / ngày / cuốn</span><span><?= e(tien(PHI_PHAT_MOI_NGAY)) ?></span></div>
            <div><span class="dam"><b>Phí quá hạn tự động</b></span><span class="lon" id="tra-phi-tre-dien-giai">—</span></div>
        </div>

        <label for="tra-tinh-trang">Tình trạng sách khi trả *</label>
        <select id="tra-tinh-trang" name="tinh_trang" required aria-describedby="tra-tinh-trang-goi-y">
            <option value="binh_thuong">Bình thường (Normal)</option>
            <option value="hong">Hư hỏng (Damaged)</option>
            <option value="mat">Mất (Lost)</option>
        </select>
        <p class="tr-goi-y" id="tra-tinh-trang-goi-y">Lựa chọn: Bình thường / Hư hỏng (Damaged) / Mất (Lost).</p>

        <div class="tr-tong">
            <p>Phí hư hỏng / mất: <b id="tra-phi-boi-hoan">0 đ</b><br>Sau khi hoàn tất, bản sao được chuyển về sẵn có.</p>
            <div class="so">TỔNG CẦN THU<strong id="tra-tong">0 đ</strong></div>
        </div>

        <div class="tr-hanh-dong">
            <button type="button" class="tr-nut vien" data-dong="tra-popup">Hủy</button>
            <button type="submit" class="tr-nut" id="tra-hoan-tat">Hoàn tất trả sách &amp; thu phí</button>
        </div>
    </form>
</dialog>

<!-- Popup bổ sung khi chọn Hư hỏng / Mất: xác nhận bồi hoàn (cũng nằm giữa màn hình, nền tối) -->
<dialog class="tr-popup" id="tra-boi-hoan-popup" aria-labelledby="tra-bh-tieu-de" style="width:min(480px, calc(100vw - 32px))">
    <form method="dialog" id="tra-bh-form">
        <div class="tr-popup-dau">
            <h2 id="tra-bh-tieu-de">Xác nhận sách hư hỏng</h2>
            <button type="button" class="tr-dong" data-huy-boi-hoan aria-label="Đóng">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg>
            </button>
        </div>
        <p class="tr-phu" id="tra-bh-phieu">—</p>
        <p class="tr-canh-bao" id="tra-bh-canh-bao">Sách sẽ không được đưa về trạng thái sẵn có.</p>

        <div class="tr-phi">
            <div><span>Giá bìa</span><span id="tra-bh-gia-bia">—</span></div>
            <div><span>Tỷ lệ bồi hoàn</span><span id="tra-bh-ty-le">—</span></div>
            <div><span class="dam"><b>Phí bồi hoàn</b></span><span class="lon" id="tra-bh-phi">—</span></div>
            <div><span>Phí quá hạn</span><span id="tra-bh-phi-tre">—</span></div>
            <div><span class="dam"><b>Tổng cần thu</b></span><span class="lon" id="tra-bh-tong">—</span></div>
        </div>

        <label for="tra-bh-ghi-chu">Ghi chú tình trạng (không bắt buộc)</label>
        <textarea id="tra-bh-ghi-chu" maxlength="300" placeholder="Ví dụ: rách bìa, mất trang 45–60..."></textarea>

        <div class="tr-hanh-dong">
            <button type="button" class="tr-nut vien" data-huy-boi-hoan>Quay lại</button>
            <button type="button" class="tr-nut" id="tra-bh-xac-nhan">Xác nhận bồi hoàn</button>
        </div>
    </form>
</dialog>
</div>
