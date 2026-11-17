/*
 * Đồng hồ đếm ngược 24h giữ chỗ (SR-05, SR-08).
 * Dùng:  <span data-het-han="2026-10-28T08:00:00+07:00">23:00:00</span>
 * Chỉ để HIỂN THỊ — việc tự hủy đơn quá hạn do server làm (tac-vu-nen/huy-dat-cho-qua-han.php).
 */
(function () {
    'use strict';
    function dinhDang(ms) {
        if (ms <= 0) { return '00:00:00'; }
        var g = Math.floor(ms / 3600000), p = Math.floor(ms % 3600000 / 60000), s = Math.floor(ms % 60000 / 1000);
        return [g, p, s].map(function (n) { return String(n).padStart(2, '0'); }).join(':');
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-het-han]').forEach(function (el) {
            var moc = Date.parse(el.getAttribute('data-het-han'));
            if (isNaN(moc)) { return; }
            function capNhat() {
                var con = moc - Date.now();
                el.textContent = dinhDang(con);
                el.classList.toggle('het-han', con <= 0);
            }
            capNhat();
            setInterval(capNhat, 1000);
        });
    });
})();
