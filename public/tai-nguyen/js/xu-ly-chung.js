/* JS dùng chung (Sơn). Giữ nhỏ gọn, mỗi chức năng riêng nên đặt file riêng. */
(function () {
    'use strict';
    // Bấm vào thông báo để ẩn.
    document.addEventListener('click', function (e) {
        var tb = e.target.closest('.thong-bao');
        if (tb) { tb.remove(); }
    });
    // TODO (Sơn): bộ lọc/sắp xếp danh mục sách ở trang chủ (SR-01) tự gửi form khi đổi lựa chọn.
})();
