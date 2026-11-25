<?php
/** SR-07 — Kho sách & danh mục. Biến: $boLoc, $ds, $tong, $trang, $soTrang, $theLoai, $khoSach, $cu */
$so = static fn(int $n): string => number_format($n, 0, ',', '.');

$lienKet = static function (int $t) use ($boLoc): string {
    $q = array_filter([
        'q'          => $boLoc['tu_khoa'],
        'the_loai'   => $boLoc['the_loai_id'] ?: null,
        'trang_thai' => $boLoc['trang_thai'],
        'trang'      => $t > 1 ? $t : null,
    ], static fn($v) => $v !== null && $v !== '');
    return url('/quan-tri/kho-sach' . ($q ? '?' . http_build_query($q) : ''));
};

$trangThaiSach = static function (array $s): string {
    if ($s['trang_thai_luu_hanh'] === 'ngung_phuc_vu') { return 'Ngừng phục vụ'; }
    return $s['san_co'] > 0 ? 'Còn sách' : 'Hết sách';
};

$tu = ($trang - 1) * SO_SACH_MOI_TRANG + 1;
$den = $tu + count($ds) - 1;
?>
<p class="nhan">SR-07 / Book Inventory &amp; Category Management</p>
<div class="tieu-de-trang">
    <div>
        <h1>Kho sách &amp; danh mục</h1>
        <p class="phu">Quản lý đầu sách, vị trí kệ và số lượng bản sao trong thư viện.</p>
    </div>
</div>

<div class="thanh-cong-cu">
    <span class="phu"><?= e($so($khoSach['dau_sach'])) ?> đầu sách · <?= e($so($khoSach['ban_sao'])) ?> bản sao</span>
    <div class="nhom-nut">
        <a class="nut nut-vien" href="<?= e(url('/quan-tri/the-loai')) ?>">Quản lý thể loại</a>
        <button type="button" class="nut" id="nut-them-sach">+ Thêm sách mới</button>
    </div>
</div>

