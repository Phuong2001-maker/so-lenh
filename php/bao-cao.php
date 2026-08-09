<?php
/* =====================================================================
   bao-cao.php — trang đọc số. Không có trang này thì DB chỉ là cái hố ghi.

   QUY TẮC HIỂN THỊ, đừng gỡ:
   - Không bao giờ in tỷ lệ thắng mà không in mốc so sánh baseline bên cạnh.
     Vì chốt lời và cắt lỗ đều là TƯỜNG với khoảng cách bất đối xứng, thắng 60%
     khi baseline là 65% nghĩa là TỆ HƠN tung đồng xu.
   - Luôn in cỡ mẫu và khoảng tin cậy Wilson. Không có nó thì sau 30 kèo sẽ lại
     chỉnh trọng số — đúng vòng lặp đã đẻ ra 4 chốt chặn hiện tại.
   ===================================================================== */
declare(strict_types=1);
require __DIR__ . '/config.php';

$pdo = db();
$q = fn(string $sql) => $pdo->query($sql)->fetchAll();

/* Wilson 95% — khoảng tin cậy cho tỷ lệ, đúng cả khi n nhỏ (khác Wald) */
function wilson(int $k, int $n): array {
    if ($n === 0) return [0.0, 1.0];
    $p = $k / $n; $z = 1.96; $z2 = $z * $z;
    $mau = 1 + $z2 / $n;
    $tam = $p + $z2 / (2 * $n);
    $bien = $z * sqrt($p * (1 - $p) / $n + $z2 / (4 * $n * $n));
    return [max(0, ($tam - $bien) / $mau), min(1, ($tam + $bien) / $mau)];
}
function pc(?float $x, int $d = 1): string { return $x === null ? '—' : number_format($x * 100, $d) . '%'; }
function so(?float $x, int $d = 2): string { return $x === null ? '—' : number_format($x, $d); }
function h(?string $x): string { return htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8'); }

$tong = $q("SELECT
      nhom,
      COUNT(*) n, SUM(thang01) dung,
      AVG(thang01) tl, AVG(p_baseline) base, AVG(rr_du_kien) rr,
      SUM(r_sau_phi) tongR, AVG(r_sau_phi) kv,
      AVG(thang01_dong) tl_dong, AVG(chot_bp) chot_bp, AVG(spread_bp) spread_bp
    FROM v_keo WHERE thang01 IS NOT NULL GROUP BY nhom");

$trangThai = $q("SELECT kq, COUNT(*) n FROM keo GROUP BY kq");
$tongDong  = (int)$pdo->query("SELECT COUNT(*) FROM keo")->fetchColumn();

$bo = function (string $nhan, string $bieuThuc, string $dieuKien = '1') use ($q) {
    return $q("SELECT $bieuThuc AS o, COUNT(*) n, AVG(thang01) tl, AVG(p_baseline) base,
                      SUM(r_sau_phi) tongR
               FROM v_keo WHERE thang01 IS NOT NULL AND chan = 0 AND $dieuKien
               GROUP BY o ORDER BY n DESC");
};
$theoTin  = $bo('tin', "CASE WHEN vao_trust>=55 THEN 'tin >= 55' WHEN vao_trust>=35 THEN 'tin 35-54' ELSE 'tin < 35' END");
$theoOkx  = $bo('okx', "CASE WHEN vao_ty_le_okx>=0.7 THEN 'OKX >= 70%' WHEN vao_ty_le_okx>=0.4 THEN 'OKX 40-69%' ELSE 'OKX < 40%' END", 'vao_ty_le_okx IS NOT NULL');
$theoMay  = $bo('may', "CASE WHEN tuoi_may<300 THEN 'may nguoi (<5 phut)' ELSE 'may da am' END");
$theoS    = $bo('s',   "CASE WHEN ABS(s)>=60 THEN '|S| >= 60' WHEN ABS(s)>=40 THEN '|S| 40-59' ELSE '|S| 25-39' END");
$theoChan = $q("SELECT chan AS o, COUNT(*) n, AVG(thang01) tl, AVG(p_baseline) base, SUM(r_sau_phi) tongR
                FROM v_keo WHERE thang01 IS NOT NULL GROUP BY chan ORDER BY chan");
$nhapNhang = $q("SELECT
      AVG(CASE WHEN kq='thang' THEN 1 WHEN kq='thua' THEN 0 END) a,
      AVG(CASE WHEN kq='thang' THEN 1 WHEN kq IN ('thua','nhap_nhang') THEN 0 END) b,
      AVG(CASE WHEN kq IN ('thang','nhap_nhang') THEN 1 WHEN kq='thua' THEN 0 END) c,
      SUM(kq='nhap_nhang') nn
    FROM v_keo WHERE chan = 0")[0] ?? null;

$nThat = 0; foreach ($tong as $r) if ($r['nhom'] === 'that') $nThat = (int)$r['n'];
?><!doctype html><html lang="vi"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Báo cáo tỷ lệ thắng — Sổ Lệnh Gộp</title>
<style>
:root{--bg:#0a0d12;--panel:#141922;--line:#242c38;--text:#e8ecf1;--dim:#7f8996;--dim2:#5c6674;
      --green:#0ecb81;--red:#f6465d;--amber:#e2b93b;--mono:ui-monospace,Consolas,monospace}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font:14px/1.55 system-ui,'Segoe UI',sans-serif;
     padding:20px 16px 40px;max-width:1080px;margin:0 auto}
h1{font-size:19px;margin-bottom:4px}
h2{font-size:13px;letter-spacing:.3px;margin:22px 0 8px;color:var(--text)}
.sub{color:var(--dim);font-size:12px;margin-bottom:16px}
.canh{background:rgba(226,185,59,.08);border:1px solid rgba(226,185,59,.25);color:var(--amber);
      border-radius:10px;padding:11px 14px;font-size:12.5px;line-height:1.65;margin-bottom:16px}
table{width:100%;border-collapse:collapse;background:var(--panel);border:1px solid var(--line);
      border-radius:10px;overflow:hidden;font-size:12.5px}
th{background:#191f29;color:var(--dim2);font-size:10px;letter-spacing:.4px;text-align:right;
   padding:8px 10px;font-weight:600}
th:first-child,td:first-child{text-align:left}
td{padding:7px 10px;text-align:right;border-top:1px solid rgba(255,255,255,.04);font-family:var(--mono)}
td:first-child{font-family:system-ui,sans-serif}
.up{color:var(--green)}.dn{color:var(--red)}.dim{color:var(--dim2)}
.note{color:var(--dim2);font-size:11.5px;margin-top:6px;line-height:1.6}
</style></head><body>

<h1>Báo cáo tỷ lệ thắng</h1>
<div class="sub"><?= $tongDong ?> kèo đã ghi · cập nhật <?= date('d/m/Y H:i') ?> ·
  trạng thái: <?php foreach ($trangThai as $t) echo h($t['kq']) . ' ' . (int)$t['n'] . '  '; ?></div>

<?php if ($nThat < 800): ?>
<div class="canh"><b>⚠ CHƯA ĐỦ MẪU ĐỂ KẾT LUẬN.</b>
  Mới có <?= $nThat ?> kèo thật đã chấm; cần khoảng <b>800 kèo độc lập</b> để phân biệt
  55% với 50% ở mức tin cậy 95%. Mọi con số dưới đây đọc để <i>phát hiện lỗi thô</i>
  (bao nhiêu % kèo có R:R &lt; 1, bao nhiêu % sinh khi máy chưa ấm), <b>không phải để
  chỉnh trọng số</b>. Chỉnh trọng số sớm chính là vòng lặp đã đẻ ra 4 chốt chặn hiện tại.
</div>
<?php endif; ?>

<h2>TỔNG — nhóm "thật" là kèo qua hết 4 chốt chặn, "bóng" là kèo bị chặn</h2>
<table><tr><th>Nhóm</th><th>n</th><th>Đúng</th><th>Tỷ lệ thắng</th><th>Baseline</th>
<th>Hơn ngẫu nhiên</th><th>Wilson 95%</th><th>R:R TB</th><th>Tổng R sau phí</th><th>KV/kèo</th>
<th>Chốt (bp)</th><th>Spread (bp)</th></tr>
<?php foreach ($tong as $r):
  $n=(int)$r['n']; $k=(int)$r['dung']; [$lo,$hi]=wilson($k,$n);
  $delta = $r['tl']!==null && $r['base']!==null ? (float)$r['tl']-(float)$r['base'] : null; ?>
<tr><td><?= h($r['nhom']) ?></td><td><?= $n ?></td><td><?= $k ?></td>
<td><?= pc((float)$r['tl']) ?></td><td class="dim"><?= pc((float)$r['base']) ?></td>
<td class="<?= $delta>0?'up':'dn' ?>"><b><?= $delta===null?'—':($delta>0?'+':'').number_format($delta*100,1).'%' ?></b></td>
<td class="dim"><?= pc($lo) ?> – <?= pc($hi) ?></td>
<td><?= so((float)$r['rr']) ?></td>
<td class="<?= (float)$r['tongR']>0?'up':'dn' ?>"><?= so((float)$r['tongR']) ?></td>
<td class="<?= (float)$r['kv']>0?'up':'dn' ?>"><?= so((float)$r['kv'],3) ?></td>
<td class="dim"><?= so((float)$r['chot_bp'],0) ?></td><td class="dim"><?= so((float)$r['spread_bp'],1) ?></td></tr>
<?php endforeach; if(!$tong): ?><tr><td colspan="12" class="dim">Chưa có kèo nào được chấm.</td></tr><?php endif; ?>
</table>
<div class="note"><b>Hơn ngẫu nhiên</b> = tỷ lệ thắng − baseline. Đây là con số duy nhất đáng đọc:
baseline là xác suất chạm chốt trước nếu giá đi hoàn toàn ngẫu nhiên, tính từ khoảng cách
tới chốt và tới cắt lỗ. Số này ≤ 0 nghĩa là máy không hơn gì tung đồng xu.
Cột <b>Tổng R sau phí</b> mới là thứ quyết định lãi lỗ, không phải tỷ lệ thắng.</div>

<h2>ĐỘ NHẠY THEO QUY ƯỚC CHẤM</h2>
<table><tr><th>Nhóm</th><th>Chạm (bóng nến)</th><th>Đóng nến vượt hẳn</th><th>Chênh</th></tr>
<?php foreach ($tong as $r):
  $d = $r['tl']!==null && $r['tl_dong']!==null ? (float)$r['tl']-(float)$r['tl_dong'] : null; ?>
<tr><td><?= h($r['nhom']) ?></td><td><?= pc((float)$r['tl']) ?></td><td><?= pc($r['tl_dong']===null?null:(float)$r['tl_dong']) ?></td>
<td class="<?= abs((float)$d)>0.05?'dn':'dim' ?>"><?= $d===null?'—':number_format($d*100,1).'%' ?></td></tr>
<?php endforeach; ?></table>
<div class="note">Chênh lệch quá <b>5 điểm phần trăm</b> nghĩa là kết luận phụ thuộc vào quy ước
chấm chứ không phụ thuộc vào máy — lúc đó đừng tin con số nào cả.</div>

<?php if ($nhapNhang): ?>
<h2>BA CON SỐ BIÊN CHO NHÓM NHẬP NHẰNG (<?= (int)$nhapNhang['nn'] ?> kèo)</h2>
<table><tr><th>Loại bỏ nhập nhằng</th><th>Tính là thua</th><th>Tính là thắng</th></tr>
<tr><td><?= pc($nhapNhang['a']===null?null:(float)$nhapNhang['a']) ?></td>
<td><?= pc($nhapNhang['b']===null?null:(float)$nhapNhang['b']) ?></td>
<td><?= pc($nhapNhang['c']===null?null:(float)$nhapNhang['c']) ?></td></tr></table>
<div class="note">Nhập nhằng = cả chốt lời lẫn cắt lỗ rơi vào cùng một cây nến 1m, không biết
cái nào đến trước. Ba con số chênh nhau quá 5 điểm thì kết luận vô nghĩa — <b>đừng chọn con đẹp nhất</b>.</div>
<?php endif; ?>

<?php
$bang = function (string $tieuDe, array $rows, string $cot, string $ghi) {
  echo '<h2>' . h($tieuDe) . '</h2><table><tr><th>' . h($cot) . '</th><th>n</th><th>Tỷ lệ thắng</th>'
     . '<th>Baseline</th><th>Hơn ngẫu nhiên</th><th>Tổng R</th></tr>';
  foreach ($rows as $r) {
    $d = $r['tl']!==null && $r['base']!==null ? (float)$r['tl']-(float)$r['base'] : null;
    printf('<tr><td>%s</td><td>%d</td><td>%s</td><td class="dim">%s</td>'
         . '<td class="%s"><b>%s</b></td><td class="%s">%s</td></tr>',
      h((string)$r['o']), (int)$r['n'], pc($r['tl']===null?null:(float)$r['tl']),
      pc($r['base']===null?null:(float)$r['base']),
      $d>0?'up':'dn', $d===null?'—':($d>0?'+':'').number_format($d*100,1).'%',
      (float)$r['tongR']>0?'up':'dn', so((float)$r['tongR']));
  }
  if (!$rows) echo '<tr><td colspan="6" class="dim">chưa có dữ liệu</td></tr>';
  echo '</table><div class="note">' . $ghi . '</div>';
};

$bang('4 CHỐT CHẶN ĐANG CỨU HAY ĐANG CẮT MẤT TIỀN?', $theoChan, 'chan (bitmask)',
  'chan = 0 là kèo đã qua hết 4 chốt. Các giá trị khác là kèo <b>bị chặn</b> nhưng vẫn được ghi và chấm — '
  . 'đây là nhóm đối chứng duy nhất. Nếu nhóm bị chặn lại có "hơn ngẫu nhiên" cao hơn nhóm thật thì '
  . 'chốt chặn đang <b>cắt mất kèo thắng</b>. Bit 1=|S|&lt;25, 2=dưới 2 tín hiệu, 4=flow ngược, 8=bão hoà không xác nhận.');

$bang('BỘ CHẤM TIN CẬY TƯỜNG CÓ TÁC DỤNG THẬT KHÔNG?', $theoTin, 'Dải điểm tin',
  'Nếu ba dải cho kết quả như nhau thì công thức +15/+8/−30 chỉ là trang trí, phải chỉnh lại hoặc bỏ.');

$bang('CHUYỂN SANG SỔ OKX CÓ ĐÚNG KHÔNG?', $theoOkx, 'Tỷ lệ tiền nằm trên OKX',
  'Tường 90% nằm trên OKX phải hành xử khác hẳn tường chỉ 14% khi giá chạm tới. '
  . 'Nếu không khác thì giả định nền của lần đổi sang sổ riêng OKX là sai.');

$bang('"KÈO MÙ" — máy vừa bật thì mọi tường còn ở mốc tin 30', $theoMay, 'Trạng thái máy',
  'Nếu nhóm "máy nguội" tệ hơn rõ rệt thì nên chặn không phát kèo trong 5 phút đầu của mỗi coin.');

$bang('NGƯỠNG |S| = 25 CÓ ĐẶT ĐÚNG CHỖ KHÔNG?', $theoS, 'Dải điểm',
  'Nếu dải 25–39 không hơn gì ngẫu nhiên còn dải ≥ 60 thì có, ngưỡng nên nâng lên.');
?>

<div class="note" style="margin-top:26px">
  Chi phí lấy từ bảng <b>phi</b> (mặc định taker 5bp/chiều + trượt giá 2bp — <b>giả định, chưa đo thực tế</b>).
  Đổi bậc phí thì thêm một dòng vào bảng đó, mọi con số tự tính lại, không phải sửa dữ liệu cũ.
  Đây là sổ giấy: chưa có lệnh thật nào nên "Tổng R sau phí" là <b>trần trên</b>, thực tế sẽ thấp hơn.
</div>
</body></html>
