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

    /* ---- SR-09: popup "Trả sách & xử lý phí phạt" (chỉ chạy khi trang có #tra-sach) ---- */
    function tien(n) { return Number(n).toLocaleString('vi-VN') + ' đ'; }
    function ngayNganGon(iso) { var p = iso.split('-'); return p[2] + '/' + p[1]; }
    function ngayDayDu(iso) { var p = iso.split('-'); return p[2] + '/' + p[1] + '/' + p[0]; }

    document.addEventListener('DOMContentLoaded', function () {
        var goc = document.getElementById('tra-sach');
        if (!goc) { return; }
        var $ = function (id) { return document.getElementById(id); };
        var donGia = Number(goc.getAttribute('data-don-gia')) || 0;
        var tyLe = { hong: Number(goc.getAttribute('data-ty-le-hong')) || 0, mat: Number(goc.getAttribute('data-ty-le-mat')) || 0 };
        var homNay = goc.getAttribute('data-hom-nay');
        var popup = $('tra-popup'), form = $('tra-form'), oNgay = $('tra-ngay-tra'), oTinhTrang = $('tra-tinh-trang');
        var popupBH = $('tra-boi-hoan-popup'), oGhiChu = $('tra-bh-ghi-chu'), oGhiChuAn = $('tra-ghi-chu-an');
        var phieu = null;            // dữ liệu dòng đang xử lý
        var tinhTrang = 'binh_thuong';   // tình trạng ĐÃ xác nhận

        function khoaCuon() {
            document.body.classList.toggle('tr-khoa-cuon', popup.open || popupBH.open);
        }
        function phiBoiHoan(tt) { return phieu ? Math.round(Number(phieu.giaBia) * (tyLe[tt] || 0)) : 0; }
        function ngayTraHopLe() {
            // Chặn ngày tương lai và ngày trước ngày mượn: ép về khoảng hợp lệ.
            if (!oNgay.value) { oNgay.value = homNay; }
            if (oNgay.value > homNay) { oNgay.value = homNay; }
            if (phieu && oNgay.value < phieu.ngayMuon) { oNgay.value = phieu.ngayMuon; }
            return oNgay.value;
        }
        function capNhat() {
            if (!phieu) { return; }
            var ngayTra = ngayTraHopLe();
            var ngayTre = soNgayTre(phieu.hanTra, ngayTra);
            var phiTre = phiQuaHan(phieu.hanTra, ngayTra, donGia);
            var boiHoan = phiBoiHoan(tinhTrang);
            $('tra-so-ngay-dien-giai').textContent = ngayNganGon(ngayTra) + ' − ' + ngayNganGon(phieu.hanTra) + ' = ' + ngayTre + ' ngày';
            $('tra-phi-tre-dien-giai').textContent = ngayTre + ' × ' + tien(donGia) + ' = ' + tien(phiTre);
            $('tra-phi-boi-hoan').textContent = tien(boiHoan);
            $('tra-tong').textContent = tien(phiTre + boiHoan);
        }

        function moPopup(nut) {
            phieu = {
                hanhDong: nut.getAttribute('data-hanh-dong'), maPhieu: nut.getAttribute('data-ma-phieu'),
                maBanSao: nut.getAttribute('data-ma-ban-sao'), sinhVien: nut.getAttribute('data-sinh-vien'),
                maSv: nut.getAttribute('data-ma-sv'), tenSach: nut.getAttribute('data-ten-sach'),
                tacGia: nut.getAttribute('data-tac-gia'), giaBia: Number(nut.getAttribute('data-gia-bia')) || 0,
                ngayMuon: nut.getAttribute('data-ngay-muon'), hanTra: nut.getAttribute('data-han-tra')
            };
            form.setAttribute('action', phieu.hanhDong);
            $('tra-thong-tin-phieu').textContent = 'Phiếu ' + phieu.maPhieu + ' · Bản sao ' + phieu.maBanSao + ' · ' + phieu.sinhVien + ' (' + phieu.maSv + ')';
            $('tra-ten-sach').textContent = phieu.tenSach;
            var ngayMuonDG = ngayDayDu(phieu.ngayMuon);
            var soNgayMuon = Math.round((Date.parse(phieu.hanTra) - Date.parse(phieu.ngayMuon)) / MS_NGAY);
            $('tra-phu-sach').textContent = (phieu.tacGia ? phieu.tacGia + ' · ' : '') + 'Ngày mượn ' + ngayMuonDG + ' · Thời hạn ' + soNgayMuon + ' ngày';
            $('tra-han-tra').value = ngayDayDu(phieu.hanTra);
            oNgay.min = phieu.ngayMuon; oNgay.max = homNay; oNgay.value = homNay;
            oTinhTrang.value = 'binh_thuong'; tinhTrang = 'binh_thuong'; oGhiChuAn.value = '';
            $('tra-hoan-tat').disabled = false;
            capNhat();
            popup.showModal();
            khoaCuon();
        }

        // Mở popup từ nút "Trả sách" ở mỗi dòng
        document.addEventListener('click', function (e) {
            var nut = e.target.closest('[data-mo-tra]');
            if (nut) { moPopup(nut); return; }
            var dong = e.target.closest('[data-dong]');
            if (dong) { $(dong.getAttribute('data-dong')).close(); }
        });
        popup.addEventListener('close', khoaCuon);
        // Bấm vào vùng tối (backdrop) thì đóng
        [popup, popupBH].forEach(function (d) {
            d.addEventListener('mousedown', function (e) {
                if (e.target === d) { if (d === popupBH) { huyBoiHoan(); } else { d.close(); } }
            });
        });

        oNgay.addEventListener('change', capNhat);
        oNgay.addEventListener('blur', capNhat);

        // Popup bổ sung cho Hư hỏng / Mất
        function huyBoiHoan() {
            if (popupBH.open) { popupBH.close(); }
            oTinhTrang.value = tinhTrang;   // quay về lựa chọn đã xác nhận trước đó
            khoaCuon();
        }
        oTinhTrang.addEventListener('change', function () {
            var tt = oTinhTrang.value;
            if (tt === 'binh_thuong') { tinhTrang = tt; oGhiChuAn.value = ''; capNhat(); return; }
            var ngayTra = ngayTraHopLe();
            var phiTre = phiQuaHan(phieu.hanTra, ngayTra, donGia), bh = phiBoiHoan(tt);
            $('tra-bh-tieu-de').textContent = tt === 'mat' ? 'Xác nhận sách bị mất' : 'Xác nhận sách hư hỏng';
            $('tra-bh-canh-bao').textContent = tt === 'mat'
                ? 'Sách bị mất: bản sao sẽ được đánh dấu "mất" và không trở lại trạng thái sẵn có.'
                : 'Sách hư hỏng: bản sao sẽ được đánh dấu "hỏng" và không trở lại trạng thái sẵn có.';
            $('tra-bh-phieu').textContent = 'Phiếu ' + phieu.maPhieu + ' · ' + phieu.tenSach + ' · Bản sao ' + phieu.maBanSao;
            $('tra-bh-gia-bia').textContent = tien(phieu.giaBia);
            $('tra-bh-ty-le').textContent = Math.round((tyLe[tt] || 0) * 100) + '% giá bìa';
            $('tra-bh-phi').textContent = tien(bh);
            $('tra-bh-phi-tre').textContent = tien(phiTre);
            $('tra-bh-tong').textContent = tien(phiTre + bh);
            oGhiChu.value = oGhiChuAn.value;
            popupBH.showModal();
            khoaCuon();
        });
        $('tra-bh-xac-nhan').addEventListener('click', function () {
            tinhTrang = oTinhTrang.value;
            oGhiChuAn.value = oGhiChu.value.trim();
            popupBH.close();
            capNhat();
            khoaCuon();
        });
        popupBH.addEventListener('click', function (e) { if (e.target.closest('[data-huy-boi-hoan]')) { huyBoiHoan(); } });
        popupBH.addEventListener('cancel', function (e) { e.preventDefault(); huyBoiHoan(); });   // phím Esc

        form.addEventListener('submit', function (e) {
            if (!phieu || oTinhTrang.value !== tinhTrang) { e.preventDefault(); return; }
            ngayTraHopLe();
            $('tra-hoan-tat').disabled = true;   // chống bấm đúp; server vẫn tự kiểm tra và tính lại phí
        });
    });
})();
