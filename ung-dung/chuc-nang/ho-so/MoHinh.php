<?php
declare(strict_types=1);

namespace ChucNang\HoSo;

use Loi\CoSoDuLieu;

final class MoHinh
{
    /** @return array<string, mixed>|null */
    public static function layHoSo(int $nguoiDungId): ?array
    {
        // TODO (Trạng)
        return null;
    }

    /** @param array<string, string> $duLieu ho_ten, email, sdt, khoa_lop */
    public static function capNhat(int $nguoiDungId, array $duLieu): bool
    {
        // TODO (Trạng)
        return false;
    }

    /** @return array<int, array<string, mixed>> phiếu mượn của bạn đọc: mã, sách, ngày mượn, hạn trả, trạng thái. */
    public static function layLichSuMuon(int $nguoiDungId): array
    {
        // TODO (Trạng)
        return [];
    }

    /** Gia hạn thêm GIA_HAN_NGAY ngày nếu đủ điều kiện; trả về false kèm lý do qua ngoại lệ hoặc mã lỗi tùy bạn chọn. */
    public static function giaHan(int $phieuMuonId, int $nguoiDungId): bool
    {
        // TODO (Trạng)
        return false;
    }
}
