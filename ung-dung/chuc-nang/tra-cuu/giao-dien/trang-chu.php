<?php
$boLoc = $boLoc ?? [];
$theLoai = $theLoai ?? [];
$danhSach = $danhSach ?? [];
$tongSo = $tongSo ?? 0;
$trang = $trang ?? 1;
$tongSoTrang = $tongSoTrang ?? 1;

$q = $boLoc['q'] ?? '';
$theLoaiChon = $boLoc['the_loai'] ?? '';
$loaiTaiLieuChon = $boLoc['loai_tai_lieu'] ?? '';
$trangThaiChon = $boLoc['trang_thai'] ?? '';
$sapXepChon = $boLoc['sap_xep'] ?? 'moi_nhat';
$dangLoc = $q !== '' || $theLoaiChon !== '' || $loaiTaiLieuChon !== '' || $trangThaiChon !== '' || $sapXepChon !== 'moi_nhat';

$thamSoTrang = [];
foreach (['q', 'the_loai', 'loai_tai_lieu', 'trang_thai', 'sap_xep'] as $tenThamSo) {
    if (($boLoc[$tenThamSo] ?? '') !== '' && ($tenThamSo !== 'sap_xep' || ($boLoc[$tenThamSo] ?? '') !== 'moi_nhat')) {
        $thamSoTrang[$tenThamSo] = $boLoc[$tenThamSo];
    }
}
?>
<section style="border:1px solid var(--vien); border-radius:10px; background:var(--nen-xam); padding:36px 40px; margin-bottom:32px">
    <p class="nhan">Thư viện số QNU Library</p>
    <h1 style="margin:0 0 8px; font-size:34px">Khám phá kho sách của chúng tôi</h1>
    <p style="margin:0 0 22px; color:var(--chu-phu)">Tìm kiếm sách, tài liệu và giáo trình phục vụ học tập và nghiên cứu.</p>

    <form action="<?= e(url('/')) ?>#danh-muc" method="get" style="display:flex; gap:10px; max-width:760px">
        <input name="q" type="search" value="<?= e($q) ?>" placeholder="Tên sách, tác giả hoặc mã ISBN..." aria-label="Từ khóa tìm kiếm" style="flex:1">
        <button class="nut" type="submit">Tìm kiếm sách</button>
    </form>
</section>

