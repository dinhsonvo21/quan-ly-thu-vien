<?php
declare(strict_types=1);

namespace ChucNang\DatCho;

use Loi\CoSoDuLieu;

/**
 * Bảng `reservations`. ĐÂY LÀ HỢP ĐỒNG giữa các thành viên — giữ nguyên chữ ký hàm:
 *   - Trạng (SR-05) gọi: layCuaNguoiDung(), huy()
 *   - Quân (SR-08)  gọi: layDangCho(), danhDauDaGiao()
 *   - Tác vụ nền    gọi: huyQuaHan()
 */
final class MoHinh
{
    /** Tạo đơn giữ chỗ (giao dịch + khóa bản sao). Trả về id đơn. */
    public static function tao(int $nguoiDungId, int $sachId, string $noiNhan = NOI_NHAN_MAC_DINH): int
    {
        // TODO (Sơn)
        return 0;
    }

    /**
     * Đơn đang chờ nhận, mới hết hạn trước. Dùng cho bảng SR-08.
     * @return array<int, array{id:int, ma_dat_cho:string, sinh_vien:string, ma_sv:string, ten_sach:string, het_han:string}>
     */
    public static function layDangCho(?string $tuKhoa = null): array
    {
        // STUB: dữ liệu giả để Quân dựng giao diện — thay bằng SQL thật.
        return [
            ['id' => 1042, 'ma_dat_cho' => 'DC-1042', 'sinh_vien' => 'Nguyễn Trần Thị', 'ma_sv' => 'SV2024001', 'ten_sach' => 'The Midnight Library', 'het_han' => date('Y-m-d H:i:s', time() + 23 * 3600)],
            ['id' => 1038, 'ma_dat_cho' => 'DC-1038', 'sinh_vien' => 'Nguyễn Trần Thị', 'ma_sv' => 'SV2024001', 'ten_sach' => 'The Creative Act', 'het_han' => date('Y-m-d H:i:s', time() + 7 * 3600)],
            ['id' => 1036, 'ma_dat_cho' => 'DC-1036', 'sinh_vien' => 'Nguyễn Minh Anh', 'ma_sv' => 'SV2024012', 'ten_sach' => 'Atomic Habits', 'het_han' => date('Y-m-d H:i:s', time() + 4 * 3600)],
        ];
    }

    /** @return array<int, array<string, mixed>> đơn đang hoạt động của một bạn đọc (bảng SR-05). */
    public static function layCuaNguoiDung(int $nguoiDungId): array
    {
        // TODO (Sơn)
        return [];
    }

    /** Chuyển đơn sang 'da_giao' — Quân gọi bên trong giao dịch lập phiếu mượn. */
    public static function danhDauDaGiao(int $datChoId): bool
    {
        // TODO (Sơn)
        return false;
    }

    /** Hủy đơn của chính người đó, trả bản sao về san_co. */
    public static function huy(int $datChoId, int $nguoiDungId): bool
    {
        // TODO (Sơn)
        return false;
    }

    /** Tự hủy mọi đơn quá hạn giữ chỗ (> GIU_CHO_GIO) và giải phóng bản sao. Trả về số đơn đã hủy. */
    public static function huyQuaHan(): int
    {
        // TODO (Sơn)
        return 0;
    }
}
