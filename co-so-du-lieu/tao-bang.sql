-- =====================================================================
-- TẠO BẢNG — QNU Library (8 bảng)           Chủ sở hữu: Võ Đình Sơn
-- TODO: viết đầy đủ (kiểu dữ liệu, khóa chính/ngoại, CHECK, chỉ mục).
-- Mọi thay đổi sau này: thêm file vào co-so-du-lieu/thay-doi/ (001-xxx.sql, 002-xxx.sql...)
-- =====================================================================
CREATE DATABASE IF NOT EXISTS quan_ly_thu_vien CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quan_ly_thu_vien;

-- users          : id, ma_nguoi_dung (SV2024001 / TT-001 / QT-001), ho_dem, ten, email, mat_khau_bam, sdt, khoa_lop,
--                  vai_tro (ban_doc/thu_thu/quan_tri), trang_thai (hoat_dong/tam_khoa), anh_dai_dien
-- categories     : id, ten_the_loai
-- books          : id, ma_sach, isbn, ten_sach, tac_gia, nha_xuat_ban, nam_xuat_ban, ngon_ngu, loai_tai_lieu, mo_ta,
--                  gia_bia, vi_tri_ke, anh_bia, trang_thai_luu_hanh, the_loai_id, ngay_tao
-- book_copies    : id, ma_ban_sao (BK-002-05), sach_id, trang_thai (san_co/dang_giu/dang_muon/hong/mat)
-- reservations   : id, nguoi_dung_id, ban_sao_id, noi_nhan, ngay_dat, het_han, trang_thai (cho_nhan/da_giao/da_huy/het_han)
-- borrow_tickets : id, nguoi_dung_id, ban_sao_id, dat_cho_id (NULL), ngay_muon, han_tra, ngay_tra, so_lan_gia_han,
--                  trang_thai (dang_muon/da_tra), thu_thu_id         -- "quá hạn" TÍNH từ han_tra, không lưu
-- fines          : id, phieu_muon_id, so_ngay_tre, phi_tre, tinh_trang_sach, phi_boi_hoan, da_thu
-- reading_lists  : id, nguoi_dung_id, sach_id, ngay_them (UNIQUE nguoi_dung_id + sach_id)
