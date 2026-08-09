<?php
/* =====================================================================
   ghi.php — nhận kèo từ trình duyệt và chèn vào bảng `keo`.
   Nhận MỘT LÔ (mảng kèo) để client gom lại rồi gửi một lần, đỡ số request.

   Chống ghi trùng bằng `uid` (ULID sinh ở JS): mạng chập, client gửi lại
   nguyên lô cũng chỉ thành một dòng. Không có nó thì cỡ mẫu bị thổi phồng
   giả và khoảng tin cậy hẹp lại một cách dối trá.
   ===================================================================== */
declare(strict_types=1);
require __DIR__ . '/config.php';

batCors();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    traLoi(405, ['ok' => false, 'loi' => 'chi nhan POST']);
}
kiemTraToken();

$tho = file_get_contents('php://input');
if ($tho === false || strlen($tho) > 262144) {          // trần 256KB chống nhồi
    traLoi(413, ['ok' => false, 'loi' => 'goi tin qua lon']);
}
$lo = json_decode($tho, true);
if (!is_array($lo)) traLoi(400, ['ok' => false, 'loi' => 'JSON hong']);
if (isset($lo['uid'])) $lo = [$lo];                      // cho phép gửi một kèo lẻ
if (count($lo) > 50)  traLoi(413, ['ok' => false, 'loi' => 'toi da 50 keo moi lo']);

/* Danh sách cột client được phép ghi. Cột kết quả (kq, gia_ra, mfe_bp...)
   CỐ Ý không có ở đây — chỉ cron mới được chấm sổ. */
const COT = [
    'uid','ts','coin','inst_id','ver','loai',
    'dir','mid','bid','ask','vao','chot','cat','han_ms',
    's','sig_f30','sig_f5','sig_bk','sig_oi','sig_fu','sig_lq',
    'book_usd','mua30_usd','ban30_usd','cap','frame_h','ex_mask',
    'vao_trust','vao_sv','vao_pl','vao_usd','vao_ty_le_okx',
    'chot_trust','chot_usd','chot_ty_le_okx',
    'tuoi_may','tuoi_coin','chan','ghim',
];
const BAT_BUOC = ['uid','ts','coin','inst_id','ver','dir','mid','vao','chot','cat','han_ms','s'];

try {
    $pdo = db();
    $sql = 'INSERT INTO keo (' . implode(',', COT) . ') VALUES ('
         . implode(',', array_map(fn($c) => ':' . $c, COT)) . ')'
         /* uid trùng = client gửi lại. Không cập nhật gì, giữ nguyên bản ghi đầu. */
         . ' ON DUPLICATE KEY UPDATE id = id';
    $st = $pdo->prepare($sql);

    $nhan = 0; $trung = 0; $bo = [];
    foreach ($lo as $i => $k) {
        if (!is_array($k)) { $bo[] = "[$i] khong phai object"; continue; }
        $thieu = array_values(array_filter(BAT_BUOC, fn($c) => !isset($k[$c]) || $k[$c] === ''));
        if ($thieu) { $bo[] = "[$i] thieu: " . implode(',', $thieu); continue; }

        $tv = [];
        foreach (COT as $c) $tv[$c] = $k[$c] ?? null;
        $tv['loai'] = $tv['loai'] ?: 'dir';
        /* Giá gửi lên dưới dạng CHUỖI để không mất chính xác khi qua float PHP —
           coin có giá 0.0000000123 lẫn 90000, bind kiểu số là quay về đúng lỗi
           nhị phân mà cột DECIMAL sinh ra để tránh. */
        foreach (['mid','bid','ask','vao','chot','cat'] as $c) {
            if ($tv[$c] !== null) $tv[$c] = (string)$tv[$c];
        }
        $st->execute($tv);
        if ($st->rowCount() > 0) $nhan++; else $trung++;
    }
    traLoi(200, ['ok' => true, 'nhan' => $nhan, 'trung' => $trung, 'bo' => $bo]);

} catch (Throwable $e) {
    error_log('[ghi.php] ' . $e->getMessage());
    traLoi(500, ['ok' => false, 'loi' => 'loi may chu']);
}
