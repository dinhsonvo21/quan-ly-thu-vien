<?php
$chiSoNoiQuy = [
    [
        'nhan' => 'Thời hạn mượn tiêu chuẩn',
        'gia_tri' => HAN_MUON_NGAY . ' ngày',
        'ghi_chu' => 'Có thể gia hạn thêm ' . GIA_HAN_NGAY . ' ngày khi đủ điều kiện.',
        'bieu_tuong' => 'lich',
    ],
    [
        'nhan' => 'Hạn mức mượn tối đa',
        'gia_tri' => SO_SACH_TOI_DA . ' cuốn',
        'ghi_chu' => 'Số lượng đang mượn cùng lúc.',
        'bieu_tuong' => 'sach',
    ],
    [
        'nhan' => 'Thời gian giữ chỗ trực tuyến',
        'gia_tri' => GIU_CHO_GIO . ' giờ',
        'ghi_chu' => 'Tính từ lúc hệ thống ghi nhận phiếu.',
        'bieu_tuong' => 'giu-cho',
    ],
    [
        'nhan' => 'Phí phạt trả trễ',
        'gia_tri' => tien(PHI_PHAT_MOI_NGAY),
        'ghi_chu' => 'Mỗi ngày trên từng cuốn quá hạn.',
        'bieu_tuong' => 'phi',
    ],
];
?>
<div class="noi-quy">
    <header class="noi-quy-banner">
        <p class="noi-quy-nhan">QNU LIBRARY <span aria-hidden="true">•</span> QUY ĐỊNH CHUNG</p>
        <h1>Nội quy &amp; Quy định Thư viện</h1>
        <p class="noi-quy-mo-ta">Cùng gìn giữ nguồn tri thức chung bằng việc sử dụng tài liệu đúng hạn, đúng cách và có trách nhiệm.</p>
    </header>

    <section class="noi-quy-chi-so" aria-label="Các quy định chính">
        <?php foreach ($chiSoNoiQuy as $chiSo): ?>
            <article class="noi-quy-the-chi-so">
                <span class="noi-quy-bieu-tuong" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <?php if ($chiSo['bieu_tuong'] === 'lich'): ?>
                            <rect x="3.5" y="5" width="17" height="16" rx="2"></rect>
                            <path d="M7.5 3v4M16.5 3v4M3.5 9.5h17M8 13h3M8 16.5h3M14 13h2"></path>
                        <?php elseif ($chiSo['bieu_tuong'] === 'sach'): ?>
                            <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21z"></path>
                            <path d="M4 5.5v13A2.5 2.5 0 0 1 6.5 16H20M8 7h8M8 10h8"></path>
                        <?php elseif ($chiSo['bieu_tuong'] === 'giu-cho'): ?>
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M12 7v5l3.5 2"></path>
                        <?php else: ?>
                            <path d="M12 3v18M17 7.5c0-1.4-1.8-2.5-4-2.5S9 6.1 9 7.5 10.8 10 13 10s4 1.1 4 2.5-1.8 2.5-4 2.5-4-1.1-4-2.5"></path>
                        <?php endif; ?>
                    </svg>
                </span>
                <p class="noi-quy-chi-so-nhan"><?= e($chiSo['nhan']) ?></p>
                <p class="noi-quy-chi-so-gia-tri"><?= e($chiSo['gia_tri']) ?></p>
                <p class="noi-quy-chi-so-ghi-chu"><?= e($chiSo['ghi_chu']) ?></p>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="noi-quy-chi-tiet" aria-label="Quy định sử dụng thư viện">
        <article class="noi-quy-the">
            <span class="noi-quy-the-so">01</span>
            <h2>Mượn &amp; gia hạn sách</h2>
            <ul>
                <li>Tài khoản bạn đọc cần ở trạng thái hoạt động để mượn tài liệu.</li>
                <li>Khi nhận sách, vui lòng xuất trình thẻ sinh viên hoặc cung cấp mã bạn đọc cho thủ thư.</li>
                <li>Mỗi lượt mượn có thời hạn <?= (int) HAN_MUON_NGAY ?> ngày. Bạn có thể gia hạn thêm <?= (int) GIA_HAN_NGAY ?> ngày, tối đa <?= (int) SO_LAN_GIA_HAN_TOI_DA ?> lần, nếu sách chưa quá hạn và chưa có người khác đặt trước.</li>
            </ul>
        </article>

        <article class="noi-quy-the">
            <span class="noi-quy-the-so">02</span>
            <h2>Đặt giữ chỗ trực tuyến</h2>
            <ul>
                <li>Đặt giữ chỗ miễn phí qua hệ thống; phiếu được ghi nhận theo thời điểm gửi hợp lệ.</li>
                <li>Khi có nhiều yêu cầu cho cùng đầu sách, thứ tự xử lý căn cứ vào thời điểm ghi nhận phiếu.</li>
                <li>Sách được giữ trong <?= (int) GIU_CHO_GIO ?> giờ kể từ lúc ghi nhận. Phiếu sẽ tự động hết hạn nếu bạn không đến nhận đúng hạn.</li>
            </ul>
        </article>

        <article class="noi-quy-the">
            <span class="noi-quy-the-so">03</span>
            <h2>Trả sách, phí quá hạn &amp; bồi hoàn</h2>
            <ul>
                <li>Trả sách đúng hạn ghi trên phiếu mượn; số ngày trễ được tính theo hạn trả và ngày nhận lại sách.</li>
                <li>Phí quá hạn là <?= e(tien(PHI_PHAT_MOI_NGAY)) ?> mỗi ngày trên mỗi cuốn trễ hạn.</li>
                <li>Nếu sách bị rách, hỏng hoặc mất, hãy báo thủ thư để ghi nhận tình trạng và thực hiện bồi hoàn theo quy định của thư viện. Phí quá hạn (nếu có) được tính riêng.</li>
            </ul>
        </article>

        <article class="noi-quy-the">
            <span class="noi-quy-the-so">04</span>
            <h2>Văn hóa &amp; nội quy phòng đọc</h2>
            <ul>
                <li>Giữ trật tự, để điện thoại ở chế độ im lặng và giữ vệ sinh trong không gian đọc.</li>
                <li>Không viết, vẽ hoặc làm hư hại sách, thiết bị và tài sản chung của thư viện.</li>
                <li>Không cho người khác mượn thẻ sinh viên hoặc mã bạn đọc để sử dụng dịch vụ.</li>
            </ul>
        </article>
    </section>

    <footer class="noi-quy-ho-tro">
        <div class="noi-quy-thong-tin">
            <div class="noi-quy-thong-tin-muc">
                <span class="noi-quy-thong-tin-nhan">Giờ phục vụ</span>
                <strong>Thứ 2 - Thứ 7: 07:00 – 17:00</strong>
            </div>
            <div class="noi-quy-thong-tin-muc">
                <span class="noi-quy-thong-tin-nhan">Địa điểm hỗ trợ</span>
                <strong>Quầy Thủ thư QNU</strong>
            </div>
        </div>
        <a class="noi-quy-nut" href="<?= e(url('/#danh-muc')) ?>">Khám phá danh mục sách</a>
    </footer>
</div>
