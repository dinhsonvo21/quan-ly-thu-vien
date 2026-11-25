<?php /** Quản lý thể loại. Biến: $ds, $sua */ ?>
<div class="tieu-de-trang">
    <div>
        <h1>Quản lý thể loại</h1>
        <p class="phu"><?= e(count($ds)) ?> thể loại · Không thể xóa thể loại còn sách.</p>
    </div>
    <a class="nut nut-vien" href="<?= e(url('/quan-tri/kho-sach')) ?>">Về kho sách</a>
</div>

<form method="post" action="<?= e(url('/quan-tri/the-loai/luu')) ?>" class="bo-loc-sach">
    <?= csrf_field() ?>
    <input type="text" name="ten_the_loai" class="chon o-tim" maxlength="80" placeholder="Tên thể loại mới" aria-label="Tên thể loại mới" required>
    <button type="submit" class="nut">+ Thêm thể loại</button>
</form>

<div class="cuon-ngang">
<table class="bang-du-lieu">
    <thead>
        <tr><th>Tên thể loại</th><th>Số đầu sách</th><th>Thao tác</th></tr>
    </thead>
    <tbody>
    <?php if ($ds === []): ?>
        <tr><td colspan="3" class="trong">Chưa có thể loại nào.</td></tr>
    <?php endif; ?>
    <?php foreach ($ds as $tl): ?>
        <tr>
            <?php if ($tl['id'] === $sua): ?>
                <td colspan="2">
                    <form method="post" action="<?= e(url('/quan-tri/the-loai/luu')) ?>" class="bo-loc-sach" style="margin:0">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($tl['id']) ?>">
                        <input type="text" name="ten_the_loai" class="chon o-tim" maxlength="80" value="<?= e($tl['ten_the_loai']) ?>" aria-label="Tên thể loại" required autofocus>
                        <button type="submit" class="nut nut-nho">Lưu</button>
                        <a class="nut nut-vien nut-nho" href="<?= e(url('/quan-tri/the-loai')) ?>">Hủy</a>
                    </form>
                </td>
                <td></td>
            <?php else: ?>
                <td><?= e($tl['ten_the_loai']) ?></td>
                <td><?= e($tl['so_sach']) ?></td>
                <td>
                    <div class="nhom-nut">
                        <a class="nut nut-vien nut-nho" href="<?= e(url('/quan-tri/the-loai?sua=' . $tl['id'])) ?>">Sửa</a>
                        <?php if ($tl['so_sach'] === 0): ?>
                            <form method="post" action="<?= e(url('/quan-tri/the-loai/' . $tl['id'] . '/xoa')) ?>"
                                  onsubmit="return confirm('Xóa thể loại này?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="nut nut-vien nut-nho">Xóa</button>
                            </form>
                        <?php else: ?>
                            <span class="phu nho">Còn <?= e($tl['so_sach']) ?> sách</span>
                        <?php endif; ?>
                    </div>
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>