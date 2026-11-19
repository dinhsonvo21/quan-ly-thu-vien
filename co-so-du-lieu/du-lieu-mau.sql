-- =====================================================================
-- DỮ LIỆU MẪU — QNU Library
-- Chạy SAU tao-bang.sql.  Một bộ nhân vật thống nhất cho mọi màn hình SR.
--
--  * MẬT KHẨU của MỌI tài khoản mẫu:  thuvien2026
--  * Ngày giờ tính TƯƠNG ĐỐI theo hôm nay (CURDATE()/NOW()) nên khi demo
--    luôn có: phiếu quá hạn 3/5/7/8 ngày, đơn giữ chỗ còn ~23h / ~7h / ~4h.
--  * Mã hiển thị khớp ảnh SR: PM-2064 = id 2064, DC-1042 = id 1042 (xem DinhDang::maPhieuMuon).
--  * Số liệu tổng (12.480 bản, 326 đang mượn...) trong ảnh chỉ là minh họa;
--    dữ liệu thật tính từ các bảng này.
-- =====================================================================
SET NAMES utf8mb4;
USE quan_ly_thu_vien;

-- ---------------------------------------------------------------------
-- Người dùng: 5 bạn đọc, 2 thủ thư, 1 quản trị viên
-- ---------------------------------------------------------------------
INSERT INTO users (ma_nguoi_dung, ho_dem, ten, email, mat_khau_bam, sdt, khoa_lop, vai_tro, trang_thai) VALUES
('SV2024001', 'Nguyễn Trần', 'Thị',    'nguyenthi@example.com',  '$2y$10$NTre.xyy3S/uv2SNp1UwEeHyasgi203m2nEoXc1oOGj5YUpM82sZS', '0912345678', 'Công nghệ thông tin / CNTT K47', 'ban_doc',  'hoat_dong'),
('SV2024012', 'Nguyễn Minh', 'Anh',    'minhanh.24@qnu.edu.vn',  '$2y$10$NTre.xyy3S/uv2SNp1UwEeHyasgi203m2nEoXc1oOGj5YUpM82sZS', '0905111222', 'Công nghệ thông tin / CNTT K47', 'ban_doc',  'hoat_dong'),
('SV2024025', 'Lê Minh',     'Khoa',   'minhkhoa.24@qnu.edu.vn', '$2y$10$NTre.xyy3S/uv2SNp1UwEeHyasgi203m2nEoXc1oOGj5YUpM82sZS', '0905333444', 'Công nghệ thông tin',            'ban_doc',  'hoat_dong'),
('SV2024030', 'Lê Thu',      'Hà',     'thuha.24@qnu.edu.vn',    '$2y$10$NTre.xyy3S/uv2SNp1UwEeHyasgi203m2nEoXc1oOGj5YUpM82sZS', '0905555666', 'Sư phạm Ngữ văn',                'ban_doc',  'hoat_dong'),
('SV2024038', 'Trần Hoàng',  'Nam',    'hoangnam.24@qnu.edu.vn', '$2y$10$NTre.xyy3S/uv2SNp1UwEeHyasgi203m2nEoXc1oOGj5YUpM82sZS', '0905777888', 'Quản trị kinh doanh',            'ban_doc',  'tam_khoa'),
('TT-001',    'Nguyễn Minh', 'Trạng',  'trang.nm@qnu.edu.vn',    '$2y$10$NTre.xyy3S/uv2SNp1UwEeHyasgi203m2nEoXc1oOGj5YUpM82sZS', NULL,         NULL,                             'thu_thu',  'hoat_dong'),
('TT-002',    'Nguyễn Đình', 'Quân',   'quan.nd@qnu.edu.vn',     '$2y$10$NTre.xyy3S/uv2SNp1UwEeHyasgi203m2nEoXc1oOGj5YUpM82sZS', NULL,         NULL,                             'thu_thu',  'hoat_dong'),
('QT-001',    'Võ Đình',     'Sơn',    'admin.son@qnu.edu.vn',   '$2y$10$NTre.xyy3S/uv2SNp1UwEeHyasgi203m2nEoXc1oOGj5YUpM82sZS', NULL,         NULL,                             'quan_tri', 'hoat_dong');

