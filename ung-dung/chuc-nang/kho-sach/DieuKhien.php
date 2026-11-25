<?php
declare(strict_types=1);

namespace ChucNang\KhoSach;

use Loi\CoSoDuLieu;
use Loi\DieuKhienCoSo;
use Loi\KiemTra;
use Loi\TaiAnh;

/** Chủ sở hữu: Nguyễn Minh Trạng. Báo cáo: 2.2.3, 4.7 (SR-07). */
final class DieuKhien extends DieuKhienCoSo
{
    // ------------------------------------------------------------------
    // SR-07: Kho sách
    // ------------------------------------------------------------------

    public function danhSach(): void
    {
        $trangThai = (string) ($_GET['trang_thai'] ?? '');
        $boLoc = [
            'tu_khoa'     => trim((string) ($_GET['q'] ?? '')),
            'the_loai_id' => max(0, (int) ($_GET['the_loai'] ?? 0)),
            'trang_thai'  => in_array($trangThai, ['dang_luu_hanh', 'ngung_phuc_vu'], true) ? $trangThai : '',
        ];
        $kq = Sach::timKiem($boLoc, max(1, (int) ($_GET['trang'] ?? 1)));

        // Dữ liệu form cũ khi lưu bị lỗi -> mở lại popup với các giá trị đã nhập.
        $cu = $_SESSION['kho_sach_cu'] ?? null;
        unset($_SESSION['kho_sach_cu']);

        $this->hienThi('quan-ly-kho-sach', [
            'boLoc'   => $boLoc,
            'ds'      => $kq['danh_sach'],
            'tong'    => $kq['tong'],
            'trang'   => $kq['trang'],
            'soTrang' => $kq['so_trang'],
            'theLoai' => TheLoai::tatCa(),
            'khoSach' => Sach::tongQuan(),
            'cu'      => $cu,
        ], 'quan-tri', 'Kho sách & danh mục');
    }

    public function luuSach(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $id = $id > 0 ? $id : null;
        $f = $this->docForm();
        $tongBan = filter_var($_POST['tong_ban'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000]]);
        $sachCu = $id !== null ? Sach::timTheoId($id) : null;

        $loi = $this->kiemTra($f, $tongBan);
        if ($id !== null && $sachCu === null) {
            $loi[] = 'Không tìm thấy sách cần sửa.';
        }
        if ($loi !== []) {
            $this->baoLoi($loi, $id, $f, $sachCu);
        }

        $d = $this->chuanHoa($f);

        // Ảnh bìa (tuỳ chọn): chỉ xử lý khi người dùng có chọn file.
        $anhMoi = null;
        if (isset($_FILES['anh_bia']) && ($_FILES['anh_bia']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $anhMoi = TaiAnh::luu($_FILES['anh_bia'], 'anh-bia-sach');
            } catch (\InvalidArgumentException $e) {
                $this->baoLoi([$e->getMessage()], $id, $f, $sachCu);
            }
        }
        $d['anh_bia'] = $anhMoi;

        try {
            CoSoDuLieu::giaoDich(static function () use ($id, $d, $tongBan): void {
                $sachId = Sach::luu($id, $d);
                $chenh = $tongBan - ($id === null ? 0 : BanSao::demTong($sachId));
                if ($chenh > 0) {
                    BanSao::taoBanSao($sachId, $chenh);
                } elseif ($chenh < 0 && !BanSao::xoaBanSanCo($sachId, -$chenh)) {
                    throw new \DomainException(
                        'Không thể giảm số bản: chỉ xóa được các bản đang sẵn có và chưa từng có phiếu mượn hoặc đặt chỗ.'
                    );
                }
            });
        } catch (\DomainException $e) {
            $this->xoaAnh($anhMoi);
            $this->baoLoi([$e->getMessage()], $id, $f, $sachCu);
        } catch (\Throwable $e) {
            $this->xoaAnh($anhMoi);
            error_log('Lưu sách: ' . $e->getMessage());
            $this->baoLoi(['Không lưu được thông tin sách, vui lòng thử lại.'], $id, $f, $sachCu);
        }

