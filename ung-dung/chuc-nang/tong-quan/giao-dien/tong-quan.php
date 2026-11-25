<?php
/** SR-06 — Tổng quan thư viện. Biến: $thang, $cacThang, $thongKe, $quaHan, $tongQuaHan, $datCho, $muonTra */
use Loi\DinhDang;

$thu = ['Chủ nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
?>
<p class="nhan">SR-06 / Admin &amp; Librarian Dashboard</p>
<div class="tieu-de-trang">
    <div>
        <h1>Tổng quan thư viện</h1>
        <p class="phu">Theo dõi kho sách, tình hình mượn trả và các phiếu cần xử lý hôm nay.</p>
    </div>
    <a class="nut" href="<?= e(url('/quan-tri/xuat-bao-cao?thang=' . $thang)) ?>">Xuất báo cáo</a>
</div>

<div class="thanh-cong-cu">
    <span class="phu"><?= e($thu[(int) date('w')]) ?>, <?= e(date('d/m/Y')) ?> · Cập nhật lúc <?= e(date('H:i')) ?></span>
    <form method="get" action="<?= e(url('/quan-tri')) ?>">
        <select name="thang" class="chon" aria-label="Chọn tháng" onchange="this.form.submit()">
            <?php foreach ($cacThang as $giaTri => $nhan): ?>
                <option value="<?= e($giaTri) ?>" <?= $giaTri === $thang ? 'selected' : '' ?>><?= e($nhan) ?></option>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit" class="nut nut-vien nut-nho">Xem</button></noscript>
    </form>
</div>

<div class="the-thong-ke">
    <div>
        <span class="phu">Tổng số sách</span>
        <strong><?= e(number_format($thongKe['ban_sao'], 0, ',', '.')) ?></strong>
        <small class="phu"><?= e(number_format($thongKe['dau_sach'], 0, ',', '.')) ?> đầu sách trong kho</small>
    </div>
    <div>
        <span class="phu">Đang cho mượn</span>
        <strong><?= e(number_format($thongKe['dang_cho_muon'], 0, ',', '.')) ?></strong>
        <small class="phu"><?= e($thongKe['phieu_moi_thang']) ?> phiếu mới trong tháng</small>
    </div>
    <div>
        <span class="phu">Phiếu quá hạn</span>
        <strong><?= e($thongKe['phieu_qua_han']) ?></strong>
        <small class="phu">Cần nhắc trả sách</small>
    </div>
    <div>
        <span class="phu">Phí phạt chưa thu</span>
        <strong><?= e(tien($thongKe['phi_phat_chua_thu'])) ?></strong>
        <small class="phu"><?= e($thongKe['phieu_phi_chua_thu']) ?> phiếu đang chờ thanh toán</small>
    </div>
</div>

<section class="khoi">
    <h2>Cảnh báo phiếu mượn quá hạn</h2>
    <p class="phu"><?= e($tongQuaHan) ?> phiếu cần xử lý · Phí tính <?= e(tien(PHI_PHAT_MOI_NGAY)) ?> / ngày / cuốn, đến ngày <?= e(date('d/m/Y')) ?>.</p>

    <div class="cuon-ngang">
    <table class="bang-du-lieu">
        <thead>
            <tr><th>Mã phiếu</th><th>Sinh viên</th><th>Tên sách</th><th>Hạn trả</th><th>Quá hạn</th><th>Phí tạm tính</th><th>Thao tác</th></tr>
        </thead>
        <tbody>
        <?php if ($quaHan === []): ?>
            <tr><td colspan="7" class="trong">Không có phiếu mượn nào quá hạn.</td></tr>
        <?php endif; ?>
        <?php foreach ($quaHan as $p): ?>
            <tr>
                <td><?= e(DinhDang::maPhieuMuon($p['id'])) ?></td>
                <td><?= e($p['sinh_vien']) ?></td>
                <td><?= e($p['ten_sach']) ?></td>
                <td><?= e(ngay($p['han_tra'])) ?></td>
                <td><?= e($p['so_ngay_tre']) ?> ngày</td>
                <td><?= e(tien($p['phi_tam_tinh'])) ?></td>
                <td>
                    <form method="post" action="<?= e(url('/quan-tri/nhac-tra/' . $p['id'])) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="quay_ve" value="">
                        <button type="submit" class="nut nut-vien nut-nho">Nhắc trả</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <div class="chan-bang">
        <span class="phu">Hiển thị <?= e(count($quaHan)) ?>/<?= e($tongQuaHan) ?> phiếu quá hạn</span>
        <a class="lien-ket-dam" href="<?= e(url('/quan-tri/qua-han')) ?>">Xem tất cả phiếu quá hạn</a>
    </div>
</section>

<div class="hai-cot">
    <section class="khoi">
        <h2>Đặt chỗ hôm nay</h2>
        <p class="phu"><?= e(GIU_CHO_GIO) ?> giờ giữ chỗ tính từ lúc xác nhận.</p>
        <p><?= e($datCho['cho_nhan']) ?> chỗ chờ nhận · <?= e($datCho['sap_het_han']) ?> sắp hết hạn<br>
           <a href="<?= e(url('/quan-tri/dat-cho')) ?>">Mở danh sách đặt chỗ</a></p>
    </section>
    <section class="khoi">
        <h2>Mượn trả tại quầy</h2>
        <p class="phu">Thời hạn <?= e(HAN_MUON_NGAY) ?> ngày · Gia hạn thêm <?= e(GIA_HAN_NGAY) ?> ngày.</p>
        <p><?= e($muonTra['luot_muon']) ?> lượt mượn · <?= e($muonTra['luot_tra']) ?> lượt trả hôm nay<br>
           <a href="<?= e(url('/quan-tri/muon-tra')) ?>">Mở màn hình mượn / trả</a></p>
    </section>
</div>