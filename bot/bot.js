'use strict';
/* =====================================================================
   BOT — chạy 24/24 trên server, KHÔNG cần trình duyệt nào mở.

   Cách chạy:
     node --experimental-websocket bot.js
   hoặc dùng ./chay.sh (đã kèm nohup + đường dẫn node + ghi bot.log).

   ---------------------------------------------------------------------
   TẠI SAO NẠP index.html CHỨ KHÔNG CHÉP CODE SANG ĐÂY

   Toàn bộ thuật toán (taoEngine, aggregate, updateWalls, wallTrust, analyze,
   bestWall, pickLevels, keoCua, sinhKeo) nằm trong khối <script> của
   index.html. File này ĐỌC đúng khối đó rồi chạy, nên chỉ tồn tại MỘT bản
   thuật toán duy nhất.

   Nếu chép sang đây thành bản thứ hai thì: (a) sửa một bên quên bên kia,
   (b) sai lệch âm thầm giữa số bot ghi vào DB và số trang xem hiển thị,
   (c) dữ liệu trước/sau lần chép không so sánh được với nhau nữa.

   Chi phí của cách này gần bằng 0: cả 1500 dòng chỉ dùng ĐÚNG 10 chỗ DOM
   (4 innerHTML, 3 textContent, document.title, getElementById,
   querySelectorAll) nên bản giả lập DOM dưới đây chỉ cần vài dòng. Mà bot
   còn không vẽ gì (LA_BOT bỏ hẳn nhịp vẽ), nên nó gần như không bị gọi.

   ---------------------------------------------------------------------
   DÙNG eval CHỨ KHÔNG DÙNG MODULE vm

   vm.createContext tạo một realm khác, khiến mọi phép `instanceof` và
   `Array.isArray` xuyên realm có thể sai âm thầm — đúng loại lỗi tệ nhất
   cho một tiến trình chạy nền không ai xem. Indirect eval `(0,eval)(...)`
   chạy ngay trong realm này nên không có vấn đề đó. Mã được nạp là mã của
   chính chủ dự án, không phải mã lạ tải từ mạng.
   ===================================================================== */

const fs   = require('fs');
const path = require('path');

/* ---- đường dẫn: sửa 2 dòng này nếu chuyển máy ---- */
const GOC_WEB = process.env.GOC_WEB
  || '/home/buwsofujhosting/domains/zewvir85.hiteckqualityconstruction.com.au';
const FILE_APP   = path.join(GOC_WEB, 'index.html');
const FILE_ANH   = path.join(GOC_WEB, 'trangthai.json');
const FILE_TOKEN = path.join(__dirname, 'token.txt');
const FILE_GHIM  = path.join(__dirname, 'ghim.txt');

/* URL cham.php — dùng cho việc CHẤM SỔ DỰ PHÒNG, xem mục goiCham() ở dưới. */
const URL_CHAM = process.env.URL_CHAM
  || 'https://zewvir85.hiteckqualityconstruction.com.au/php/cham.php';

const NHIP_ANH  = 2000;    /* ghi ảnh trạng thái — đúng bằng nhịp phân tích */
const NHIP_GHIM = 30000;   /* đọc lại ghim.txt */
const NHIP_LOG  = 60000;   /* in một dòng sức khoẻ vào bot.log */
const NHIP_CHAM = 60000;   /* gọi cham.php dự phòng */

/* ---------------------------------------------------------------- ghi log */
const gio = () => new Date().toISOString().replace('T', ' ').slice(0, 19);
const ghi = (...a) => console.log(gio(), ...a);

/* XOAY LOG. `chay.sh` dùng `>> bot.log` không giới hạn kích thước, và bộ ghi ngoại lệ
   in nguyên stack mỗi lần. Một lỗi lặp theo nhịp 2 giây là ~43.000 stack/ngày, hàng
   trăm MB — đầy quota đĩa thì `writeFileSync` trangthai.json thất bại, MySQL ngừng
   ghi, cron PHP lỗi. Tức một lỗi lẻ lại hạ cả hệ thống, đúng điều đang cố chống. */