        if ($anhMoi !== null && $sachCu !== null) {
            $this->xoaAnh($sachCu['anh_bia'] ?? null);
        }
        $this->flash('thanh_cong', $id === null ? 'Đã thêm sách mới.' : 'Đã cập nhật thông tin sách.');
        $this->chuyenHuong('/quan-tri/kho-sach');
    }

    // ------------------------------------------------------------------
    // SR-07 (phụ): Quản lý thể loại
    // ------------------------------------------------------------------

    public function theLoai(): void
    {
        $this->hienThi('quan-ly-the-loai', [
            'ds'  => TheLoai::tatCaKemSoSach(),
            'sua' => max(0, (int) ($_GET['sua'] ?? 0)),   // id thể loại đang sửa (0 = không sửa)
        ], 'quan-tri', 'Quản lý thể loại');
    }

    public function luuTheLoai(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $id = $id > 0 ? $id : null;
        $ten = trim((string) ($_POST['ten_the_loai'] ?? ''));
        $quayVe = '/quan-tri/the-loai' . ($id !== null ? '?sua=' . $id : '');

        if (!KiemTra::khongRong($ten)) {
            $this->flash('loi', 'Vui lòng nhập tên thể loại.');
        } elseif (mb_strlen($ten) > 80) {
            $this->flash('loi', 'Tên thể loại tối đa 80 ký tự.');
        } elseif ($id !== null && !TheLoai::tonTai($id)) {
            $this->flash('loi', 'Không tìm thấy thể loại cần sửa.');
            $quayVe = '/quan-tri/the-loai';
        } elseif (TheLoai::trungTen($ten, $id)) {
            $this->flash('loi', 'Thể loại "' . $ten . '" đã tồn tại.');
        } else {
            TheLoai::luu($id, $ten);
            $this->flash('thanh_cong', $id === null ? 'Đã thêm thể loại.' : 'Đã đổi tên thể loại.');
            $quayVe = '/quan-tri/the-loai';
        }
        $this->chuyenHuong($quayVe);
    }

    public function xoaTheLoai(string $id): void
    {
        if (TheLoai::xoa((int) $id)) {
            $this->flash('thanh_cong', 'Đã xóa thể loại.');
        } else {
            $this->flash('loi', 'Không xóa được: thể loại vẫn còn sách hoặc không tồn tại.');
        }
        $this->chuyenHuong('/quan-tri/the-loai');
    }

    // ------------------------------------------------------------------
    // Hàm hỗ trợ
    // ------------------------------------------------------------------

    /** @return array<string, string> */
    private function docForm(): array
    {
        $t = static fn(string $khoa): string => trim((string) ($_POST[$khoa] ?? ''));
        return [
            'isbn' => $t('isbn'), 'ten_sach' => $t('ten_sach'), 'tac_gia' => $t('tac_gia'),
            'nha_xuat_ban' => $t('nha_xuat_ban'), 'nam_xuat_ban' => $t('nam_xuat_ban'),
            'ngon_ngu' => $t('ngon_ngu'), 'loai_tai_lieu' => $t('loai_tai_lieu'), 'mo_ta' => $t('mo_ta'),
            'gia_bia' => $t('gia_bia'), 'vi_tri_ke' => $t('vi_tri_ke'),
            'trang_thai_luu_hanh' => $t('trang_thai_luu_hanh'), 'the_loai_id' => $t('the_loai_id'),
        ];
    }

    /** @param array<string, string> $f @return array<int, string> danh sách lỗi (rỗng nếu hợp lệ) */
    private function kiemTra(array $f, int|false $tongBan): array
    {
        $loi = [];
        $dai = static fn(string $s, int $max): bool => mb_strlen($s) > $max;

        if (!KiemTra::khongRong($f['ten_sach']))  { $loi[] = 'Vui lòng nhập tên sách.'; }
        elseif ($dai($f['ten_sach'], 200))        { $loi[] = 'Tên sách tối đa 200 ký tự.'; }
        if (!KiemTra::khongRong($f['tac_gia']))   { $loi[] = 'Vui lòng nhập tác giả.'; }
        elseif ($dai($f['tac_gia'], 150))         { $loi[] = 'Tác giả tối đa 150 ký tự.'; }
        if (!KiemTra::khongRong($f['vi_tri_ke'])) { $loi[] = 'Vui lòng nhập vị trí kệ.'; }
        elseif ($dai($f['vi_tri_ke'], 30))        { $loi[] = 'Vị trí kệ tối đa 30 ký tự.'; }
        if ($dai($f['nha_xuat_ban'], 100))        { $loi[] = 'Nhà xuất bản tối đa 100 ký tự.'; }
        if ($dai($f['ngon_ngu'], 40) || $dai($f['loai_tai_lieu'], 40)) {
            $loi[] = 'Ngôn ngữ và loại tài liệu tối đa 40 ký tự.';
        }

        if ($f['isbn'] !== '' && preg_match('/^[0-9Xx-]{10,17}$/', $f['isbn']) !== 1) {
            $loi[] = 'ISBN không hợp lệ (10–17 ký tự gồm số, dấu gạch ngang hoặc X).';
        }
        if ($f['nam_xuat_ban'] !== '' && (!ctype_digit($f['nam_xuat_ban'])
                || (int) $f['nam_xuat_ban'] < 1000 || (int) $f['nam_xuat_ban'] > (int) date('Y') + 1)) {
            $loi[] = 'Năm xuất bản không hợp lệ.';
        }
        if ($f['gia_bia'] !== '' && !ctype_digit($f['gia_bia'])) {
            $loi[] = 'Giá bìa phải là số nguyên không âm (đồng).';
        }
        if (!ctype_digit($f['the_loai_id']) || !TheLoai::tonTai((int) $f['the_loai_id'])) {
            $loi[] = 'Vui lòng chọn thể loại.';
        }
        if (!in_array($f['trang_thai_luu_hanh'], ['dang_luu_hanh', 'ngung_phuc_vu'], true)) {
            $loi[] = 'Trạng thái lưu hành không hợp lệ.';
        }
        if ($tongBan === false) {
            $loi[] = 'Tổng số bản phải là số nguyên từ 1 đến 1000.';
        }
        return $loi;
    }

    /** @param array<string, string> $f @return array<string, mixed> */
    private function chuanHoa(array $f): array
    {
        return [
            'isbn'                => $f['isbn'] !== '' ? $f['isbn'] : null,
            'ten_sach'            => $f['ten_sach'],
            'tac_gia'             => $f['tac_gia'],
            'nha_xuat_ban'        => $f['nha_xuat_ban'] !== '' ? $f['nha_xuat_ban'] : null,
            'nam_xuat_ban'        => $f['nam_xuat_ban'] !== '' ? (int) $f['nam_xuat_ban'] : null,
            'ngon_ngu'            => $f['ngon_ngu'] !== '' ? $f['ngon_ngu'] : 'Tiếng Việt',
            'loai_tai_lieu'       => $f['loai_tai_lieu'] !== '' ? $f['loai_tai_lieu'] : 'Sách tham khảo',
            'mo_ta'               => $f['mo_ta'] !== '' ? $f['mo_ta'] : null,
            'gia_bia'             => $f['gia_bia'] !== '' ? (int) $f['gia_bia'] : 0,
            'vi_tri_ke'           => $f['vi_tri_ke'],
            'trang_thai_luu_hanh' => $f['trang_thai_luu_hanh'],
            'the_loai_id'         => (int) $f['the_loai_id'],
        ];
    }

    /**
     * Báo lỗi, lưu lại dữ liệu đã nhập để mở lại popup, rồi quay về danh sách.
     * @param array<int, string> $loi
     * @param array<string, string> $f
     */
    private function baoLoi(array $loi, ?int $id, array $f, ?array $sachCu): never
    {
        $_SESSION['kho_sach_cu'] = $f + [
            'id'        => $id,
            'ma_sach'   => $sachCu['ma_sach'] ?? null,
            'tong_ban'  => trim((string) ($_POST['tong_ban'] ?? '')),
            'san_co'    => $sachCu['san_co'] ?? null,
            'dang_muon' => $sachCu['dang_muon'] ?? null,
            'dang_giu'  => $sachCu['dang_giu'] ?? null,
            'anh_url'   => !empty($sachCu['anh_bia']) ? url('tep-tai-len/anh-bia-sach/' . $sachCu['anh_bia']) : '',
        ];
        foreach ($loi as $l) {
            $this->flash('loi', $l);
        }
        $this->chuyenHuong('/quan-tri/kho-sach');
    }

    private function xoaAnh(?string $ten): void
    {
        if ($ten === null || $ten === '') {
            return;
        }
        $duongDan = THU_MUC_TAI_LEN . '/anh-bia-sach/' . basename($ten);
        if (is_file($duongDan)) {
            @unlink($duongDan);
        }
    }
}