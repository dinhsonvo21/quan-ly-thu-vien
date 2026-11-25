/* SR-10 Tài khoản & phân quyền (Quân): popup tạo thủ thư, popup đổi vai trò, xác nhận khóa/mở khóa. */
(function () {
    'use strict';

    document.querySelectorAll('form[data-tu-gui] select').forEach(function (s) {
        s.addEventListener('change', function () { s.form.submit(); });
    });

    document.querySelectorAll('form[data-xac-nhan]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            if (!window.confirm(f.dataset.xacNhan)) { e.preventDefault(); }
        });
    });

    function chuanBi(hop) {
        if (!hop || typeof hop.showModal !== 'function') { return false; }
        hop.querySelectorAll('[data-dong]').forEach(function (n) {
            n.addEventListener('click', function () { hop.close(); });
        });
        hop.addEventListener('click', function (e) { if (e.target === hop) { hop.close(); } });
        return true;
    }

    var hopTao = document.getElementById('hop-tao-thu-thu');
    if (chuanBi(hopTao)) {
        var formTao = hopTao.querySelector('form');
        document.getElementById('nut-tao-thu-thu').addEventListener('click', function () {
            formTao.reset();
            hopTao.showModal();
            formTao.elements.ho_ten.focus();
        });
        var cu = null;
        try { cu = JSON.parse(hopTao.dataset.cu || 'null'); } catch (e) { cu = null; }
        if (cu) {
            formTao.elements.ho_ten.value = cu.ho_ten || '';
            formTao.elements.email.value = cu.email || '';
            hopTao.showModal();
        }
    }

    var hopVaiTro = document.getElementById('hop-doi-vai-tro');
    if (chuanBi(hopVaiTro)) {
        var formVt = hopVaiTro.querySelector('form');
        var chon = formVt.elements.vai_tro;
        var canhBao = hopVaiTro.querySelector('[data-canh-bao]');
        var capNhatCanhBao = function () { canhBao.hidden = chon.value !== 'quan_tri'; };
        chon.addEventListener('change', capNhatCanhBao);

        document.querySelectorAll('.tk-nut-sua').forEach(function (n) {
            n.addEventListener('click', function () {
                formVt.action = hopVaiTro.dataset.mauUrl.replace('__ID__', encodeURIComponent(n.dataset.id));
                hopVaiTro.querySelector('[data-o="ma"]').value = n.dataset.ma || '—';
                hopVaiTro.querySelector('[data-o="ten"]').value = n.dataset.ten;
                hopVaiTro.querySelector('[data-o="email"]').value = n.dataset.email;
                chon.value = n.dataset.vaiTro;
                capNhatCanhBao();
                hopVaiTro.showModal();
            });
        });
    }
})();
