# QNU Library — Hệ thống Quản lý và Tra cứu Sách trực tuyến

Đồ án THLVN2B — Nhóm 3. PHP (MVC tự viết, chia theo chức năng) + MySQL (PDO).
Thành viên: Võ Đình Sơn · Nguyễn Minh Trạng · Nguyễn Đình Quân.

## Yêu cầu
PHP ≥ 8.1 (bật `pdo_mysql`, `fileinfo`, `mbstring`), MySQL/MariaDB, Apache có `mod_rewrite` (Laragon hoặc XAMPP).

## Chạy trên máy mình
1. `git clone <link-repo>` vào thư mục web (`C:\laragon\www\` hoặc `htdocs\`), thư mục tên `quan-ly-thu-vien`.
2. Sao chép `ung-dung/cau-hinh/csdl.mau.php` thành `csdl.php` rồi sửa thông số MySQL (file này không bị commit).
3. Tạo CSDL: chạy `co-so-du-lieu/tao-bang.sql` rồi `du-lieu-mau.sql` (và các file trong `thay-doi/` theo thứ tự nếu có).
4. Mở `http://localhost/quan-ly-thu-vien/` (Laragon: `http://quan-ly-thu-vien.test`).
5. Chưa có đăng nhập thật? Vào `/dang-nhap`, dùng nút **"Đăng nhập nhanh"** (chế độ phát triển) để xem giao diện theo từng vai trò.

## Cấu trúc (chia theo chức năng — mỗi thư mục có đủ route + controller + model + giao diện)
```
public/                  chỉ thư mục này mở ra web (index.php, css, js, tep-tai-len)
ung-dung/
  cau-hinh/              quy-dinh.php (14 ngày, +7, 5 cuốn, 2.000đ, 24h), ung-dung.php, csdl.mau.php
  loi/                   router, session/CSRF, phân quyền, PDO, kiểm tra dữ liệu, tải ảnh  (Sơn)
  chuc-nang/<ten>/       route.php · DieuKhien.php · (các) MoHinh · giao-dien/
  khung-giao-dien/       layout người dùng + layout quản trị + khung "đang xây dựng"
  tac-vu-nen/            chạy bằng Task Scheduler/cron: hủy đặt chỗ quá 24h, nhắc trả sách
co-so-du-lieu/           tao-bang.sql, du-lieu-mau.sql, thay-doi/
tai-lieu/                báo cáo, ERD, ảnh thiết kế, phan-cong.md
```

## Làm thế nào để thêm một màn hình?
1. Khai báo URL + quyền trong `route.php` của chức năng.
2. Viết hàm trong `DieuKhien.php`, gọi `$this->hienThi('ten-file', [...], 'nguoi-dung' | 'quan-tri', 'Tiêu đề')`.
3. Viết giao diện trong `giao-dien/ten-file.php` (xóa lời gọi `dang-xay-dung.php`); dữ liệu ra HTML qua `e()`.

Đọc thêm: [CONTRIBUTING.md](CONTRIBUTING.md) (quy ước git) · [tai-lieu/phan-cong.md](tai-lieu/phan-cong.md) (ai làm gì).

## Tác vụ nền (hủy đặt chỗ quá 24h)
Chạy `php ung-dung/tac-vu-nen/huy-dat-cho-qua-han.php` định kỳ mỗi 5–10 phút.
- Windows: Task Scheduler → Action: `php.exe` + đối số là đường dẫn file trên.
- Linux: crontab, dòng `*/5 * * * * php /duong-dan/ung-dung/tac-vu-nen/huy-dat-cho-qua-han.php`
