/*
 * Tính phạt quá hạn ngay trên popup trả sách (SR-09) — chỉ để HIỂN THỊ.
 * Số tiền thật luôn do server tính lại (TinhPhiPhat.php), không tin dữ liệu từ trình duyệt.
 * Ngày dạng 'YYYY-MM-DD'.   Phí = max(0, ngàyTrả - hạnTrả) × đơnGiá
 */
(function () {
    'use strict';
    var MS_NGAY = 86400000;
    function soNgayTre(hanTra, ngayTra) {
        var d = Math.floor((Date.parse(ngayTra) - Date.parse(hanTra)) / MS_NGAY);
        return d > 0 ? d : 0;
    }
    function phiQuaHan(hanTra, ngayTra, donGia) {
        return soNgayTre(hanTra, ngayTra) * donGia;
    }
    window.TinhPhiPhat = { soNgayTre: soNgayTre, phiQuaHan: phiQuaHan };
    // TODO (Quân): gắn vào ô "Ngày trả thực tế" + dropdown "Tình trạng sách" để cập nhật khung phí và tổng cần thu.
})();