-- ---------------------------------------------------------------------
-- Thể loại
-- ---------------------------------------------------------------------
INSERT INTO categories (ten_the_loai) VALUES
('Văn học'), ('Phát triển bản thân'), ('Tài chính'), ('Hồi ký'), ('Lịch sử'),
('Nghệ thuật'), ('Khoa học'), ('Giáo trình đại cương'), ('Toán - Tin học'), ('Ngoại ngữ');

-- ---------------------------------------------------------------------
-- Đầu sách (8 cuốn như trang chủ SR-01 + 3 giáo trình như SR-08)
-- ISBN chỉ có ở BK-001 (theo ảnh SR-07); các cuốn khác để trống, bổ sung khi cần.
-- ---------------------------------------------------------------------
INSERT INTO books (ma_sach, isbn, ten_sach, tac_gia, nha_xuat_ban, nam_xuat_ban, ngon_ngu, loai_tai_lieu, so_trang, mo_ta, gia_bia, vi_tri_ke, the_loai_id, ngay_tao)
SELECT b.ma_sach, b.isbn, b.ten_sach, b.tac_gia, b.nxb, b.nam, b.ngon_ngu, b.loai, b.so_trang, b.mo_ta, b.gia, b.ke, c.id, NOW() - INTERVAL b.ngay_truoc DAY
FROM (
  SELECT 'BK-001' AS ma_sach, '9780525559474' AS isbn, 'The Midnight Library' AS ten_sach, 'Matt Haig' AS tac_gia, 'Viking' AS nxb, 2020 AS nam, 'Tiếng Anh' AS ngon_ngu, 'Sách tham khảo' AS loai, 304 AS so_trang,
         'Nora Seed lạc vào một thư viện kỳ lạ, nơi mỗi cuốn sách là một cuộc đời khác mà cô có thể đã sống.' AS mo_ta, 180000 AS gia, 'F-HAI' AS ke, 'Văn học' AS the_loai, 1 AS ngay_truoc
  UNION ALL SELECT 'BK-002', NULL, 'Atomic Habits', 'James Clear', 'Avery', 2018, 'Tiếng Anh', 'Sách tham khảo', NULL,
         'Những thay đổi nhỏ về thói quen tạo nên kết quả lớn theo thời gian.', 190000, 'P-CLE', 'Phát triển bản thân', 2
  UNION ALL SELECT 'BK-003', NULL, 'The Psychology of Money', 'Morgan Housel', 'Harriman House', 2020, 'Tiếng Anh', 'Sách tham khảo', NULL,
         'Cách con người suy nghĩ và hành xử với tiền bạc quan trọng hơn kiến thức tài chính thuần túy.', 170000, 'B-HOU', 'Tài chính', 3
  UNION ALL SELECT 'BK-004', NULL, 'Where the Crawdads Sing', 'Delia Owens', 'G.P. Putnam''s Sons', 2018, 'Tiếng Anh', 'Sách tham khảo', NULL,
         'Cô gái lớn lên một mình giữa đầm lầy ven biển và vụ án làm đảo lộn cuộc sống của cô.', 175000, 'F-OWE', 'Văn học', 4
  UNION ALL SELECT 'BK-005', NULL, 'Educated', 'Tara Westover', 'Random House', 2018, 'Tiếng Anh', 'Sách tham khảo', NULL,
         'Hồi ký về hành trình từ một gia đình sống biệt lập trên núi đến giảng đường đại học.', 185000, 'M-WES', 'Hồi ký', 5
  UNION ALL SELECT 'BK-006', NULL, 'Sapiens', 'Yuval Noah Harari', 'Harper', 2015, 'Tiếng Anh', 'Sách tham khảo', NULL,
         'Lược sử loài người từ thời săn bắt hái lượm đến xã hội hiện đại.', 220000, 'H-HAR', 'Lịch sử', 6
  UNION ALL SELECT 'BK-007', NULL, 'The Creative Act', 'Rick Rubin', 'Penguin Press', 2023, 'Tiếng Anh', 'Sách tham khảo', NULL,
         'Cách nhìn về sáng tạo như một thực hành sống, không chỉ dành cho nghệ sĩ.', 250000, 'A-RUB', 'Nghệ thuật', 7
  UNION ALL SELECT 'BK-008', NULL, 'A Brief History of Time', 'Stephen Hawking', 'Bantam Books', 1988, 'Tiếng Anh', 'Sách tham khảo', NULL,
         'Giới thiệu về vũ trụ, thời gian và lỗ đen cho bạn đọc phổ thông.', 160000, 'S-HAW', 'Khoa học', 8
  UNION ALL SELECT 'BK-009', NULL, 'Triết học Mác - Lênin', 'Bộ Giáo dục và Đào tạo', 'NXB Chính trị quốc gia Sự thật', 2021, 'Tiếng Việt', 'Giáo trình', NULL,
         'Giáo trình môn Triết học Mác - Lênin dành cho sinh viên các ngành không chuyên lý luận.', 90000, 'GT-TRI', 'Giáo trình đại cương', 9
  UNION ALL SELECT 'BK-010', NULL, 'Toán rời rạc', 'Nhiều tác giả', 'NXB Giáo dục Việt Nam', 2019, 'Tiếng Việt', 'Giáo trình', NULL,
         'Giáo trình toán rời rạc cho sinh viên công nghệ thông tin.', 80000, 'GT-TOA', 'Toán - Tin học', 10
  UNION ALL SELECT 'BK-011', NULL, 'Tiếng Anh cơ bản', 'Nhiều tác giả', 'NXB Đại học Quốc gia', 2020, 'Tiếng Việt', 'Giáo trình', NULL,
         'Giáo trình tiếng Anh cơ bản học phần đại cương.', 70000, 'GT-ANH', 'Ngoại ngữ', 11
) b JOIN categories c ON c.ten_the_loai = b.the_loai;

