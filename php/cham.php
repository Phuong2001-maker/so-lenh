<?php
/* =====================================================================
   cham.php — CRON mỗi phút. Chấm sổ các kèo đang mở bằng nến 1m của OKX.

   VÌ SAO PHẢI CHẠY Ở ĐÂY MÀ KHÔNG PHẢI TRÌNH DUYỆT:
   máy quét xoay vòng tập coin mỗi 60 giây, nên kèo của coin bị gỡ sẽ bị bỏ rơi
   giữa chừng — và việc mất tích đó KHÔNG ngẫu nhiên, nó tương quan với "có coin
   khác đang bùng nổ". Chấm ở client là tự tạo thiên lệch sống sót không sửa được.

   CHẤM BẰNG HAI QUY ƯỚC:
     kq      = CHẠM      (bóng nến chạm tới giá) — rộng rãi
     kq_dong = ĐÓNG NẾN  (giá đóng cửa vượt hẳn) — chặt chẽ
   Chênh lệch giữa hai con số là thước đo độ nhạy của kết luận. Cả hai đều áp
   CÙNG một quy ước cho chốt lời và cắt lỗ — siết một đầu là tự làm đẹp số.

   Cài cron trong 1Panel:  * * * * *  php /duong/dan/php/cham.php
   Chạy tay để thử:        php cham.php
   ===================================================================== */
declare(strict_types=1);
require __DIR__ . '/config.php';

const BAR          = '1m';
const MS_NEN       = 60000;
const TRAN_TRANG   = 12;         // tối đa 12 trang x 300 nến = 60 giờ mỗi kèo
const HAN_BO_QUA_H = 6;          // quá hạn + 6h mà vẫn không chấm được -> bo_qua
const CHAY_CLI     = PHP_SAPI === 'cli';

if (!CHAY_CLI) {
    header('Content-Type: text/plain; charset=utf-8');
    /* Nhiều gói hosting chỉ cho cron gọi URL chứ không có PHP CLI, mà gọi URL thì
       không gửi được header X-Token. Cho phép token qua query string trong ĐÚNG
       trường hợp này. Đường dẫn có token nên đừng chia sẻ URL cron cho ai. */
    if (isset($_GET['token']) && API_TOKEN !== '' && hash_equals(API_TOKEN, (string)$_GET['token'])) {
        // hợp lệ, đi tiếp
    } else {
        kiemTraToken();
    }
}

$pdo = db();

/* MỘT NGƯỜI GHI DUY NHẤT: shared hosting chạy chậm, cron phút này có thể chưa
   xong thì phút sau đã nổ. Hai tiến trình cùng chấm sẽ ghi đè nhau im lặng. */
$khoa = $pdo->query("SELECT GET_LOCK('cham_keo', 0)")->fetchColumn();
if ((int)$khoa !== 1) { echo "dang co tien trinh khac chay, bo qua luot nay\n"; exit; }

try {
    $keos = $pdo->query(
        "SELECT id, uid, coin, inst_id, dir, ts, han_ms, vao, chot, cat
         FROM keo WHERE kq = 'mo' ORDER BY han_ms ASC LIMIT 60"
    )->fetchAll();

    echo count($keos) . " keo dang mo\n";
    $dem = ['thang'=>0,'thua'=>0,'het_han'=>0,'nhap_nhang'=>0,'khong_khop'=>0,'bo_qua'=>0,'cho'=>0];
    $bay = time() * 1000;

    foreach ($keos as $k) {
        $ket = chamMotKeo($k, $bay);
        if ($ket === null) { $dem['cho']++; continue; }
        luuKetQua($pdo, (int)$k['id'], $ket);
        $dem[$ket['kq']]++;
        echo sprintf("  %-10s %-12s %s  gia_ra=%s  mfe=%+d mae=%+d  (dong: %s)\n",
            $k['coin'], $ket['kq'], $k['dir'] > 0 ? 'LONG ' : 'SHORT',
            $ket['gia_ra'] ?? '-', $ket['mfe_bp'], $ket['mae_bp'], $ket['kq_dong']);
    }
    echo "ket qua: " . json_encode($dem) . "\n";

} catch (Throwable $e) {
    echo "LOI: " . $e->getMessage() . "\n";
    error_log('[cham.php] ' . $e->getMessage());
} finally {
    $pdo->query("SELECT RELEASE_LOCK('cham_keo')");
}

// =====================================================================

