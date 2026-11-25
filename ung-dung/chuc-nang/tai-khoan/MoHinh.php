<?php
declare(strict_types=1);

namespace ChucNang\TaiKhoan;

use Loi\CoSoDuLieu;

/** Bảng `users` ở góc nhìn quản trị. ID hiển thị (SV2024001 / TT-001 / QT-001) là cột `ma_nguoi_dung`. */
final class MoHinh
{
    public const SO_DONG_MOI_TRANG = 7;
    public const VAI_TRO = [VAI_TRO_BAN_DOC, VAI_TRO_THU_THU, VAI_TRO_QUAN_TRI];
    public const TRANG_THAI = ['hoat_dong', 'tam_khoa'];

    /** @return array{tong:int, ban_doc:int, thu_thu:int, quan_tri:int} 4 thẻ thống kê đầu trang */
    public static function thongKe(): array
    {
        $r = CoSoDuLieu::truyVan(
            "SELECT COUNT(*) AS tong,
                    COALESCE(SUM(vai_tro = 'ban_doc'), 0)  AS ban_doc,
                    COALESCE(SUM(vai_tro = 'thu_thu'), 0)  AS thu_thu,
                    COALESCE(SUM(vai_tro = 'quan_tri'), 0) AS quan_tri
             FROM users"
        )->fetch();
        return [
            'tong'     => (int) $r['tong'],
            'ban_doc'  => (int) $r['ban_doc'],
            'thu_thu'  => (int) $r['thu_thu'],
            'quan_tri' => (int) $r['quan_tri'],
        ];
    }

    /**
     * @param array{tu_khoa?:string, vai_tro?:string, trang_thai?:string} $boLoc
     * @return array{danh_sach: array<int, array<string, mixed>>, tong: int, trang: int, so_trang: int}
     */
    public static function timKiem(array $boLoc, int $trang): array
    {
        $dieuKien = ['1 = 1'];
        $tham = [];

        $tuKhoa = trim((string) ($boLoc['tu_khoa'] ?? ''));
        if ($tuKhoa !== '') {
            $mau = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $tuKhoa) . '%';
            $dieuKien[] = "(ma_nguoi_dung LIKE :k1 OR CONCAT(ho_dem, ' ', ten) LIKE :k2 OR email LIKE :k3)";
            $tham += ['k1' => $mau, 'k2' => $mau, 'k3' => $mau];
        }
        if (in_array($boLoc['vai_tro'] ?? '', self::VAI_TRO, true)) {
            $dieuKien[] = 'vai_tro = :vt';
            $tham['vt'] = $boLoc['vai_tro'];
        }
        if (in_array($boLoc['trang_thai'] ?? '', self::TRANG_THAI, true)) {
            $dieuKien[] = 'trang_thai = :tt';
            $tham['tt'] = $boLoc['trang_thai'];
        }
        $where = implode(' AND ', $dieuKien);

        $tong = (int) CoSoDuLieu::truyVan("SELECT COUNT(*) FROM users WHERE $where", $tham)->fetchColumn();
        $soTrang = max(1, (int) ceil($tong / self::SO_DONG_MOI_TRANG));
        $trang = min(max(1, $trang), $soTrang);
        $viTri = ($trang - 1) * self::SO_DONG_MOI_TRANG;

        // Sinh viên -> Thủ thư -> Quản trị viên, rồi theo mã (khớp thứ tự trên thiết kế).
        $ds = CoSoDuLieu::truyVan(
            "SELECT id, ma_nguoi_dung, CONCAT(ho_dem, ' ', ten) AS ho_ten, email, vai_tro, trang_thai
             FROM users WHERE $where
             ORDER BY FIELD(vai_tro, 'ban_doc', 'thu_thu', 'quan_tri'), ma_nguoi_dung IS NULL, ma_nguoi_dung, id
             LIMIT " . self::SO_DONG_MOI_TRANG . " OFFSET $viTri",
            $tham
        )->fetchAll();

        return ['danh_sach' => $ds, 'tong' => $tong, 'trang' => $trang, 'so_trang' => $soTrang];
    }

    /** @return array<string, mixed>|null */
    public static function timTheoId(int $id): ?array
    {
        $r = CoSoDuLieu::truyVan(
            "SELECT id, ma_nguoi_dung, CONCAT(ho_dem, ' ', ten) AS ho_ten, email, vai_tro, trang_thai
             FROM users WHERE id = ?",
            [$id]
        )->fetch();
        return $r === false ? null : $r;
    }

    public static function emailDaDung(string $email): bool
    {
        return (bool) CoSoDuLieu::truyVan('SELECT 1 FROM users WHERE email = ? LIMIT 1', [$email])->fetchColumn();
    }

    /** Số Quản trị viên đang hoạt động (để không cho hạ quyền/khóa người cuối cùng). */
    public static function demQuanTriHoatDong(): int
    {
        return (int) CoSoDuLieu::truyVan(
            "SELECT COUNT(*) FROM users WHERE vai_tro = 'quan_tri' AND trang_thai = 'hoat_dong'"
        )->fetchColumn();
    }

    /**
     * Tạo tài khoản Thủ thư, mã TT-001, TT-002... sinh tự động. Trả về id mới.
     * Họ tên được tách: từ cuối là `ten`, phần còn lại là `ho_dem`.
     * @throws \PDOException nếu trùng email (mã lỗi 23000) — controller bắt và báo lại.
     */
    public static function taoThuThu(string $hoTen, string $email, string $matKhauBam): int
    {
        $tu = preg_split('/\s+/u', trim($hoTen), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $ten = (string) array_pop($tu);
        $hoDem = implode(' ', $tu);

        return (int) CoSoDuLieu::giaoDich(static function (\PDO $pdo) use ($hoDem, $ten, $email, $matKhauBam): int {
            $so = (int) $pdo->query(
                "SELECT COALESCE(MAX(CAST(SUBSTRING(ma_nguoi_dung, 4) AS UNSIGNED)), 0)
                 FROM users WHERE ma_nguoi_dung REGEXP '^TT-[0-9]+$' FOR UPDATE"
            )->fetchColumn();
            $ma = sprintf('TT-%03d', $so + 1);

            $cau = $pdo->prepare(
                "INSERT INTO users (ma_nguoi_dung, ho_dem, ten, email, mat_khau_bam, vai_tro, trang_thai)
                 VALUES (:ma, :ho_dem, :ten, :email, :mk, 'thu_thu', 'hoat_dong')"
            );
            $cau->execute(['ma' => $ma, 'ho_dem' => $hoDem, 'ten' => $ten, 'email' => $email, 'mk' => $matKhauBam]);
            return (int) $pdo->lastInsertId();
        });
    }

    public static function doiVaiTro(int $id, string $vaiTro): bool
    {
        if (!in_array($vaiTro, self::VAI_TRO, true)) {
            return false;
        }
        return CoSoDuLieu::truyVan('UPDATE users SET vai_tro = ? WHERE id = ?', [$vaiTro, $id])->rowCount() > 0;
    }

    /** $trangThai: 'hoat_dong' | 'tam_khoa' */
    public static function doiTrangThai(int $id, string $trangThai): bool
    {
        if (!in_array($trangThai, self::TRANG_THAI, true)) {
            return false;
        }
        return CoSoDuLieu::truyVan('UPDATE users SET trang_thai = ? WHERE id = ?', [$trangThai, $id])->rowCount() > 0;
    }
}
