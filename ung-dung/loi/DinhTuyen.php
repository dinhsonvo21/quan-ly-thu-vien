<?php
declare(strict_types=1);

namespace Loi;

/**
 * Router. Mỗi chức năng khai báo URL của mình trong chuc-nang/<ten>/route.php
 * (router tự nạp), nên không ai phải sửa một file route chung.
 *
 *   $r->get('/sach/{id}', [DieuKhien::class, 'chiTiet']);                        // công khai
 *   $r->post('/ho-so/cap-nhat', [DieuKhien::class, 'capNhat'], PhanQuyen::BAT_KY);
 *   $r->get('/quan-tri', [DieuKhien::class, 'tongQuan'], PhanQuyen::NHAN_VIEN);
 *
 * Tham số {id} được truyền vào hàm theo THỨ TỰ xuất hiện (dạng chuỗi).
 * Mọi request POST tự động bị kiểm tra CSRF.
 */
final class DinhTuyen
{
    private static string $goc = '';
    private static string $duongDan = '/';

    /** @var array<int, array{pt:string, mau:string, xuLy:array{0:class-string,1:string}, vaiTro:?array<int,string>}> */
    private array $tuyen = [];

    /** Đường dẫn gốc của ứng dụng trên URL (rỗng nếu chạy ở gốc domain). */
    public static function goc(): string
    {
        return self::$goc;
    }

    /** Đường dẫn của request hiện tại, đã bỏ phần gốc. Ví dụ '/quan-tri/kho-sach'. */
    public static function duongDanHienTai(): string
    {
        return self::$duongDan;
    }

    /** @param array{0:class-string,1:string} $xuLy @param ?array<int,string> $vaiTro null = công khai */
    public function get(string $duongDan, array $xuLy, ?array $vaiTro = null): void
    {
        $this->them('GET', $duongDan, $xuLy, $vaiTro);
    }

    /** @param array{0:class-string,1:string} $xuLy @param ?array<int,string> $vaiTro */
    public function post(string $duongDan, array $xuLy, ?array $vaiTro = null): void
    {
        $this->them('POST', $duongDan, $xuLy, $vaiTro);
    }

    public function chay(): void
    {
        PhienLamViec::batDau();
        $this->napRoute();
        self::$duongDan = self::tinhDuongDan();
        $phuongThuc = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        foreach ($this->tuyen as $t) {
            if ($t['pt'] !== $phuongThuc || !preg_match($t['mau'], self::$duongDan, $kq)) {
                continue;
            }
            if ($phuongThuc === 'POST' && !PhienLamViec::kiemTraCsrf()) {
                TrangLoi::hien(403, 'Phiên làm việc đã hết hạn. Hãy tải lại trang rồi thử lại.');
            }
            if ($t['vaiTro'] !== null && !PhanQuyen::choPhep($t['vaiTro'])) {
                PhanQuyen::tuChoi();
            }
            $thamSo = [];
            foreach ($kq as $khoa => $giaTri) {
                if (is_string($khoa)) {
                    $thamSo[] = $giaTri;
                }
            }
            [$lop, $ham] = $t['xuLy'];
            (new $lop())->$ham(...$thamSo);
            return;
        }
        TrangLoi::hien(404);
    }

    /** @param array{0:class-string,1:string} $xuLy @param ?array<int,string> $vaiTro */
    private function them(string $pt, string $duongDan, array $xuLy, ?array $vaiTro): void
    {
        $mau = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $duongDan) . '$#';
        $this->tuyen[] = ['pt' => $pt, 'mau' => $mau, 'xuLy' => $xuLy, 'vaiTro' => $vaiTro];
    }

    private function napRoute(): void
    {
        foreach (glob(UNG_DUNG . '/chuc-nang/*/route.php') ?: [] as $tep) {
            $khaiBao = require $tep;
            $khaiBao($this);
        }
    }

    /**
     * Hỗ trợ 3 cách chạy: vhost trỏ vào public/, hoặc http://localhost/quan-ly-thu-vien/
     * (qua .htaccess ở thư mục gốc), hoặc http://localhost/quan-ly-thu-vien/public/.
     */
    private static function tinhDuongDan(): string
    {
        $uri = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
        $goc1 = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        $goc2 = (string) preg_replace('#/public$#', '', $goc1);
        $batDauVoi = static fn (string $u, string $b): bool => $b !== '' && ($u === $b || str_starts_with($u, $b . '/'));

        if ($batDauVoi($uri, $goc1)) {
            self::$goc = $goc1;
        } elseif ($batDauVoi($uri, $goc2)) {
            self::$goc = $goc2;
        } else {
            self::$goc = '';
        }
        $duongDan = substr($uri, strlen(self::$goc));
        $duongDan = '/' . trim($duongDan, '/');
        return $duongDan;
    }
}
