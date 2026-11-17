<?php
declare(strict_types=1);

namespace ChucNang\LuuThong;

use Loi\CoSoDuLieu;

/** Bảng `borrow_tickets`. Mọi hàm ghi phải chạy trong CoSoDuLieu::giaoDich(). */
final class PhieuMuon
{
    /**
     * Đủ điều kiện mượn = tài khoản hoạt động VÀ < SO_SACH_TOI_DA phiếu đang mượn
     * VÀ không có phiếu quá hạn VÀ không nợ phí phạt. (SR-08 khối kiểm tra điều kiện)
     * @return array<string, mixed>
     */
    public static function dieuKienMuon(string $maSinhVien): array
    {
        // TODO (Quân)
        return [];
    }

    /** Tạo phiếu mượn, trả về id. $datChoId = null nếu cho mượn trực tiếp. */
    public static function tao(int $nguoiDungId, int $banSaoId, int $thuThuId, ?int $datChoId = null): int
    {
        // TODO (Quân): ngay_muon = hôm nay, han_tra = hôm nay + HAN_MUON_NGAY.
        return 0;
    }

    /** @return array<int, array<string, mixed>> phiếu đang mượn, có lọc theo từ khóa/trạng thái — bảng nền SR-09. */
    public static function layDangMuon(?string $tuKhoa = null): array
    {
        // TODO (Quân)
        return [];
    }

    public static function danhDauDaTra(int $phieuId, string $ngayTra): bool
    {
        // TODO (Quân): chặn ngày trả trong tương lai hoặc trước ngày mượn.
        return false;
    }
}
