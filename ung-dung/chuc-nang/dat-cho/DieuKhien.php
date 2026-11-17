<?php
declare(strict_types=1);

namespace ChucNang\DatCho;

use Loi\DieuKhienCoSo;

/** Chủ sở hữu: Võ Đình Sơn. Báo cáo: 2.2.4 (Đặt trước sách trực tuyến). */
final class DieuKhien extends DieuKhienCoSo
{
    public function tao(): void
    {
        // TODO (Sơn): đọc sach_id + noi_nhan -> MoHinh::tao() (trong giao dịch: khóa 1 bản sao san_co -> dang_giu,
        //             het_han = now + GIU_CHO_GIO giờ) -> flash -> quay lại /sach/{id}. Hết sách thì báo rõ.
        $this->chuaLam('Đặt trước sách', '/');
    }

    public function huy(string $id): void
    {
        // TODO (Sơn): MoHinh::huy((int) $id, nguoiDungId) — chỉ chủ đơn được hủy; trả bản sao về san_co.
        $this->chuaLam('Hủy đặt chỗ', '/ho-so');
    }
}