function chamMotKeo(array $k, int $bay): ?array
{
    $dir  = (int)$k['dir'];
    $vao  = (float)$k['vao'];
    $chot = (float)$k['chot'];
    $cat  = (float)$k['cat'];
    $ts   = (int)$k['ts'];
    $han  = (int)$k['han_ms'];
    $quaHan = $bay >= $han;

    $den  = min($bay, $han);
    if ($den <= $ts + MS_NEN) return null;                 // chưa đủ một cây nến

    $nen = layNen($k['inst_id'], $ts, $den);

    /* Không có nến dùng được — hai trường hợp gộp làm một:
         null  = gọi API hỏng (mạng, OKX lỗi)
         []    = gọi được nhưng khoảng thời gian đã rơi ra ngoài 1.440 nến mà
                 /market/candles giữ (tức cron chết > 24 giờ, hoặc đồng hồ client lệch)
       Trước đây chỉ nhánh null mới có lối thoát 'bo_qua', nhánh mảng rỗng thì
       return null vô hạn -> kèo kẹt 'mo' vĩnh viễn. Tệ hơn: SELECT sắp theo
       han_ms ASC nên đám kèo chết luôn đứng đầu, tích đủ 60 con là cron chỉ
       quét xác chết và không kèo mới nào được chấm nữa — chết im lặng. */
    if ($nen === null || !$nen) {
        if ($bay > $han + HAN_BO_QUA_H * 3600000) {
            return ['kq'=>'bo_qua','kq_dong'=>'bo_qua','gia_ra'=>null,'ts_ra'=>$bay,
                    'mfe_bp'=>0,'mae_bp'=>0];
        }
        return null;
    }

    $mfe = 0.0; $mae = 0.0;                                 // điểm cơ bản, so với giá vào
    $kqCham = null; $kqDong = null; $giaRa = null; $tsRa = null; $dongCuoi = null;
    $daKhop = false;

    foreach ($nen as $c) {
        [$t, $o, $h, $l, $cl] = $c;
        if ($t < $ts) continue;                             // nến bắt đầu trước lúc ghi kèo
        if ($t > $han) break;
        $dongCuoi = $cl;

        /* ===== CHỜ LỆNH KHỚP ĐÃ =====
           `vao` là tường ở PHÍA BÊN KIA giá lúc phát kèo: kèo LONG vào tại tường
           đỡ NẰM DƯỚI giá, kèo SHORT vào tại tường kháng NẰM TRÊN. Đó là lệnh
           CHỜ, chưa khớp. Nếu giá chạy thẳng tới chốt lời mà không quay lại chạm
           `vao` thì lệnh KHÔNG BAO GIỜ vào — chấm nó là "thắng" là bịa ra một
           khoản lãi chưa từng tồn tại, và sai lệch này thuận chiều thị trường
           nên nó thổi phồng kết quả một cách CÓ HỆ THỐNG. */
        if (!$daKhop) {
            $daKhop = $dir > 0 ? ($l <= $vao) : ($h >= $vao);
            if (!$daKhop) continue;                         // chưa vào lệnh, chưa tính gì
        }

        /* MFE/MAE chỉ tính TRONG LÚC ĐANG CẦM LỆNH — trước đây tính cả đoạn giá
           sau khi đã thoát, làm "giá tốt nhất từng chạm" thành số vô nghĩa. */
        if ($kqCham === null) {
            $tot = $dir > 0 ? $h : $l;
            $xau = $dir > 0 ? $l : $h;
            $mfe = max($mfe, ($tot - $vao) * $dir / $vao * 10000);
            $mae = min($mae, ($xau - $vao) * $dir / $vao * 10000);
        }

        // --- quy ước CHẠM (bóng nến) ---
        $chamTP = $dir > 0 ? ($h >= $chot) : ($l <= $chot);
        $chamSL = $dir > 0 ? ($l <= $cat)  : ($h >= $cat);
        if ($kqCham === null && ($chamTP || $chamSL)) {
            /* Cả hai trong CÙNG một cây nến 1m thì KHÔNG biết cái nào đến trước.
               Bắt buộc là một kết quả riêng — ném bỏ nhóm này là làm lệch số LÊN,
               vì nến biến động mạnh thường quét stop trước rồi mới chạy. */
            $kqCham = ($chamTP && $chamSL) ? 'nhap_nhang' : ($chamTP ? 'thang' : 'thua');
            $giaRa  = $chamTP && !$chamSL ? $chot : ($chamSL && !$chamTP ? $cat : $cl);
            $tsRa   = $t + MS_NEN;
        }

        // --- quy ước ĐÓNG NẾN (chặt hơn), áp CÙNG cách cho cả hai đầu ---
        $dongTP = $dir > 0 ? ($cl >= $chot) : ($cl <= $chot);
        $dongSL = $dir > 0 ? ($cl <= $cat)  : ($cl >= $cat);
        if ($kqDong === null && ($dongTP || $dongSL)) {
            $kqDong = ($dongTP && $dongSL) ? 'nhap_nhang' : ($dongTP ? 'thang' : 'thua');
        }

        if ($kqCham !== null && $kqDong !== null) break;
    }

    /* Lệnh chờ không bao giờ khớp — KHÔNG phải thắng, thua, hay hết hạn.
       Phải là nhóm riêng và phải bị loại khỏi mọi phép tính tỷ lệ thắng. */
    if (!$daKhop) {
        if (!$quaHan) return null;
        return ['kq'=>'khong_khop','kq_dong'=>'khong_khop','gia_ra'=>null,'ts_ra'=>$han,
                'mfe_bp'=>0,'mae_bp'=>0];
    }

    /* CHƯA CHỐT SỔ khi một trong hai quy ước còn bỏ ngỏ và vẫn còn hạn.
       Vì cl <= h luôn đúng nên quy ước ĐÓNG không bao giờ ngã ngũ TRƯỚC quy ước
       CHẠM; đóng dòng ngay khi CHẠM xong sẽ khoá kq_dong ở 'mo' vĩnh viễn
       (SELECT chỉ tìm kq='mo') và vô hiệu hoá luôn bảng đo độ nhạy quy ước.
       Chấm lại từ nến là tất định nên chạy lại nhiều lượt vẫn ra đúng một kết quả. */
    if ($kqCham === null || $kqDong === null) {
        if (!$quaHan) return null;
        if ($kqCham === null) { $kqCham = 'het_han'; $giaRa = $dongCuoi; $tsRa = $han; }
        if ($kqDong === null) $kqDong = 'het_han';
    }

    return ['kq'=>$kqCham, 'kq_dong'=>$kqDong, 'gia_ra'=>$giaRa,
            'ts_ra'=>$tsRa ?? $han, 'mfe_bp'=>(int)round($mfe), 'mae_bp'=>(int)round($mae)];
}