-- ---------------------------------------------------------------------
-- Bản sao. Trạng thái khớp SR-07 / SR-08 / SR-09:
--   BK-001: 5 bản = 3 sẵn có + 1 đang mượn + 1 đang giữ chỗ   (popup SR-07)
--   BK-002: 8 bản, sẵn có 5                                    (SR-08 "Còn 5 / 8 bản")
--   BK-003: hết sách; PM-2064 mượn bản BK-003-02               (SR-09)
-- ---------------------------------------------------------------------
INSERT INTO book_copies (ma_ban_sao, sach_id, trang_thai)
SELECT x.ma_ban_sao, b.id, x.trang_thai
FROM (
  SELECT 'BK-001-01' AS ma_ban_sao, 'BK-001' AS ma_sach, 'san_co' AS trang_thai
  UNION ALL SELECT 'BK-001-02', 'BK-001', 'san_co'
  UNION ALL SELECT 'BK-001-03', 'BK-001', 'san_co'
  UNION ALL SELECT 'BK-001-04', 'BK-001', 'dang_muon'
  UNION ALL SELECT 'BK-001-05', 'BK-001', 'dang_giu'
  UNION ALL SELECT 'BK-002-01', 'BK-002', 'dang_muon'
  UNION ALL SELECT 'BK-002-02', 'BK-002', 'dang_giu'
  UNION ALL SELECT 'BK-002-03', 'BK-002', 'hong'
  UNION ALL SELECT 'BK-002-04', 'BK-002', 'san_co'
  UNION ALL SELECT 'BK-002-05', 'BK-002', 'san_co'
  UNION ALL SELECT 'BK-002-06', 'BK-002', 'san_co'
  UNION ALL SELECT 'BK-002-07', 'BK-002', 'san_co'
  UNION ALL SELECT 'BK-002-08', 'BK-002', 'san_co'
  UNION ALL SELECT 'BK-003-01', 'BK-003', 'mat'
  UNION ALL SELECT 'BK-003-02', 'BK-003', 'dang_muon'
  UNION ALL SELECT 'BK-004-01', 'BK-004', 'san_co'
  UNION ALL SELECT 'BK-004-02', 'BK-004', 'san_co'
  UNION ALL SELECT 'BK-004-03', 'BK-004', 'san_co'
  UNION ALL SELECT 'BK-004-04', 'BK-004', 'san_co'
  UNION ALL SELECT 'BK-005-01', 'BK-005', 'san_co'
  UNION ALL SELECT 'BK-005-02', 'BK-005', 'san_co'
  UNION ALL SELECT 'BK-005-03', 'BK-005', 'dang_muon'
  UNION ALL SELECT 'BK-006-01', 'BK-006', 'dang_muon'
  UNION ALL SELECT 'BK-007-01', 'BK-007', 'san_co'
  UNION ALL SELECT 'BK-007-02', 'BK-007', 'san_co'
  UNION ALL SELECT 'BK-007-03', 'BK-007', 'dang_giu'
  UNION ALL SELECT 'BK-008-01', 'BK-008', 'dang_muon'
  UNION ALL SELECT 'BK-008-02', 'BK-008', 'san_co'
  UNION ALL SELECT 'BK-009-01', 'BK-009', 'san_co'
  UNION ALL SELECT 'BK-009-02', 'BK-009', 'san_co'
  UNION ALL SELECT 'BK-009-03', 'BK-009', 'san_co'
  UNION ALL SELECT 'BK-009-04', 'BK-009', 'san_co'
  UNION ALL SELECT 'BK-010-01', 'BK-010', 'hong'
  UNION ALL SELECT 'BK-010-02', 'BK-010', 'san_co'
  UNION ALL SELECT 'BK-010-03', 'BK-010', 'san_co'
  UNION ALL SELECT 'BK-010-04', 'BK-010', 'san_co'
  UNION ALL SELECT 'BK-011-01', 'BK-011', 'san_co'
  UNION ALL SELECT 'BK-011-02', 'BK-011', 'san_co'
  UNION ALL SELECT 'BK-011-03', 'BK-011', 'san_co'
  UNION ALL SELECT 'BK-011-04', 'BK-011', 'san_co'
  UNION ALL SELECT 'BK-011-05', 'BK-011', 'san_co'
) x JOIN books b ON b.ma_sach = x.ma_sach;

