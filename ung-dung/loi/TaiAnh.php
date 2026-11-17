<?php
declare(strict_types=1);

namespace Loi;

/** Tải ảnh lên: chỉ JPG/PNG, tối đa ANH_BIA_TOI_DA_BYTE, tên file tự sinh. */
final class TaiAnh
{
    private const CHO_PHEP = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    private const THU_MUC = ['anh-bia-sach', 'anh-dai-dien'];

    /**
     * @param array<string, mixed> $tep phần tử của $_FILES
     * @return string tên file đã lưu (lưu vào CSDL, ví dụ books.anh_bia)
     * @throws \InvalidArgumentException với thông báo tiếng Việt hiển thị được cho người dùng
     */
    public static function luu(array $tep, string $thuMucCon): string
    {
        if (!in_array($thuMucCon, self::THU_MUC, true)) {
            throw new \InvalidArgumentException('Thư mục lưu ảnh không hợp lệ.');
        }
        if (($tep['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $tep['tmp_name'])) {
            throw new \InvalidArgumentException('Tải ảnh lên không thành công.');
        }
        if ((int) $tep['size'] > ANH_BIA_TOI_DA_BYTE) {
            throw new \InvalidArgumentException('Ảnh vượt quá 5 MB.');
        }
        $loai = (new \finfo(FILEINFO_MIME_TYPE))->file((string) $tep['tmp_name']);
        if (!isset(self::CHO_PHEP[$loai])) {
            throw new \InvalidArgumentException('Chỉ chấp nhận ảnh JPG hoặc PNG.');
        }
        $ten = bin2hex(random_bytes(12)) . '.' . self::CHO_PHEP[$loai];
        if (!move_uploaded_file((string) $tep['tmp_name'], THU_MUC_TAI_LEN . '/' . $thuMucCon . '/' . $ten)) {
            throw new \InvalidArgumentException('Không lưu được ảnh lên máy chủ.');
        }
        return $ten;
    }
}
