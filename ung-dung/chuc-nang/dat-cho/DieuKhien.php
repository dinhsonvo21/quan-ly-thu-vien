<?php
declare(strict_types=1);

namespace ChucNang\DatCho;

use Loi\DieuKhienCoSo;
use Loi\DinhDang;
use Loi\PhanQuyen;
use Loi\PhienLamViec;
use Loi\TrangLoi;

/** Xử lý đặt giữ chỗ trực tuyến cho bạn đọc. */
final class DieuKhien extends DieuKhienCoSo
{
    public function tao(): void
    {
        PhanQuyen::yeuCauVaiTro([VAI_TRO_BAN_DOC]);

        $sachNhap = $_POST['sach_id'] ?? '';
        $sachId = is_string($sachNhap) && ctype_digit($sachNhap) ? (int) $sachNhap : 0;
        if ($sachId < 1) {
            $this->flash('loi', 'Thông tin sách không hợp lệ.');
            $this->chuyenHuong('/');
        }

        try {
            $datChoId = MoHinh::tao((int) PhienLamViec::nguoiDung()['id'], $sachId);
        } catch (\DomainException $ngoaiLe) {
            $this->flash('loi', $ngoaiLe->getMessage());
            $this->chuyenHuong('/sach/' . $sachId);
        }

        $this->flash(
            'thanh_cong',
            'Đặt giữ chỗ thành công (' . DinhDang::maDatCho($datChoId) . '). Vui lòng nhận sách trong '
                . GIU_CHO_GIO . ' giờ.'
        );
        $this->chuyenHuong('/sach/' . $sachId);
    }

    public function huy(string $id): void
    {
        PhanQuyen::yeuCauVaiTro([VAI_TRO_BAN_DOC]);
        if (!ctype_digit($id) || (int) $id < 1) {
            TrangLoi::hien(404);
        }

        $nguoiDungId = (int) PhienLamViec::nguoiDung()['id'];
        $daHuy = MoHinh::huy((int) $id, $nguoiDungId);
        $sachNhap = $_POST['sach_id'] ?? '';
        $sachId = is_string($sachNhap) && ctype_digit($sachNhap) ? (int) $sachNhap : 0;

        if ($daHuy) {
            $this->flash('thanh_cong', 'Đã hủy phiếu giữ chỗ và giải phóng bản sao.');
        } else {
            $this->flash('thong_tin', 'Phiếu giữ chỗ không còn ở trạng thái có thể hủy.');
        }

        if ($sachId > 0) {
            $this->chuyenHuong('/sach/' . $sachId);
        }
        $this->chuyenHuong('/ho-so#dat-cho');
    }
}
