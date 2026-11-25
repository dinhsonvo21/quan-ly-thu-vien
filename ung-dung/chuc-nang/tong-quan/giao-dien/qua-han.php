<?php
/** Tất cả phiếu quá hạn. Biến: $ds, $tong, $trang, $soTrang */
use Loi\DinhDang;
?>
<p class="nhan">SR-06 / Phiếu quá hạn</p>
<div class="tieu-de-trang">
    <div>
        <h1>Phiếu mượn quá hạn</h1>
        <p class="phu"><?= e($tong) ?> phiếu · Phí tính <?= e(tien(PHI_PHAT_MOI_NGAY)) ?> / ngày / cuốn, đến ngày <?= e(date('d/m/Y')) ?>.</p>
    </div>
    <a class="nut nut-vien" href="<?= e(url('/quan-tri')) ?>">Về tổng quan</a>
</div>

<section class="khoi">
    <div class="cuon-ngang">
    <table class="bang-du-lieu">
        <thead>
            <tr><th>Mã phiếu</th><th>Sinh viên</th><th>Tên sách</th><th>Hạn trả</th><th>Quá hạn</th><th>Phí tạm tính</th><th>Thao tác</th></tr>
        </thead>
        <tbody>
        <?php if ($ds === []): ?>
            <tr><td colspan="7" class="trong">Không có phiếu mượn nào quá hạn.</td></tr>
        <?php endif; ?>
        <?php foreach ($ds as $p): ?>
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
                        <input type="hidden" name="quay_ve" value="qua-han">
                        <button type="submit" class="nut nut-vien nut-nho">Nhắc trả</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <?php if ($soTrang > 1): ?>
        <nav class="phan-trang" aria-label="Phân trang">
            <?php for ($i = 1; $i <= $soTrang; $i++): ?>
                <a class="<?= $i === $trang ? 'dang-chon' : '' ?>" href="<?= e(url('/quan-tri/qua-han?trang=' . $i)) ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>