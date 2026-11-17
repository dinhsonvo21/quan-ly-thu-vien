<?php
declare(strict_types=1);

namespace Loi;

/** Định dạng hiển thị: tiền, ngày, mã phiếu. */
final class DinhDang
{
    /** 6000 -> "6.000 đ" */
    public static function tien(int|float $soTien): string
    {
        return number_format((float) $soTien, 0, ',', '.') . ' đ';
    }

    /** '2026-10-27' hoặc '2026-10-27 09:00:00' -> '27/10/2026' */
    public static function ngay(?string $ngay, string $dang = 'd/m/Y'): string
    {
        if ($ngay === null || $ngay === '') {
            return '—';
        }
        return (new \DateTimeImmutable($ngay))->format($dang);
    }

    public static function ngayGio(?string $ngay): string
    {
        return self::ngay($ngay, 'd/m/Y · H:i');
    }

    public static function maPhieuMuon(int $id): string
    {
        return 'PM-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    public static function maDatCho(int $id): string
    {
        return 'DC-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    /** Thời gian còn lại tới $hetHan dạng HH:MM:SS (giá trị ban đầu; JS dem-nguoc.js chạy tiếp). */
    public static function conLai(string $hetHan): string
    {
        $giay = max(0, (new \DateTimeImmutable($hetHan))->getTimestamp() - time());
        return sprintf('%02d:%02d:%02d', intdiv($giay, 3600), intdiv($giay % 3600, 60), $giay % 60);
    }
}
