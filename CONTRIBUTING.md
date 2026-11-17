# Quy ước làm việc nhóm

## Nhánh
- `main`: bản ổn định để nộp. **Không push trực tiếp.**
- `develop`: nhánh tích hợp. **Không push trực tiếp**, chỉ nhận Pull Request.
- `feature/<chuc-nang>-<viec>` (vd `feature/kho-sach-popup-sua`): mỗi việc một nhánh, tách từ `develop`.

## Quy trình mỗi việc
1. `git checkout develop && git pull`
2. `git checkout -b feature/kho-sach-popup-sua`
3. Code, commit nhỏ: `feat(kho-sach): popup thêm/sửa sách` (các loại: `feat`, `fix`, `docs`, `refactor`, `db`)
4. `git push -u origin feature/...` rồi mở Pull Request vào `develop`, điền checklist trong mẫu PR.
5. Một người khác review (Sơn xem Trạng · Trạng xem Quân · Quân xem Sơn), sửa xong mới merge.
6. Cuối tuần cả nhóm chạy thử `develop` rồi mới merge vào `main`.

## Mỗi file có một chủ
Chỉ sửa thư mục `chuc-nang/` của mình. Cần sửa của người khác → mở PR cho chủ đó review.
- `ung-dung/loi/`, `cau-hinh/`, `khung-giao-dien/`, `co-so-du-lieu/tao-bang.sql`: **Sơn**.
- Đổi schema: nhắn Sơn, thêm file vào `co-so-du-lieu/thay-doi/` để mọi người chạy lại trên máy mình.
- Đổi chữ ký hàm "hợp đồng" (xem `tai-lieu/phan-cong.md`): báo cả nhóm trước.

## Quy tắc code
1. Mọi request qua `public/index.php`; quyền khai báo ở `route.php` theo **vai trò** (`PhanQuyen::NHAN_VIEN`, `CHI_QUAN_TRI`, `BAT_KY`).
2. SQL chỉ viết trong file model, luôn prepared statement. Controller không viết SQL.
3. Đổi trạng thái bản sao / phiếu mượn / đặt chỗ → trong `CoSoDuLieu::giaoDich()` + `SELECT ... FOR UPDATE`.
4. Mọi con số nghiệp vụ (14, 7, 5, 2.000đ, 24h) lấy từ `ung-dung/cau-hinh/quy-dinh.php`.
5. Dữ liệu ra HTML luôn qua `e()`; form POST luôn có `<?= csrf_field() ?>`.
6. Tên: thư mục + file giao diện `kebab-case`; file chứa class `PascalCase`; cột CSDL `snake_case` không dấu.
7. Không commit `ung-dung/cau-hinh/csdl.php` (có mật khẩu). Nếu lỡ commit → đổi mật khẩu DB.
8. Trước khi nộp: đặt `che_do_go_loi => false` và xóa route `/dev/dang-nhap-nhanh`.