<form method="get" action="<?= e(url('/quan-tri/kho-sach')) ?>" class="bo-loc-sach">
    <input type="search" name="q" class="chon o-tim" value="<?= e($boLoc['tu_khoa']) ?>" placeholder="Tìm theo tên, mã sách, tác giả, ISBN" aria-label="Tìm sách">
    <select name="the_loai" class="chon" aria-label="Thể loại" onchange="this.form.submit()">
        <option value="">Tất cả thể loại</option>
        <?php foreach ($theLoai as $tl): ?>
            <option value="<?= e($tl['id']) ?>" <?= (int) $tl['id'] === $boLoc['the_loai_id'] ? 'selected' : '' ?>><?= e($tl['ten_the_loai']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="trang_thai" class="chon" aria-label="Trạng thái" onchange="this.form.submit()">
        <option value="">Tất cả trạng thái</option>
        <option value="dang_luu_hanh" <?= $boLoc['trang_thai'] === 'dang_luu_hanh' ? 'selected' : '' ?>>Đang lưu hành</option>
        <option value="ngung_phuc_vu" <?= $boLoc['trang_thai'] === 'ngung_phuc_vu' ? 'selected' : '' ?>>Ngừng phục vụ</option>
    </select>
    <button type="submit" class="nut nut-vien nut-nho">Tìm</button>
</form>

<div class="cuon-ngang">
<table class="bang-du-lieu">
    <thead>
        <tr><th>Mã sách</th><th>Tên sách</th><th>Thể loại</th><th>Vị trí kệ</th><th>Tổng / Sẵn có</th><th>Trạng thái</th><th>Sửa</th></tr>
    </thead>
    <tbody>
    <?php if ($ds === []): ?>
        <tr><td colspan="7" class="trong">Không tìm thấy sách nào.</td></tr>
    <?php endif; ?>
    <?php foreach ($ds as $s): ?>
        <?php
        $duLieu = [
            'id' => $s['id'], 'ma_sach' => $s['ma_sach'], 'isbn' => $s['isbn'], 'ten_sach' => $s['ten_sach'],
            'tac_gia' => $s['tac_gia'], 'nha_xuat_ban' => $s['nha_xuat_ban'], 'nam_xuat_ban' => $s['nam_xuat_ban'],
            'the_loai_id' => $s['the_loai_id'], 'vi_tri_ke' => $s['vi_tri_ke'], 'tong_ban' => $s['tong_ban'],
            'trang_thai_luu_hanh' => $s['trang_thai_luu_hanh'], 'loai_tai_lieu' => $s['loai_tai_lieu'],
            'ngon_ngu' => $s['ngon_ngu'], 'gia_bia' => $s['gia_bia'], 'mo_ta' => $s['mo_ta'],
            'san_co' => $s['san_co'], 'dang_muon' => $s['dang_muon'], 'dang_giu' => $s['dang_giu'],
            'anh_url' => $s['anh_bia'] ? url('tep-tai-len/anh-bia-sach/' . $s['anh_bia']) : '',
        ];
        ?>
        <tr>
            <td><?= e($s['ma_sach']) ?></td>
            <td><?= e($s['ten_sach']) ?></td>
            <td><?= e($s['ten_the_loai']) ?></td>
            <td><?= e($s['vi_tri_ke']) ?></td>
            <td><?= e($s['tong_ban']) ?> / <?= e($s['san_co']) ?></td>
            <td><?= e($trangThaiSach($s)) ?></td>
            <td><button type="button" class="nut nut-vien nut-nho nut-sua" data-sach="<?= e(json_encode($duLieu, JSON_UNESCAPED_UNICODE)) ?>">Sửa</button></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<div class="chan-bang">
    <span class="phu"><?= $tong > 0 ? 'Hiển thị ' . e($tu) . '–' . e($den) . ' / ' . e($so($tong)) . ' đầu sách' : 'Không có kết quả' ?></span>
    <?php if ($soTrang > 1): ?>
        <nav class="phan-trang" aria-label="Phân trang">
            <?php if ($trang > 1): ?><a href="<?= e($lienKet($trang - 1)) ?>">Trang trước</a><?php endif; ?>
            <?php for ($i = max(1, $trang - 2); $i <= min($soTrang, $trang + 2); $i++): ?>
                <a class="<?= $i === $trang ? 'dang-chon' : '' ?>" href="<?= e($lienKet($i)) ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($trang < $soTrang): ?><a href="<?= e($lienKet($trang + 1)) ?>">Trang sau</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</div>

<dialog id="hop-sach" class="hop-thoai">
    <form method="post" action="<?= e(url('/quan-tri/kho-sach/luu')) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="">
        <div class="hop-dau">
            <h2 data-tieu-de>Chỉnh sửa thông tin sách</h2>
            <button type="button" class="nut-dong" data-dong aria-label="Đóng">&times;</button>
        </div>
        <p class="phu" data-phu></p>

        <div class="luoi-form">
            <label>Mã sách
                <input type="text" name="ma_sach_hien_thi" disabled>
            </label>
            <label>ISBN
                <input type="text" name="isbn" maxlength="17">
            </label>
            <label class="cot-2">Tên sách *
                <input type="text" name="ten_sach" maxlength="200" required>
            </label>
            <label>Tác giả *
                <input type="text" name="tac_gia" maxlength="150" required>
            </label>
            <div class="cap-o">
                <label>Nhà xuất bản
                    <input type="text" name="nha_xuat_ban" maxlength="100">
                </label>
                <label>Năm
                    <input type="number" name="nam_xuat_ban" min="1000" max="<?= (int) date('Y') + 1 ?>">
                </label>
            </div>
            <label>Thể loại *
                <select name="the_loai_id" required>
                    <option value="">— Chọn thể loại —</option>
                    <?php foreach ($theLoai as $tl): ?>
                        <option value="<?= e($tl['id']) ?>"><?= e($tl['ten_the_loai']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Vị trí kệ *
                <input type="text" name="vi_tri_ke" maxlength="30" required>
            </label>
            <label>Tổng số bản *
                <input type="number" name="tong_ban" min="1" max="1000" required>
            </label>
            <label>Trạng thái
                <select name="trang_thai_luu_hanh">
                    <option value="dang_luu_hanh">Đang lưu hành</option>
                    <option value="ngung_phuc_vu">Ngừng phục vụ</option>
                </select>
            </label>
            <p class="phu cot-2 nho" data-thong-ke></p>
            <label>Loại tài liệu
                <input type="text" name="loai_tai_lieu" maxlength="40">
            </label>
            <label>Ngôn ngữ
                <input type="text" name="ngon_ngu" maxlength="40">
            </label>
            <label>Giá bìa (đ)
                <input type="number" name="gia_bia" min="0" step="1000">
            </label>
            <label class="cot-2">Giới thiệu
                <textarea name="mo_ta" rows="3"></textarea>
            </label>
        </div>

        <div class="anh-bia">
            <div class="khung-anh"><img alt="" data-xem-anh hidden><span data-chua-anh>Ảnh</span></div>
            <p class="phu nho">Ảnh bìa sách · <span data-trang-thai-anh>Chưa tải lên</span><br>JPG, PNG · tối đa 5 MB</p>
            <label class="nut nut-vien nut-nho">Chọn ảnh bìa
                <input type="file" name="anh_bia" accept="image/jpeg,image/png" hidden>
            </label>
        </div>

        <div class="hop-chan">
            <button type="button" class="nut nut-vien" data-dong>Hủy</button>
            <button type="submit" class="nut">Lưu thông tin sách</button>
        </div>
    </form>
</dialog>

<script>
(function () {
    var hop = document.getElementById('hop-sach');
    if (!hop || typeof hop.showModal !== 'function') { return; }
    var form = hop.querySelector('form');
    var f = form.elements;
    var anh = hop.querySelector('[data-xem-anh]');
    var chuaAnh = hop.querySelector('[data-chua-anh]');
    var ttAnh = hop.querySelector('[data-trang-thai-anh]');
    var MAC_DINH = { isbn: '', ten_sach: '', tac_gia: '', nha_xuat_ban: '', nam_xuat_ban: '', the_loai_id: '',
        vi_tri_ke: '', tong_ban: 1, trang_thai_luu_hanh: 'dang_luu_hanh', loai_tai_lieu: 'Sách tham khảo',
        ngon_ngu: 'Tiếng Việt', gia_bia: 0, mo_ta: '' };

    function hienAnh(url, nhan) {
        anh.hidden = !url; chuaAnh.hidden = !!url;
        if (url) { anh.src = url; }
        ttAnh.textContent = nhan;
    }

    function mo(s) {
        var sua = !!(s && s.id);
        var d = Object.assign({}, MAC_DINH, s || {});
        form.reset();
        f.id.value = sua ? s.id : '';
        Object.keys(MAC_DINH).forEach(function (k) { f[k].value = d[k] == null ? '' : d[k]; });
        f.ma_sach_hien_thi.value = sua && s.ma_sach ? s.ma_sach : 'Tự động tạo';
        hop.querySelector('[data-tieu-de]').textContent = sua ? 'Chỉnh sửa thông tin sách' : 'Thêm sách mới';
        hop.querySelector('[data-phu]').textContent = sua
            ? (s.ma_sach + ' · Cập nhật thông tin đầu sách và số lượng bản sao trong kho.')
            : 'Nhập thông tin đầu sách mới và số lượng bản sao.';
        var tk = hop.querySelector('[data-thong-ke]');
        tk.textContent = sua && s.san_co != null
            ? (s.san_co + ' sẵn có · ' + s.dang_muon + ' đang mượn · ' + s.dang_giu + ' đang giữ chỗ') : '';
        hienAnh(sua ? s.anh_url : '', sua && s.anh_url ? 'Đang dùng ảnh hiện tại' : 'Chưa tải lên');
        hop.showModal();
    }

    document.getElementById('nut-them-sach').addEventListener('click', function () { mo(null); });
    document.querySelectorAll('.nut-sua').forEach(function (n) {
        n.addEventListener('click', function () { mo(JSON.parse(n.dataset.sach)); });
    });
    hop.querySelectorAll('[data-dong]').forEach(function (n) {
        n.addEventListener('click', function () { hop.close(); });
    });
    hop.addEventListener('click', function (e) { if (e.target === hop) { hop.close(); } });

    f.anh_bia.addEventListener('change', function () {
        var t = f.anh_bia.files[0];
        if (!t) { return; }
        if (['image/jpeg', 'image/png'].indexOf(t.type) < 0 || t.size > 5 * 1024 * 1024) {
            alert('Chỉ chấp nhận ảnh JPG/PNG, tối đa 5 MB.');
            f.anh_bia.value = '';
            return;
        }
        hienAnh(URL.createObjectURL(t), t.name);
    });

    var cu = <?= json_encode($cu, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
    if (cu) { mo(cu); }
})();
</script>