-- ---------------------------------------------------------------------
-- Đặt chỗ (id = số trong mã DC-xxxx). 3 đơn đang chờ nhận + 3 đơn đã kết thúc.
-- Đơn đang chờ: còn ~23h (DC-1042), ~7h20 (DC-1038), ~4h (DC-1036) như SR-05/SR-08.
-- ---------------------------------------------------------------------
INSERT INTO reservations (id, nguoi_dung_id, ban_sao_id, noi_nhan, ngay_dat, het_han, trang_thai)
SELECT r.id, u.id, c.id, 'Thư viện trung tâm', r.ngay_dat, r.het_han, r.trang_thai
FROM (
  SELECT 1042 AS id, 'SV2024001' AS ma_sv, 'BK-001-05' AS ma_bs, NOW() - INTERVAL 1 HOUR   AS ngay_dat, NOW() + INTERVAL 23 HOUR  AS het_han, 'cho_nhan' AS trang_thai
  UNION ALL SELECT 1038, 'SV2024001', 'BK-007-03', NOW() - INTERVAL 1000 MINUTE, NOW() + INTERVAL 440 MINUTE, 'cho_nhan'
  UNION ALL SELECT 1036, 'SV2024012', 'BK-002-02', NOW() - INTERVAL 20 HOUR,     NOW() + INTERVAL 4 HOUR,     'cho_nhan'
  UNION ALL SELECT 1030, 'SV2024025', 'BK-001-04', NOW() - INTERVAL 5 DAY,       NOW() - INTERVAL 4 DAY,      'da_giao'
  UNION ALL SELECT 1025, 'SV2024012', 'BK-004-02', NOW() - INTERVAL 9 DAY,       NOW() - INTERVAL 8 DAY,      'da_huy'
  UNION ALL SELECT 1020, 'SV2024025', 'BK-009-01', NOW() - INTERVAL 12 DAY,      NOW() - INTERVAL 11 DAY,     'het_han'
) r
JOIN users u ON u.ma_nguoi_dung = r.ma_sv
JOIN book_copies c ON c.ma_ban_sao = r.ma_bs;