const FILE_LOG = path.join(__dirname, 'bot.log');
const TRAN_LOG = 20 * 1024 * 1024;
function xoayLog(){
  try{
    if (fs.statSync(FILE_LOG).size > TRAN_LOG){
      try { fs.unlinkSync(FILE_LOG + '.1'); } catch(e){}
      fs.renameSync(FILE_LOG, FILE_LOG + '.1');
      /* Tiến trình vẫn giữ file descriptor cũ nên phải tự mở lại luồng ghi. */
      const fd = fs.openSync(FILE_LOG, 'a');
      fs.closeSync(fd);
      ghi('(da xoay bot.log, ban cu o bot.log.1)');
    }
  }catch(e){}
}

/* GOM LỖI TRÙNG: giữ dấu vân tay của từng lỗi, chỉ in lại sau 60 giây, và luôn in
   kèm tổng số lần. Không gom thì một lỗi lặp vừa làm đầy đĩa vừa che mọi dòng khác. */
const soLan = new Map();
function ghiLoi(nhan, e){
  const van = nhan + '|' + String((e && e.message) || e).slice(0, 120);
  const g = soLan.get(van) || {n: 0, lucIn: 0};
  g.n++;
  const now = Date.now();
  if (now - g.lucIn > 60000){
    g.lucIn = now;
    ghi(`!! ${nhan} (x${g.n}):`, (e && e.stack) || e);
    xoayLog();
  }
  soLan.set(van, g);
  return g.n;
}

/* Tiến trình chạy nền tuyệt đối KHÔNG được chết vì một lỗi lẻ: một cú reject không
   bắt trong lúc gọi REST là mất cả đêm dữ liệu. Ghi lại rồi chạy tiếp.
   NHƯNG chạy tiếp với trạng thái đã hỏng còn tệ hơn chết CÓ DẤU HIỆU — nên nếu ngoại
   lệ dồn dập thì thoát hẳn để cron (mỗi 5 phút) bật lại với trạng thái sạch. */
let ngoaiLeGanDay = [];
process.on('unhandledRejection', e => ghiLoi('promise loi', e));
process.on('uncaughtException',  e => {
  ghiLoi('ngoai le', e);
  const now = Date.now();
  ngoaiLeGanDay = ngoaiLeGanDay.filter(t => now - t < 60000);
  ngoaiLeGanDay.push(now);
  if (ngoaiLeGanDay.length > 20){
    ghi('!! QUA 20 NGOAI LE TRONG 60 GIAY — thoat de cron bat lai voi trang thai sach');
    process.exit(1);
  }
});

/* ---------------------------------------------------------- kiểm môi trường */
if (typeof WebSocket !== 'function'){
  ghi('!! THIEU WebSocket. Node 20 can co --experimental-websocket:');
  ghi('   node --experimental-websocket bot.js');
  process.exit(1);
}
if (typeof fetch !== 'function'){ ghi('!! THIEU fetch. Can Node >= 18.'); process.exit(1); }

/* ------------------------------------------------------------------- token */
let token = (process.env.API_TOKEN || '').trim();
if (!token){
  try { token = fs.readFileSync(FILE_TOKEN, 'utf8').trim(); }
  catch(e){
    ghi('!! Khong doc duoc token. Tao file', FILE_TOKEN, 'chua dung chuoi API_TOKEN');
    ghi('   trong php/config.php (mot dong, khong dau ngoac).');
    process.exit(1);
  }
}
if (!token){ ghi('!! token rong.'); process.exit(1); }

/* -------------------------------------------------- bản giả lập DOM tối thiểu
   Bot không vẽ, nên đây chỉ là lưới an toàn: nếu sau này có ai thêm một dòng
   chạm DOM ngoài nhánh vẽ thì nó không làm sập tiến trình. */
const oNhu = () => ({ set innerHTML(v){}, set textContent(v){}, style: {}, dataset: {} });
globalThis.document = {
  getElementById: () => oNhu(),
  querySelectorAll: () => [],
  set title(v){}, get title(){ return ''; }
};

/* -------------------------------------------------------- cờ cho index.html */
globalThis.__BOT__ = true;
globalThis.__API_TOKEN__ = token;

/* KHONG_GHI=1 -> chạy thử: làm đủ mọi việc nhưng không gửi kèo lên DB.
   Dùng lần đầu trên máy mới, để nếu có gì sai thì không đầu độc bảng keo. */
