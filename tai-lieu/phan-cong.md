# Phân công & hợp đồng giữa các phần

## Ai làm chức năng nào
| Chức năng (`ung-dung/chuc-nang/`) | Màn hình | Mục báo cáo | Người làm |
|---|---|---|---|
| `xac-thuc` | SR-02, SR-03, quên mật khẩu | 2.2.2, 4.2, 4.3 | Võ Đình Sơn |
| `tra-cuu` | SR-01, SR-04, Nội quy, Danh sách đọc | 2.2.1, 4.1, 4.4 | Võ Đình Sơn |
| `dat-cho` | (logic) popup SR-04, bảng SR-05/SR-08 | 2.2.4 | Võ Đình Sơn |
| `ho-so` | SR-05 | 2.2.2, 4.5 | Nguyễn Minh Trạng |
| `kho-sach` | SR-07 + Quản lý thể loại | 2.2.3, 4.7 | Nguyễn Minh Trạng |
| `tong-quan` | SR-06 + gửi email + xuất báo cáo | 2.2.4, 4.6 | Nguyễn Minh Trạng |
| `luu-thong` | SR-08, SR-09 | 2.2.4, 4.8, 4.9 | Nguyễn Đình Quân |
| `tai-khoan` | SR-10 | 2.1, 4.10 | Nguyễn Đình Quân |
| `loi/`, `cau-hinh/`, `khung-giao-dien/`, `co-so-du-lieu/` | nền tảng | 3.x | Võ Đình Sơn |

## Hợp đồng (giữ nguyên chữ ký hàm; hiện là STUB trả dữ liệu giả để làm song song)
- `KhoSach\BanSao::layBanSanCo(int $sachId): ?array` — khóa 1 bản `san_co` (FOR UPDATE); `doiTrangThai()`, `demSanCo()`, `taoBanSao()`
- `DatCho\MoHinh::layDangCho()`, `layCuaNguoiDung()`, `danhDauDaGiao()`, `huy()`, `huyQuaHan()`, `tao()`
- `LuuThong\TinhPhiPhat::phiQuaHan($hanTra, $ngayTra)`, `phiBoiHoan($tinhTrang, $giaBia)` — **đã cài đặt**
- `PhienLamViec::nguoiDung()`, `PhanQuyen::yeuCauVaiTro()`, `CoSoDuLieu::giaoDich()` — **đã cài đặt**

## Thứ tự gợi ý
1. **Tuần 1:** Sơn `tao-bang.sql` + `du-lieu-mau.sql` + đăng ký/đăng nhập (mọi người cần đăng nhập thật để thử). Trạng `kho-sach` (model trước). Quân `luu-thong` (model + `TinhPhiPhat`). Song song: vẽ bổ sung các màn còn thiếu ảnh trong Figma.
2. **Tuần 2:** Sơn `tra-cuu` + `dat-cho`. Trạng `ho-so`. Quân giao diện SR-08/SR-09 + `tai-khoan`.
3. **Tuần 3:** Trạng `tong-quan` (cần dữ liệu phiếu của Quân) + email + xuất báo cáo. Sơn tác vụ nền + rà phân quyền.
4. **Tuần 4:** test chéo từng màn theo bảng "Những thành phần chính" trong báo cáo, thử 2 người mượn cuốn cuối cùng cùng lúc, hoàn thiện báo cáo.
