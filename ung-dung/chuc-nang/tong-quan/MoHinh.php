<?php
declare(strict_types=1);

namespace ChucNang\TongQuan;

use Loi\CoSoDuLieu;

/** Số liệu thống kê — chỉ ĐỌC từ books / book_copies / borrow_tickets / fines / reservations. */
final class MoHinh
{
    /**
     * 4 thẻ SR-06: tong_sach (đầu sách / bản sao), dang_cho_muon, phieu_qua_han, phi_phat_chua_thu.
     * "Quá hạn" tính từ han_tra < hôm nay và chưa trả — không lưu thành trạng thái.
     * @return array<string, int>
     */
    public static function thongKe(string $thang): array
    {
        // STUB: số liệu giả để dựng giao diện.
        return ['dau_sach' => 1248, 'ban_sao' => 12480, 'dang_cho_muon' => 326, 'phieu_qua_han' => 12, 'phi_phat_chua_thu' => 186000];
    }

    /** @return array<int, array<string, mixed>> mã phiếu, sinh viên, tên sách, hạn trả, số ngày quá hạn, phí tạm tính */
    public static function phieuQuaHan(?int $gioiHan = null): array
    {
        // TODO (Trạng): phí tạm tính = Quân\TinhPhiPhat::phiQuaHan(han_tra, hôm nay).
        return [];
    }

    /** @return array{cho_nhan:int, sap_het_han:int} */
    public static function datChoHomNay(): array
    {
        // TODO (Trạng)
        return ['cho_nhan' => 0, 'sap_het_han' => 0];
    }

    /** @return array{luot_muon:int, luot_tra:int} */
    public static function muonTraHomNay(): array
    {
        // TODO (Trạng)
        return ['luot_muon' => 0, 'luot_tra' => 0];
    }
}
