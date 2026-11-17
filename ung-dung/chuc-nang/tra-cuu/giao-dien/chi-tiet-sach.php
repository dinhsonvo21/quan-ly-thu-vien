<?php
$ma = 'SR-04'; $ten = 'Chi tiết sách & đặt trước (sách #' . (int) ($id ?? 0) . ')'; $nguoi = 'Võ Đình Sơn';
$viec = [
    '(1) Ảnh bìa + nút "Thêm vào danh sách đọc"  ·  (2) Tên, tác giả, thể loại, giới thiệu, NXB / năm / ngôn ngữ / ISBN',
    '(3) Bảng tình trạng lưu trữ: số bản sẵn có + vị trí kệ (chỉ Thư viện trung tâm)',
    '(4) Thẻ "Mượn sách này": số bản khả dụng, thời hạn mượn ' . HAN_MUON_NGAY . ' ngày (SỬA lỗi 21 ngày trong ảnh), phí đặt trước: miễn phí',
    '(5) Nút "Đặt trước sách" -> (6) popup: (7) chọn nơi nhận, (8) "Xác nhận đặt trước" / "Hủy" -> POST /dat-cho',
    'Cần thiết kế thêm: bản không popup, trạng thái hết sách, trạng thái khách chưa đăng nhập',
];
require UNG_DUNG . '/khung-giao-dien/dang-xay-dung.php';