<section id="danh-muc" style="scroll-margin-top:20px">
    <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:18px">
        <div>
            <p class="nhan">Danh mục</p>
            <h2 style="margin:0">Sách trong thư viện</h2>
        </div>
        <p style="margin:0; color:var(--chu-phu)">Tìm thấy <?= (int) $tongSo ?> cuốn</p>
    </div>

    <form action="<?= e(url('/')) ?>#danh-muc" method="get" id="bo-loc-sach"
          style="display:grid; grid-template-columns:repeat(4,minmax(0,1fr)) auto auto; gap:10px; align-items:end; margin-bottom:22px">
        <input type="hidden" name="q" value="<?= e($q) ?>">

        <div>
            <label for="the_loai">Thể loại</label>
            <select id="the_loai" name="the_loai">
                <option value="">Tất cả thể loại</option>
                <?php foreach ($theLoai as $loai): ?>
                    <option value="<?= e((string) $loai['id']) ?>" <?= $theLoaiChon === (string) $loai['id'] ? 'selected' : '' ?>>
                        <?= e($loai['ten_the_loai']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="loai_tai_lieu">Loại tài liệu</label>
            <select id="loai_tai_lieu" name="loai_tai_lieu">
                <option value="">Loại tài liệu</option>
                <?php foreach (['Sách tham khảo', 'Giáo trình'] as $loaiTaiLieu): ?>
                    <option value="<?= e($loaiTaiLieu) ?>" <?= $loaiTaiLieuChon === $loaiTaiLieu ? 'selected' : '' ?>>
                        <?= e($loaiTaiLieu) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="trang_thai">Trạng thái sách</label>
            <select id="trang_thai" name="trang_thai">
                <option value="">Trạng thái sách</option>
                <option value="con_sach" <?= $trangThaiChon === 'con_sach' ? 'selected' : '' ?>>Còn sách</option>
                <option value="dang_muon" <?= $trangThaiChon === 'dang_muon' ? 'selected' : '' ?>>Đang mượn</option>
            </select>
        </div>

        <div>
            <label for="sap_xep">Sắp xếp</label>
            <select id="sap_xep" name="sap_xep">
                <option value="moi_nhat" <?= $sapXepChon === 'moi_nhat' ? 'selected' : '' ?>>Mới thêm gần đây</option>
                <option value="ten_az" <?= $sapXepChon === 'ten_az' ? 'selected' : '' ?>>Tên A-Z</option>
                <option value="nam_xb" <?= $sapXepChon === 'nam_xb' ? 'selected' : '' ?>>Năm xuất bản mới nhất</option>
            </select>
        </div>

        <button class="nut" type="submit">Lọc</button>
        <?php if ($dangLoc): ?>
            <a class="nut nut-vien" href="<?= e(url('/#danh-muc')) ?>">Xóa lọc</a>
        <?php endif; ?>
    </form>

    <?php if ($danhSach === []): ?>
        <div class="dang-xay-dung" style="text-align:center">
            <h3 style="margin-top:0">Không tìm thấy cuốn sách phù hợp</h3>
            <p>Hãy thử thay đổi từ khóa hoặc xóa bớt điều kiện lọc.</p>
        </div>
    <?php else: ?>
        <div class="the-sach-luoi" style="grid-template-columns:repeat(4,minmax(0,1fr));">
            <?php foreach ($danhSach as $sach): ?>
                <?php
                $conSach = (int) ($sach['con_sach'] ?? 0) > 0;
                $anhBia = trim((string) ($sach['anh_bia'] ?? ''));
                ?>
                <article style="border:1px solid var(--vien); border-radius:8px; overflow:hidden; background:var(--nen)">
                    <div style="height:210px; border-bottom:1px solid var(--vien); background:var(--nen-xam); display:flex; align-items:center; justify-content:center">
                        <?php if ($anhBia !== ''): ?>
                            <img src="<?= e(url('/tep-tai-len/anh-bia-sach/' . rawurlencode($anhBia))) ?>"
                                 alt="<?= e($sach['ten_sach']) ?>"
                                 style="width:100%; height:100%; object-fit:cover">
                        <?php else: ?>
                            <span style="color:var(--chu-phu); font-size:13px">Chưa có ảnh bìa</span>
                        <?php endif; ?>
                    </div>
                    <div style="padding:16px">
                        <span style="display:inline-block; margin-bottom:10px; padding:4px 8px; border-radius:999px; font-size:12px; font-weight:700; <?= $conSach ? 'background:#e3f0e5; color:#285d31;' : 'background:var(--nen-xam-dam); color:var(--canh-bao);' ?>">
                            <?= $conSach ? 'Còn sách' : 'Đang mượn' ?>
                        </span>
                        <h3 style="margin:0 0 6px; font-size:17px"><?= e($sach['ten_sach']) ?></h3>
                        <p style="margin:0 0 6px; color:var(--chu-phu); font-size:14px">
                            <?= e($sach['tac_gia']) ?> · <?= e((string) ($sach['nam_xuat_ban'] ?? 'Không rõ năm')) ?>
                        </p>
                        <p style="margin:0 0 14px; color:var(--chu-phu); font-size:13px"><?= e($sach['ten_the_loai']) ?></p>
                        <a class="nut nut-vien" href="<?= e(url('/sach/' . (int) $sach['id'])) ?>" style="width:100%; text-align:center">Xem chi tiết</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($tongSoTrang > 1): ?>
        <nav aria-label="Phân trang" style="display:flex; align-items:center; justify-content:center; gap:14px; margin-top:26px">
            <?php
            $trangTruoc = max(1, $trang - 1);
            $trangSau = min($tongSoTrang, $trang + 1);
            ?>
            <?php if ($trang > 1): ?>
                <?php $thamSoTrang['trang'] = $trangTruoc; ?>
                <a class="nut nut-vien" href="<?= e(url('/?' . http_build_query($thamSoTrang, '', '&', PHP_QUERY_RFC3986))) ?>#danh-muc">Trước</a>
            <?php else: ?>
                <span style="padding:10px 18px; color:var(--chu-phu)">Trước</span>
            <?php endif; ?>

            <span>Trang <?= (int) $trang ?> / <?= (int) $tongSoTrang ?></span>

            <?php if ($trang < $tongSoTrang): ?>
                <?php $thamSoTrang['trang'] = $trangSau; ?>
                <a class="nut nut-vien" href="<?= e(url('/?' . http_build_query($thamSoTrang, '', '&', PHP_QUERY_RFC3986))) ?>#danh-muc">Sau</a>
            <?php else: ?>
                <span style="padding:10px 18px; color:var(--chu-phu)">Sau</span>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</section>

<style>
@media (max-width: 1000px) {
    .the-sach-luoi { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
}
@media (max-width: 720px) {
    #bo-loc-sach { grid-template-columns: 1fr 1fr !important; }
    .the-sach-luoi { grid-template-columns: 1fr !important; }
}
@media (max-width: 520px) {
    #bo-loc-sach { grid-template-columns: 1fr !important; }
}
</style>
