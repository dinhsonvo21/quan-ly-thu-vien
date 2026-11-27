-- Thanh toán phí phạt tại quầy (trang Trả sách): lưu hình thức thu và ghi chú tình trạng sách.
-- Chạy một lần trên CSDL đã có dữ liệu; tao-bang.sql chưa gồm hai cột này.
USE quan_ly_thu_vien;

ALTER TABLE fines
    ADD COLUMN hinh_thuc_thu ENUM('tien_mat','chuyen_khoan') NULL DEFAULT NULL AFTER da_thu,
    ADD COLUMN ghi_chu VARCHAR(300) NULL DEFAULT NULL AFTER ngay_thu;
