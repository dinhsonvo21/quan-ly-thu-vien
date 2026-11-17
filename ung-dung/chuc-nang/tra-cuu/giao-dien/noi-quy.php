<?php /** Trang tĩnh — lấy số liệu từ cau-hinh/quy-dinh.php nên luôn khớp quy định. */ ?>
<h1>Nội quy &amp; quy định thư viện</h1>
<ul>
    <li><strong>Thời hạn mượn:</strong> <?= HAN_MUON_NGAY ?> ngày kể từ ngày nhận sách.</li>
    <li><strong>Số sách tối đa:</strong> <?= SO_SACH_TOI_DA ?> cuốn đang mượn cùng lúc.</li>
    <li><strong>Gia hạn:</strong> thêm <?= GIA_HAN_NGAY ?> ngày cho sách chưa quá hạn và không có người khác đặt trước.</li>
    <li><strong>Đặt trước (giữ chỗ):</strong> miễn phí, giữ sách trong <?= GIU_CHO_GIO ?> giờ; quá hạn nhận hệ thống tự hủy đơn.</li>
    <li><strong>Phạt trả trễ:</strong> <?= tien(PHI_PHAT_MOI_NGAY) ?> / ngày / cuốn.</li>
    <li><strong>Sách hư hỏng hoặc mất:</strong> bồi hoàn theo quy định của thư viện, cộng thêm phí trả trễ (nếu có).</li>
    <li>Cần mang thẻ sinh viên khi nhận và trả sách tại quầy.</li>
</ul>