const CHAY_THU = process.env.KHONG_GHI === '1';
globalThis.__KHONG_GHI__ = CHAY_THU;
if (CHAY_THU) ghi('*** CHAY THU (KHONG_GHI=1) — khong gui keo len DB ***');

/* ------------------------------------------------------- nạp khối <script> */
let html;
try { html = fs.readFileSync(FILE_APP, 'utf8'); }
catch(e){ ghi('!! khong doc duoc', FILE_APP, '-', e.message); process.exit(1); }

const m = html.match(/<script>([\s\S]*)<\/script>/);
if (!m){ ghi('!! khong tim thay khoi <script> trong index.html'); process.exit(1); }

ghi('nap index.html:', (m[1].length / 1024).toFixed(0), 'KB script');
try { (0, eval)(m[1]); }
catch(e){ ghi('!! loi khi chay script cua app:', e.stack || e); process.exit(1); }

const API = globalThis.__botAPI;
if (!API){
  ghi('!! index.html khong xuat __botAPI. Ban index.html qua cu?');
  ghi('   Can dong: if (LA_BOT) globalThis.__botAPI = {...}');
  process.exit(1);
}
ghi('bot da chay. Ghi anh trang thai ->', FILE_ANH);

/* --------------------------------------------------- ghi ảnh trạng thái
   Ghi vào file tạm rồi rename: rename trên cùng phân vùng là nguyên tử, nên
   trang xem không bao giờ đọc phải một file JSON đang viết dở. Ghi trực tiếp
   thì cứ 2 giây lại có một khoảnh khắc file bị cắt giữa, và trình duyệt sẽ
   nuốt một lỗi JSON không rõ nguyên nhân. */
const FILE_TAM = FILE_ANH + '.tmp';
let anhLoi = 0;

function ghiAnh(){
  try{
    const o = API.anhTrangThai();
    /* Gắn sức khoẻ bộ chấm vào ảnh: đây là thứ DUY NHẤT cho phép phát hiện
       "chấm sổ đã chết" mà chỉ cần mở link, không cần SSH hay SQL — quan trọng
       khi chủ dự án đi vắng nhiều ngày. bot.js gắn ở đây thay vì để index.html
       tự làm, vì index.html không biết gì về tiến trình bot. */
    o.cham = {ok: chamStat.ok, boQua: chamStat.boQua, loi: chamStat.loi,
              lucOk: chamStat.lucOk, loiCuoi: chamStat.loiCuoi};
    const j = JSON.stringify(o);
    fs.writeFileSync(FILE_TAM, j);
    fs.renameSync(FILE_TAM, FILE_ANH);
    anhLoi = 0;
  }catch(e){ anhLoi = ghiLoi('ghi anh loi', e); }
}

/* ------------------------------------------------- CHẤM SỔ DỰ PHÒNG
   Bot tự gọi `cham.php` mỗi phút, SONG SONG với cron.

   VÌ SAO CẦN: hiện cron đỡ bot (cron bật lại bot nếu bot chết), nhưng KHÔNG AI
   ĐỠ CRON. Nếu crontab bị ghi lại, dịch vụ cron của hosting chết, hay lệnh bắt
   đầu fail âm thầm (hosting nâng cấp PHP làm `php` rơi khỏi PATH hẹp của cron —
   kiểu phổ biến nhất), thì: bot vẫn ghi kèo bình thường, trang xem vẫn xanh với
   "dữ liệu 1s trước", mà KHÔNG AI CHẤM. Mọi kèo kẹt `kq='mo'` vô thời hạn. Với
   một đợt treo hai tuần, đó là hai tuần dữ liệu KHÔNG CÓ NHÃN — mất trắng đúng
   mục đích thu thập, và không backfill lại được vì nến 1m của OKX chỉ giữ 24h.

   Bot là tiến trình chạy dài ĐỘC LẬP với cron, nên nó bù đúng vào lỗ đó. Hai bên
   đỡ nhau chéo: cron đỡ bot, bot đỡ cron.

   CHẠY SONG SONG AN TOÀN: `cham.php` mở đầu bằng `GET_LOCK('cham_keo', 0)` —
   timeout 0 nên gặp tiến trình khác đang chấm là nó in "dang co tien trinh khac
   chay" rồi thoát ngay, không chờ, không ghi đè. Thêm nữa mọi câu UPDATE đều kèm
   `AND kq='mo'` rồi kiểm `rowCount`, nên không thể chấm trùng một kèo.

   Dùng header `X-Token` chứ không `?token=`: query string bị ghi vào access log
   của web server, còn header thì không. */
