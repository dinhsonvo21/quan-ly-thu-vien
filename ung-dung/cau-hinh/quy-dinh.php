<?php
declare(strict_types=1);

/**
 * QUY ĐỊNH NGHIỆP VỤ — nguồn duy nhất cho mọi con số trong báo cáo.
 * Không viết cứng 14, 7, 5, 2000, 24... ở bất kỳ nơi nào khác.
 */

// Mượn - trả (báo cáo mục 2.2.4)
define('HAN_MUON_NGAY', 14);          // hạn trả dự kiến = ngày mượn + 14
define('GIA_HAN_NGAY', 7);            // gia hạn +7 ngày
define('SO_LAN_GIA_HAN_TOI_DA', 1);   // TODO: chốt với nhóm (báo cáo chưa nêu)
define('SO_SACH_TOI_DA', 5);          // tối đa 5 cuốn đang mượn
define('GIU_CHO_GIO', 24);            // giữ chỗ 24 giờ

// Phạt
define('PHI_PHAT_MOI_NGAY', 2000);    // đ / ngày / cuốn
define('TY_LE_BOI_HOAN_HONG', 0.5);   // TODO: chốt — hỏng bồi hoàn 50% giá bìa
define('TY_LE_BOI_HOAN_MAT', 1.0);    // TODO: chốt — mất bồi hoàn 100% giá bìa

// Chung
define('NOI_NHAN_MAC_DINH', 'Thư viện trung tâm');
define('SO_SACH_MOI_TRANG', 8);
define('MAT_KHAU_TOI_THIEU', 8);
define('ANH_BIA_TOI_DA_BYTE', 5 * 1024 * 1024); // 5 MB, JPG/PNG

// Vai trò (giá trị lưu trong cột users.vai_tro)
define('VAI_TRO_BAN_DOC', 'ban_doc');
define('VAI_TRO_THU_THU', 'thu_thu');
define('VAI_TRO_QUAN_TRI', 'quan_tri');

// Trạng thái bản sao (book_copies.trang_thai)
define('BS_SAN_CO', 'san_co');
define('BS_DANG_GIU', 'dang_giu');
define('BS_DANG_MUON', 'dang_muon');
define('BS_HONG', 'hong');
define('BS_MAT', 'mat');
