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

    /**
     * Phiếu đang mượn, có lọc theo từ khóa (mã phiếu, tên/MSSV, tên sách, mã bản sao) và trạng thái
     * ($trangThai: null|'tat-ca' | 'dang-muon' (còn hạn) | 'qua-han') — bảng nền SR-09.
     * @return array<int, array{id:int, ma_phieu:string, sinh_vien:string, ma_sv:string, ten_sach:string, tac_gia:string, ma_ban_sao:string, gia_bia:int, ngay_muon:string, han_tra:string}>
     */
    public static function layDangMuon(?string $tuKhoa = null, ?string $trangThai = null): array
    {
        // STUB: dữ liệu giả để dựng giao diện — thay bằng SQL thật (JOIN users, book_copies, books).
        $ngay = static fn (int $lech): string => date('Y-m-d', strtotime(($lech >= 0 ? '+' : '') . $lech . ' days'));
        $tatCa = [
            ['id' => 2064, 'ma_phieu' => 'PM-2064', 'sinh_vien' => 'Alex Morgan',     'ma_sv' => 'SV2024001', 'ten_sach' => 'The Psychology of Money', 'tac_gia' => 'Morgan Housel', 'ma_ban_sao' => 'BK-003-02', 'gia_bia' => 150000, 'ngay_muon' => $ngay(-17), 'han_tra' => $ngay(-3)],
            ['id' => 2081, 'ma_phieu' => 'PM-2081', 'sinh_vien' => 'Alex Morgan',     'ma_sv' => 'SV2024001', 'ten_sach' => 'Atomic Habits',           'tac_gia' => 'James Clear',   'ma_ban_sao' => 'BK-002-05', 'gia_bia' => 180000, 'ngay_muon' => $ngay(-2),  'han_tra' => $ngay(12)],
            ['id' => 2050, 'ma_phieu' => 'PM-2050', 'sinh_vien' => 'Nguyễn Minh Anh', 'ma_sv' => 'SV2024012', 'ten_sach' => 'Sapiens',                'tac_gia' => 'Yuval Noah Harari', 'ma_ban_sao' => 'BK-007-01', 'gia_bia' => 220000, 'ngay_muon' => $ngay(-19), 'han_tra' => $ngay(-5)],
            ['id' => 2090, 'ma_phieu' => 'PM-2090', 'sinh_vien' => 'Lê Minh Khoa',    'ma_sv' => 'SV2024025', 'ten_sach' => 'The Midnight Library',    'tac_gia' => 'Matt Haig',     'ma_ban_sao' => 'BK-011-03', 'gia_bia' => 160000, 'ngay_muon' => $ngay(-1),  'han_tra' => $ngay(13)],
        ];
        $homNay = date('Y-m-d');
        return array_values(array_filter($tatCa, static function (array $p) use ($tuKhoa, $trangThai, $homNay): bool {
            $quaHan = $p['han_tra'] < $homNay;
            if ($trangThai === 'qua-han' && !$quaHan) { return false; }
            if ($trangThai === 'dang-muon' && $quaHan) { return false; }
            $k = trim((string) $tuKhoa);
            return $k === '' || mb_stripos(implode(' ', [$p['ma_phieu'], $p['sinh_vien'], $p['ma_sv'], $p['ten_sach'], $p['ma_ban_sao']]), $k) !== false;
        }));
    }

    public static function danhDauDaTra(int $phieuId, string $ngayTra): bool
    {
        // TODO (Quân): chặn ngày trả trong tương lai hoặc trước ngày mượn.
        return false;
    }
}