let dangCham = false;
const chamStat = {ok: 0, boQua: 0, loi: 0, lucOk: 0, loiCuoi: ''};

async function goiCham(){
  if (dangCham) return;              /* lượt trước chưa xong — đừng dồn */
  dangCham = true;
  const huy = new AbortController();
  const hetGio = setTimeout(() => huy.abort(), 50000);
  try{
    const r = await fetch(URL_CHAM, {signal: huy.signal, headers: {'X-Token': token}});
    const t = await r.text();
    if (!r.ok){
      chamStat.loi++;
      chamStat.loiCuoi = 'HTTP ' + r.status;
      ghiLoi('cham loi', new Error('HTTP ' + r.status + ' — token lech?'));
    } else if (t.includes('tien trinh khac')){
      /* Cron đang chấm ngay lúc này. Đây là dấu hiệu KHOẺ, không phải lỗi:
         nghĩa là đường chấm chính vẫn sống nên bot không cần làm gì. */
      chamStat.boQua++; chamStat.lucOk = Date.now(); chamStat.loiCuoi = '';
    } else {
      chamStat.ok++; chamStat.lucOk = Date.now(); chamStat.loiCuoi = '';
    }
  }catch(e){
    chamStat.loi++;
    chamStat.loiCuoi = String((e && e.message) || e);
    ghiLoi('cham loi', e);
  }finally{ clearTimeout(hetGio); dangCham = false; }
}

/* ------------------------------------------------------------ ghim từ file
   Ghim đặt bằng file vì trang xem là chỉ-đọc: cho bấm trên trang thì phải có
   endpoint điều khiển kèm xác thực, tức phải nhét token vào trang công khai.
   Sửa file qua File Manager của 1Panel, mỗi dòng một mã coin (vd PEOPLE). */
