<?php
$ma = 'SR-05'; $ten = 'Tài khoản của tôi & Lịch sử mượn - trả'; $nguoi = 'Nguyễn Minh Trạng';
$viec = [
    '(1) Tabs: Thông tin cá nhân · Đặt chỗ đang hoạt động · Lịch sử mượn (id="lich-su") · Đổi mật khẩu (id="doi-mat-khau")',
    '(2) Form hồ sơ: Mã sinh viên (khóa), Họ tên, Email, SĐT, Khoa/Lớp + "Lưu thay đổi"',
    '(3) Bảng đặt chỗ: mã, sách, nơi nhận, hết hạn, đồng hồ đếm ngược (data-het-han), "Hủy đặt chỗ" -> POST /dat-cho/{id}/huy',
    '(4) Bảng lịch sử mượn + (5) "Gia hạn (+' . GIA_HAN_NGAY . ' ngày)"; phiếu quá hạn hiện "Không thể gia hạn"',
    '(6) Form đổi mật khẩu (hiện tại / mới / xác nhận)',
    'Có sẵn $datCho (từ chức năng dat-cho) và $lichSu để in ra bảng',
];
require UNG_DUNG . '/khung-giao-dien/dang-xay-dung.php';
