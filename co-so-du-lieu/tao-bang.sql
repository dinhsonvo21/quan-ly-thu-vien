-- =====================================================================
-- TẠO BẢNG — QNU Library (8 bảng)
-- Chạy được trên MySQL 8.0+ / MariaDB 10.5+ (Laragon, XAMPP).
--
-- LƯU Ý: script này XÓA rồi TẠO LẠI toàn bộ bảng -> chỉ dùng khi dựng mới
-- hoặc muốn làm sạch dữ liệu trên máy mình. Sau đó chạy du-lieu-mau.sql.
-- Khi cần sửa cấu trúc về sau: thêm file vào co-so-du-lieu/thay-doi/ (001-xxx.sql ...),
-- đừng sửa lặng lẽ file này khi cả nhóm đã có dữ liệu.
-- =====================================================================
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS quan_ly_thu_vien
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quan_ly_thu_vien;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS fines, reading_lists, borrow_tickets, reservations, book_copies, books, categories, users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. users — bạn đọc, thủ thư, quản trị viên (SR-02, SR-03, SR-05, SR-10)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ma_nguoi_dung  VARCHAR(20)  NULL COMMENT 'SV2024001 / TT-001 / QT-001; NULL nếu bạn đọc tự đăng ký và chưa được cấp mã',
    ho_dem         VARCHAR(60)  NOT NULL,
    ten            VARCHAR(30)  NOT NULL,
    email          VARCHAR(120) NOT NULL,
    mat_khau_bam   VARCHAR(255) NOT NULL COMMENT 'kết quả password_hash(), KHÔNG lưu mật khẩu thuần',
    sdt            VARCHAR(15)  NULL,
    khoa_lop       VARCHAR(100) NULL,
    vai_tro        ENUM('ban_doc','thu_thu','quan_tri') NOT NULL DEFAULT 'ban_doc',
    trang_thai     ENUM('hoat_dong','tam_khoa')          NOT NULL DEFAULT 'hoat_dong',
    anh_dai_dien   VARCHAR(100) NULL,
    ngay_tao       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_ma (ma_nguoi_dung),
    KEY idx_users_vai_tro (vai_tro, trang_thai)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. categories — thể loại (SR-07 "Quản lý thể loại")
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ten_the_loai  VARCHAR(80)  NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_ten (ten_the_loai)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. books — đầu sách (SR-01, SR-04, SR-07)
-- ---------------------------------------------------------------------
CREATE TABLE books (
    id                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ma_sach              VARCHAR(20)  NOT NULL COMMENT 'BK-001',
    isbn                 VARCHAR(20)  NULL,
    ten_sach             VARCHAR(200) NOT NULL,
    tac_gia              VARCHAR(150) NOT NULL,
    nha_xuat_ban         VARCHAR(100) NULL,
    nam_xuat_ban         SMALLINT UNSIGNED NULL,
    ngon_ngu             VARCHAR(40)  NOT NULL DEFAULT 'Tiếng Việt',
    loai_tai_lieu        VARCHAR(40)  NOT NULL DEFAULT 'Sách tham khảo' COMMENT 'bộ lọc "Loại tài liệu" ở SR-01',
    so_trang             SMALLINT UNSIGNED NULL,
    mo_ta                TEXT         NULL,
    gia_bia              INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'đồng; dùng tính phí bồi hoàn hỏng/mất',
    vi_tri_ke            VARCHAR(30)  NOT NULL,
    anh_bia              VARCHAR(100) NULL,
    trang_thai_luu_hanh  ENUM('dang_luu_hanh','ngung_phuc_vu') NOT NULL DEFAULT 'dang_luu_hanh'
                         COMMENT 'ngừng phục vụ thay cho xóa, để giữ lịch sử giao dịch',
    the_loai_id          INT UNSIGNED NOT NULL,
    ngay_tao             DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'sắp xếp "Mới thêm gần đây"',
    PRIMARY KEY (id),
    UNIQUE KEY uq_books_ma (ma_sach),
    KEY idx_books_the_loai (the_loai_id),
    KEY idx_books_ten (ten_sach),
    KEY idx_books_tac_gia (tac_gia),
    KEY idx_books_moi (ngay_tao),
    CONSTRAINT fk_books_the_loai FOREIGN KEY (the_loai_id) REFERENCES categories (id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. book_copies — từng bản sao vật lý (SR-08 "BK-002-05", SR-09 "BK-003-02")
--    Số bản sẵn có của một đầu sách = COUNT(*) các bản có trang_thai = 'san_co'.
--    Mọi thao tác đổi trang_thai phải chạy trong giao dịch + SELECT ... FOR UPDATE.
-- ---------------------------------------------------------------------
CREATE TABLE book_copies (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ma_ban_sao   VARCHAR(30)  NOT NULL COMMENT 'BK-002-05',
    sach_id      INT UNSIGNED NOT NULL,
    trang_thai   ENUM('san_co','dang_giu','dang_muon','hong','mat') NOT NULL DEFAULT 'san_co',
    PRIMARY KEY (id),
    UNIQUE KEY uq_copies_ma (ma_ban_sao),
    KEY idx_copies_sach (sach_id, trang_thai),
    CONSTRAINT fk_copies_sach FOREIGN KEY (sach_id) REFERENCES books (id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. reservations — đặt giữ chỗ 24h (SR-04 popup, SR-05, SR-08)
--    Mã hiển thị DC-1042 = 'DC-' + id.
-- ---------------------------------------------------------------------
CREATE TABLE reservations (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nguoi_dung_id  INT UNSIGNED NOT NULL,
    ban_sao_id     INT UNSIGNED NOT NULL,
    noi_nhan       VARCHAR(100) NOT NULL DEFAULT 'Thư viện trung tâm',
    ngay_dat       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    het_han        DATETIME     NOT NULL COMMENT 'ngay_dat + 24 giờ (GIU_CHO_GIO)',
    trang_thai     ENUM('cho_nhan','da_giao','da_huy','het_han') NOT NULL DEFAULT 'cho_nhan',
    -- Chống giữ chỗ trùng: mỗi bản sao chỉ có tối đa 1 đơn đang chờ nhận.
    ban_sao_dang_cho INT UNSIGNED GENERATED ALWAYS AS (IF(trang_thai = 'cho_nhan', ban_sao_id, NULL)) VIRTUAL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_reservations_dang_cho (ban_sao_dang_cho),
    KEY idx_reservations_nguoi (nguoi_dung_id, trang_thai),
    KEY idx_reservations_het_han (trang_thai, het_han),
    CONSTRAINT fk_reservations_nguoi FOREIGN KEY (nguoi_dung_id) REFERENCES users (id),
    CONSTRAINT fk_reservations_ban_sao FOREIGN KEY (ban_sao_id) REFERENCES book_copies (id),
    CONSTRAINT ck_reservations_han CHECK (het_han > ngay_dat)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. borrow_tickets — phiếu mượn (SR-05, SR-08, SR-09)
--    Mã hiển thị PM-2064 = 'PM-' + id.
--    "Quá hạn" KHÔNG lưu: tính từ trang_thai = 'dang_muon' AND han_tra < CURDATE().
-- ---------------------------------------------------------------------
CREATE TABLE borrow_tickets (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nguoi_dung_id   INT UNSIGNED NOT NULL,
    ban_sao_id      INT UNSIGNED NOT NULL,
    dat_cho_id      INT UNSIGNED NULL COMMENT 'có nếu phiếu sinh ra từ nút "Giao sách" (đơn đặt trước)',
    thu_thu_id      INT UNSIGNED NULL COMMENT 'thủ thư/admin đã lập phiếu',
    ngay_muon       DATE         NOT NULL,
    han_tra         DATE         NOT NULL COMMENT 'ngay_muon + 14 ngày (+7 mỗi lần gia hạn)',
    ngay_tra        DATE         NULL,
    so_lan_gia_han  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    trang_thai      ENUM('dang_muon','da_tra') NOT NULL DEFAULT 'dang_muon',
    -- Chống cho mượn trùng: mỗi bản sao chỉ nằm trong tối đa 1 phiếu đang mượn.
    ban_sao_dang_muon INT UNSIGNED GENERATED ALWAYS AS (IF(trang_thai = 'dang_muon', ban_sao_id, NULL)) VIRTUAL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tickets_dang_muon (ban_sao_dang_muon),
    KEY idx_tickets_nguoi (nguoi_dung_id, trang_thai),
    KEY idx_tickets_han (trang_thai, han_tra),
    KEY idx_tickets_ban_sao (ban_sao_id),
    CONSTRAINT fk_tickets_nguoi FOREIGN KEY (nguoi_dung_id) REFERENCES users (id),
    CONSTRAINT fk_tickets_ban_sao FOREIGN KEY (ban_sao_id) REFERENCES book_copies (id),
    CONSTRAINT fk_tickets_dat_cho FOREIGN KEY (dat_cho_id) REFERENCES reservations (id),
    CONSTRAINT fk_tickets_thu_thu FOREIGN KEY (thu_thu_id) REFERENCES users (id),
    CONSTRAINT ck_tickets_han CHECK (han_tra >= ngay_muon),
    CONSTRAINT ck_tickets_tra CHECK (ngay_tra IS NULL OR ngay_tra >= ngay_muon),
    CONSTRAINT ck_tickets_trang_thai CHECK ((trang_thai = 'da_tra') = (ngay_tra IS NOT NULL))
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. fines — phí phạt ghi nhận lúc trả sách (SR-09). Mỗi phiếu có tối đa 1 dòng.
--    Chỉ tạo dòng khi phát sinh phí; da_thu = 0 nghĩa là còn nợ (chặn mượn thêm).
--    Phí của phiếu ĐANG quá hạn mà chưa trả chưa nằm ở đây: dashboard tính động
--    bằng TinhPhiPhat từ han_tra.
-- ---------------------------------------------------------------------
CREATE TABLE fines (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    phieu_muon_id    INT UNSIGNED NOT NULL,
    so_ngay_tre      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    phi_tre          INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'so_ngay_tre x 2.000',
    tinh_trang_sach  ENUM('binh_thuong','hong','mat') NOT NULL DEFAULT 'binh_thuong',
    phi_boi_hoan     INT UNSIGNED NOT NULL DEFAULT 0,
    da_thu           TINYINT(1)   NOT NULL DEFAULT 0,
    ngay_ghi         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngay_thu         DATETIME     NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_fines_phieu (phieu_muon_id),
    KEY idx_fines_chua_thu (da_thu),
    CONSTRAINT fk_fines_phieu FOREIGN KEY (phieu_muon_id) REFERENCES borrow_tickets (id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. reading_lists — nút "Thêm vào danh sách đọc" (SR-04)
-- ---------------------------------------------------------------------
CREATE TABLE reading_lists (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nguoi_dung_id  INT UNSIGNED NOT NULL,
    sach_id        INT UNSIGNED NOT NULL,
    ngay_them      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_reading_nguoi_sach (nguoi_dung_id, sach_id),
    CONSTRAINT fk_reading_nguoi FOREIGN KEY (nguoi_dung_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_reading_sach  FOREIGN KEY (sach_id)       REFERENCES books (id) ON DELETE CASCADE
) ENGINE=InnoDB;