let ghimCu = '';
function napGhim(){
  let s = '';
  try { s = fs.readFileSync(FILE_GHIM, 'utf8'); } catch(e){ s = ''; }
  if (s === ghimCu) return;
  const ds = s.split('\n').map(x => x.replace(/#.*/, '').trim().toUpperCase()).filter(Boolean);
  /* Chỉ ghi nhận nội dung ĐÃ ÁP THÀNH CÔNG. Gán ghimCu trước khi gọi thì một lần
     dongBoGhim ném lỗi là ghim đó không bao giờ được thử lại. */
  try {
    API.dongBoGhim(ds);
    ghimCu = s;
    ghi('ghim:', ds.length ? ds.join(', ') : '(khong co)');
  } catch(e){ ghiLoi('dongBoGhim loi', e); }
}

/* ----------------------------------------------------------- dòng sức khoẻ
   Mọi trạng thái "bot sống nhưng không làm gì" PHẢI kêu, và phải kêu bằng tiền tố
   `!!` để grep được trong bot.log. Một con số 0 lẫn giữa dòng thống kê thì không ai
   đọc ra — đó chính là định nghĩa của hỏng im lặng. */
function inSucKhoe(){
  const syms = Object.keys(API.engines);
  let live = 0, am = 0, chet = 0;
  for (const s of syms){
    const E = API.engines[s];
    for (const k in E.conns) if (E.conns[k].state === 'live') live++;
    const P = E.pred || {};
    if (P.ready && P.warmLeft > 0) am++;
    else if (!P.ready) chet++;
  }
  const L = API.logStat, S = API.scan;
  const canh = [];
  if (!syms.length) canh.push('KHONG CO COIN NAO DUOC THEO DOI');
  if (S.lucQuet && Date.now() - S.lucQuet > 180000)
    canh.push(`may quet OKX im ${Math.round((Date.now() - S.lucQuet) / 1000)}s`);
  if (!S.lucQuet) canh.push('may quet OKX CHUA CHAY LAN NAO');
  if (S.loi) canh.push(`quet loi x${S.loi} (${S.loiCuoi})`);
  if (chet) canh.push(`${chet} coin MAT SO`);
  if (L.mat) canh.push(`${L.mat} keo BI NEM khoi hang doi`);

  /* Đếm trực tiếp từ engines, KHÔNG gọi lại anhTrangThai(): hàm đó dựng cả object
     ảnh (~16 KB) và còn ghi vào logStat.cho — gọi thêm một lần chỉ để lấy một con số
     là việc thừa, và là một chỗ có tác dụng lề không cần thiết. */
  let tn = 0;
  for (const s of syms) if (!API.engines[s].market.ranges) tn++;
  if (tn) canh.push(`${tn} coin THIEU NEN 48h — khong sinh keo`);

  /* Socket thanh ly: nguon duy nhat cua sLiq (trong so 0.20). Nam ngoai moi engine
     nen khong bo canh chung theo-san nao che no — phai theo doi rieng o day. */
  const Q = API.trangThaiLq ? API.trangThaiLq() : null;
  if (Q){
    if (Q.loiCuoi) canh.push(`socket thanh ly: ${Q.loiCuoi}`);
    if (!Q.lastMsg) canh.push('socket thanh ly CHUA KET NOI');
    else if (Date.now() - Q.lastMsg > 90000)
      canh.push(`socket thanh ly im ${Math.round((Date.now() - Q.lastMsg) / 1000)}s`);
  }

  /* Bộ chấm: quá 10 phút không có lượt nào thành công nghĩa là CẢ cron LẪN đường
     dự phòng của bot đều chết — kèo sẽ kẹt `mo` và dữ liệu thành vô nhãn. */
  if (chamStat.loiCuoi) canh.push(`cham so loi: ${chamStat.loiCuoi}`);
  if (chamStat.lucOk && Date.now() - chamStat.lucOk > 600000)
    canh.push(`CHAM SO IM ${Math.round((Date.now() - chamStat.lucOk) / 60000)} phut`);

  ghi(`${syms.length} coin · ${live}/${syms.length * 4} san live · ${am} chua am`
    + (chet ? ` · ${chet} mat so` : '')
    + ` · log ${L.gui} gui/${L.loi} loi${L.cho ? '/' + L.cho + ' cho' : ''}`
    + (L.mat ? `/${L.mat} MAT` : '')
    + ` · cham ${chamStat.ok}ok/${chamStat.boQua}cron/${chamStat.loi}loi`
    + ` · RAM ${Math.round(process.memoryUsage().rss / 1048576)}MB`);
  if (canh.length){ ghi('!! CANH BAO:', canh.join(' | ')); xoayLog(); }
}

/* ------------------------------------------------------------------- chạy */
napGhim();
ghiAnh();
const t1 = setInterval(ghiAnh, NHIP_ANH);
const t2 = setInterval(napGhim, NHIP_GHIM);
const t3 = setInterval(inSucKhoe, NHIP_LOG);

/* Lượt chấm đầu lùi 45 giây: (a) chờ warmup xong đã, (b) lệch pha với cron chạy
   ở giây :00 nên hai bên ít giành khoá của nhau — không phải vấn đề đúng đắn, chỉ
   là bớt lượt chạy vô ích. */
let t4 = null;
const t5 = setTimeout(() => { goiCham(); t4 = setInterval(goiCham, NHIP_CHAM); }, 45000);

/* Dọn khi bị tắt: xoá file tạm để lần chạy sau không thấy rác, và ghi một dòng
   vào log để phân biệt "bị tắt có chủ đích" với "chết bất thường" — không có
   dòng này thì sau vài ngày không cách nào biết bot dừng vì sao. */
for (const sig of ['SIGINT', 'SIGTERM']){
  process.on(sig, () => {
    ghi('nhan', sig, '- dung bot.');
    clearInterval(t1); clearInterval(t2); clearInterval(t3);
    clearTimeout(t5); if (t4) clearInterval(t4);
    try { fs.unlinkSync(FILE_TAM); } catch(e){}
    process.exit(0);
  });
}
