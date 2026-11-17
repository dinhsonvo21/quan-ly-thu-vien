<?php
$ma = 'SR-09'; $ten = 'Nhận trả sách & xử lý phạt quá hạn (Check-in)'; $nguoi = 'Nguyễn Đình Quân';
$viec = [
    '(1) Bảng phiếu đang mượn (ô tìm, lọc trạng thái, cột Hạn trả / Quá hạn) + nút "Trả sách" mỗi dòng; tab "Cho mượn tại quầy" dẫn sang /quan-tri/dat-cho',
    '(2) Popup "Trả sách & xử lý phí phạt": thông tin phiếu + sách',
    '(3) Hạn trả (khóa) + Ngày trả thực tế (mặc định hôm nay, chặn ngày tương lai)',
    '(4) Khung tính phạt: số ngày quá hạn × ' . tien(PHI_PHAT_MOI_NGAY) . ' (JS: tinh-phi-phat.js; SERVER tính lại bằng TinhPhiPhat)',
    '(5) Tình trạng sách: Bình thường / Hư hỏng / Mất  ·  (6) Tổng cần thu  ·  (7) "Hoàn tất trả sách & thu phí"',
    'Cần thiết kế thêm: popup khi chọn Hư hỏng / Mất',
];
require UNG_DUNG . '/khung-giao-dien/dang-xay-dung.php';
