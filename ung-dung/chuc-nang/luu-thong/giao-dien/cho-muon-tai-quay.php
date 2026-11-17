<?php
$ma = 'SR-08'; $ten = 'Đặt chỗ & cho mượn tại quầy (Check-out)'; $nguoi = 'Nguyễn Đình Quân';
$viec = [
    '(1) Bảng "Đặt chỗ trực tuyến": mã, sinh viên (tên + MSSV), sách, hết hạn, còn lại (data-het-han), ô tìm + lọc "Chờ nhận sách"',
    '(2) Nút "Giao sách" mỗi dòng -> POST /quan-tri/dat-cho/{id}/giao   (có sẵn $datCho từ DatCho::layDangCho)',
    '(3) Ô Mã sinh viên + "Tra cứu sinh viên" (gọi GET /quan-tri/muon/tra-cuu-sinh-vien?ma=... bằng fetch)',
    '(4) Thẻ điều kiện: họ tên, khoa, tình trạng tài khoản, phiếu đang mượn (x / ' . SO_SACH_TOI_DA . '), phí chưa thanh toán',
    '(5) Ô tìm & chọn sách  ·  (6) Ngày mượn + Hạn trả dự kiến (+' . HAN_MUON_NGAY . ' ngày, chỉ đọc)  ·  (7) "Xác nhận cho mượn"',
    'Lưu ý: bỏ "mã bản sao cụ thể" nếu không dùng book_copies; sidebar "Đặt chỗ" mở trang này',
];
require UNG_DUNG . '/khung-giao-dien/dang-xay-dung.php';