/* Kéo nến 1m trong khoảng [tsTu, tsDen].

   PHÂN TRANG LÙI bằng tham số `after` (= lấy bản ghi CŨ HƠN mốc đó), không dùng
   `before`. Lý do: với `before` và limit 300, khi khoảng cần lấy dài hơn 300 nến
   thì OKX trả về 300 cây MỚI NHẤT chứ không phải 300 cây ngay sau mốc — tức là
   thủng mất đoạn giữa mà không hề báo lỗi. Đi lùi thì ngữ nghĩa rõ ràng.

   Dùng /market/candles (không phải /market/history-candles) vì nó giữ 1.440 nến
   1m gần nhất = đúng 24 giờ, thừa cho hạn kèo tối đa 4h + 6h ân hạn, mà lại mới
   hơn. Bỏ qua nến CHƯA ĐÓNG (confirm = '0') — giá đóng cửa của nó chưa chốt,
   lượt cron sau sẽ nhặt lại. */
function layNen(string $instId, int $tsTu, int $tsDen): ?array
{
    $ra = []; $moc = $tsDen + 1; $loi = false;
    for ($trang = 0; $trang < TRAN_TRANG; $trang++) {
        $url = 'https://www.okx.com/api/v5/market/candles?instId=' . rawurlencode($instId)
             . '&bar=' . BAR . '&limit=300&after=' . $moc;
        $j = getJson($url);
        if ($j === null || ($j['code'] ?? '1') !== '0') { $loi = true; break; }
        $d = $j['data'] ?? [];
        if (!$d) break;
        $cuNhat = PHP_INT_MAX;
        foreach ($d as $row) {
            $t = (int)$row[0];
            $cuNhat = min($cuNhat, $t);
            if (isset($row[8]) && $row[8] === '0') continue;      // nến chưa đóng
            if ($t < $tsTu || $t > $tsDen) continue;
            $ra[] = [$t, (float)$row[1], (float)$row[2], (float)$row[3], (float)$row[4]];
        }
        if ($cuNhat <= $tsTu || $cuNhat === PHP_INT_MAX) break;   // đã lùi đủ xa
        $moc = $cuNhat;
    }
    /* Một trang phân trang hỏng giữa chừng = tập nến THIẾU ĐOẠN. Chấm trên đó sẽ
       ra kết quả sai và ĐÓNG SỔ VĨNH VIỄN, không bao giờ sửa lại được. Thà trả
       null để lượt cron sau thử lại còn hơn ghi một kết quả sai tự tin. */
    if ($loi) return null;
    usort($ra, fn($a, $b) => $a[0] <=> $b[0]);                    // cũ -> mới
    return $ra;
}

function getJson(string $url): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_USERAGENT      => 'so-lenh-cham/1.0',
    ]);
    $r = curl_exec($ch);
    $ma = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($r === false || $ma !== 200) return null;
    $j = json_decode($r, true);
    return is_array($j) ? $j : null;
}

/* Luôn kèm AND kq='mo' rồi kiểm affected_rows — chống chấm hai lần nếu vì lý do
   nào đó có hai tiến trình lọt qua được khoá. */
function luuKetQua(PDO $pdo, int $id, array $r): void
{
    $st = $pdo->prepare(
        "UPDATE keo SET kq = :kq, kq_dong = :kq_dong, gia_ra = :gia_ra,
                        ts_ra = :ts_ra, mfe_bp = :mfe, mae_bp = :mae,
                        cham_luc = CURRENT_TIMESTAMP(3)
         WHERE id = :id AND kq = 'mo'"
    );
    $st->execute([
        ':kq' => $r['kq'], ':kq_dong' => $r['kq_dong'],
        ':gia_ra' => $r['gia_ra'] === null ? null : (string)$r['gia_ra'],
        ':ts_ra' => $r['ts_ra'], ':mfe' => $r['mfe_bp'], ':mae' => $r['mae_bp'],
        ':id' => $id,
    ]);
    if ($st->rowCount() === 0) echo "  ! id=$id da bi cham boi tien trinh khac\n";
}
