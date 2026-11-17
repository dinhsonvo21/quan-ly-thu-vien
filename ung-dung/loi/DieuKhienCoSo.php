<?php
declare(strict_types=1);

namespace Loi;

/**
 * Lớp cha của mọi controller (DieuKhien.php trong từng chức năng).
 *
 * hienThi('ten-file') tự tìm  chuc-nang/<chuc-nang-cua-controller>/giao-dien/ten-file.php
 * rồi bọc trong khung ('nguoi-dung' hoặc 'quan-tri').
 */
abstract class DieuKhienCoSo
{
    /** @param array<string, mixed> $duLieu */
    protected function hienThi(string $giaoDien, array $duLieu = [], string $khung = 'nguoi-dung', string $tieuDe = ''): void
    {
        $thuMuc = dirname((string) (new \ReflectionClass($this))->getFileName());
        $tep = $thuMuc . '/giao-dien/' . $giaoDien . '.php';
        if (!is_file($tep)) {
            throw new \RuntimeException("Không tìm thấy giao diện: {$giaoDien}");
        }
        $noiDung = self::ve($tep, $duLieu);
        $nguoiDung = PhienLamViec::nguoiDung();
        require UNG_DUNG . '/khung-giao-dien/' . $khung . '.php';
    }

    /** @param array<string, mixed> $duLieu */
    private static function ve(string $tep, array $duLieu): string
    {
        extract($duLieu, EXTR_SKIP);
        ob_start();
        require $tep;
        return (string) ob_get_clean();
    }

    protected function chuyenHuong(string $duongDan): never
    {
        header('Location: ' . url($duongDan));
        exit;
    }

    protected function flash(string $loai, string $noiDung): void
    {
        PhienLamViec::flash($loai, $noiDung);
    }

    /** @param array<string, mixed> $duLieu */
    protected function json(array $duLieu, int $ma = 200): never
    {
        http_response_code($ma);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($duLieu, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Dùng cho hàm xử lý POST chưa viết: báo "chưa làm" rồi quay lại. Xóa khi cài đặt thật. */
    protected function chuaLam(string $viec, string $quayVe): never
    {
        PhienLamViec::flash('thong_tin', "Chức năng đang xây dựng: {$viec}");
        $this->chuyenHuong($quayVe);
    }
}