-- ---------------------------------------------------------------------
-- Phiếu mượn (id = số trong mã PM-xxxx). "Hôm nay" = CURDATE().
--   4 phiếu quá hạn 3/5/7/8 ngày  -> bảng cảnh báo SR-06 (6.000 / 10.000 / 14.000 / 16.000 đ)
--   PM-2081 còn hạn gia hạn được, PM-1980 đã trả đúng hạn (SR-05)
--   SV2024025 (Lê Minh Khoa): đúng 1 phiếu, không quá hạn, không nợ -> "1 / 5 cuốn" (SR-08)
-- ---------------------------------------------------------------------
INSERT INTO borrow_tickets (id, nguoi_dung_id, ban_sao_id, dat_cho_id, thu_thu_id, ngay_muon, han_tra, ngay_tra, so_lan_gia_han, trang_thai)
SELECT p.id, u.id, c.id, p.dat_cho_id, tt.id,
       CURDATE() - INTERVAL p.muon DAY, CURDATE() - INTERVAL p.han DAY,
       IF(p.tra IS NULL, NULL, CURDATE() - INTERVAL p.tra DAY), 0, IF(p.tra IS NULL, 'dang_muon', 'da_tra')
FROM (
  -- han/tra: số ngày LÙI so với hôm nay (âm = trong tương lai)
  SELECT 2081 AS id, 'SV2024001' AS ma_sv, 'BK-002-01' AS ma_bs, NULL AS dat_cho_id, 'TT-002' AS ma_tt, 7  AS muon,  -7 AS han, NULL AS tra
  UNION ALL SELECT 2064, 'SV2024001', 'BK-003-02', NULL, 'TT-002', 17,   3, NULL
  UNION ALL SELECT 1980, 'SV2024001', 'BK-005-01', NULL, 'TT-001', 26,  12, 13
  UNION ALL SELECT 2050, 'SV2024012', 'BK-006-01', NULL, 'TT-002', 19,   5, NULL
  UNION ALL SELECT 2041, 'SV2024038', 'BK-005-03', NULL, 'TT-001', 21,   7, NULL
  UNION ALL SELECT 2038, 'SV2024030', 'BK-008-01', NULL, 'TT-002', 22,   8, NULL
  UNION ALL SELECT 2077, 'SV2024025', 'BK-001-04', 1030, 'TT-002',  5,  -9, NULL
  UNION ALL SELECT 1975, 'SV2024012', 'BK-004-01', NULL, 'TT-001', 30,  16, 14
  UNION ALL SELECT 1968, 'SV2024038', 'BK-010-01', NULL, 'TT-002', 40,  26, 23
) p
JOIN users u ON u.ma_nguoi_dung = p.ma_sv
JOIN users tt ON tt.ma_nguoi_dung = p.ma_tt
JOIN book_copies c ON c.ma_ban_sao = p.ma_bs;

-- ---------------------------------------------------------------------
-- Phí phạt đã ghi nhận lúc trả sách
--   PM-1975: trả trễ 2 ngày = 4.000 đ, đã thu
--   PM-1968: trả trễ 3 ngày (6.000) + sách hỏng, bồi hoàn 50% giá bìa 80.000 = 40.000 -> 46.000 đ, CHƯA thu
--            (SV2024038 vừa nợ phạt vừa bị tạm khóa: dùng thử chặn mượn)
-- ---------------------------------------------------------------------
INSERT INTO fines (phieu_muon_id, so_ngay_tre, phi_tre, tinh_trang_sach, phi_boi_hoan, da_thu, ngay_ghi, ngay_thu) VALUES
(1975, 2, 4000, 'binh_thuong',     0, 1, NOW() - INTERVAL 14 DAY, NOW() - INTERVAL 14 DAY),
(1968, 3, 6000, 'hong',        40000, 0, NOW() - INTERVAL 23 DAY, NULL);

-- ---------------------------------------------------------------------
-- Danh sách đọc
-- ---------------------------------------------------------------------
INSERT INTO reading_lists (nguoi_dung_id, sach_id)
SELECT u.id, b.id FROM (
  SELECT 'SV2024001' AS ma_sv, 'BK-006' AS ma_sach
  UNION ALL SELECT 'SV2024001', 'BK-007'
  UNION ALL SELECT 'SV2024012', 'BK-002'
) d JOIN users u ON u.ma_nguoi_dung = d.ma_sv JOIN books b ON b.ma_sach = d.ma_sach;
