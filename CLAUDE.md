# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## 🎭 VAI TRÒ khi làm việc trong repo này

Chủ dự án yêu cầu: mọi phiên làm việc trong repo này, bạn đóng vai **Kiến trúc sư Hệ thống kiêm Chuyên gia AI về Crypto**. Cụ thể nghĩa là:

- **Nghĩ ở tầng hệ thống trước, tầng code sau.** Trước khi sửa một hàm, hỏi việc đó ảnh hưởng gì tới luồng dữ liệu, tới tầng lưu trữ, tới khả năng đo lường về sau. Đề xuất kiến trúc kèm đánh đổi, không chỉ vá chỗ được hỏi.
- **Nói thẳng khi phương án được yêu cầu không phải phương án tốt nhất**, kèm lý do và lựa chọn thay thế — rồi vẫn làm theo quyết định cuối cùng của chủ dự án.
- **Chuẩn mực định lượng của dân quant, không phải của dân làm web.** Mọi con số về hiệu quả dự đoán phải đi kèm cỡ mẫu, mốc so sánh và khoảng tin cậy. Không bao giờ trình bày một tỷ lệ thắng trần trụi như thể nó là kết luận.
- **Trung thực về giới hạn của thị trường.** Không hứa "độ chính xác cao" cho dự đoán giá. Thứ có thể hứa là: đo đúng, không tự lừa mình, và loại bỏ được các nguồn thiên lệch. Chỗ nào chưa đủ dữ liệu để kết luận thì nói thẳng là chưa đủ.
- Giao tiếp bằng **tiếng Việt**, thuật ngữ kỹ thuật giữ nguyên tiếng Anh khi cần.

## ⚠ QUY TẮC BẮT BUỘC — chủ dự án yêu cầu

**Cập nhật gì cũng phải ghi vào file `CLAUDE.md`, ngay trong cùng lượt làm việc**, trước khi báo cáo hoàn thành. Không chỉ code — mọi thay đổi về hướng đi, quyết định kiến trúc, phát hiện mới về dữ liệu, hay ràng buộc mới đều phải vào đây. Tối thiểu:

- Ghi một mục vào **Nhật ký thay đổi** ở cuối file (ngày + việc đã làm + lý do).
- Sửa lại Kiến trúc / số dòng / Bẫy đã gặp / Lộ trình nếu thay đổi làm chúng sai.
- Nếu phát hiện một điều đã ghi trong file này là **sai**, sửa tại chỗ chứ đừng chỉ ghi thêm ở nhật ký.

Mục đích: mở chat mới, AI đọc đúng file này là hiểu ngay dự án đang ở đâu, không phải đọc lại toàn bộ code.

---

## 📍 ĐANG LÀM DỞ ĐẾN ĐÂU (cập nhật 2026-08-09)

Đọc mục này trước, rồi mới đọc phần còn lại.

**Giai đoạn 2 (database) đã viết xong code, đang ở khâu triển khai.**

| Việc | Trạng thái |
|---|---|
| `php/config.php` | ✅ đã điền đủ 4 giá trị (DB `buwsofujhosting_coin_db`, user `buwsofujhosting_coin_user`, token 32 ký tự) |
| Chống lộ bí mật lên GitHub | ✅ `config.php` đã untrack + vào `.gitignore`, thêm `php/config.mau.php` và `php/.htaccess` |
| `API_TOKEN` trong index.html | ✅ đã điền, khớp `config.php` |
| Lược đồ MySQL trên **database MỚI** | ⚠ **phải xác nhận lại** — xem dưới |
| Tải thư mục `php/` lên hosting | ⏳ chưa |
| **`API_LOG` trong index.html** | ⛔ **CÒN TRỐNG — thứ duy nhất chặn việc lưu dữ liệu**, chờ tên miền hosting |
| Cron chạy `cham.php` | ⏳ chưa |

### ⛔ Việc tiếp theo phải làm

**1. `API_LOG` ở [index.html:149](index.html:149) vẫn là chuỗi rỗng.** `guiLog()` mở đầu bằng `if (!API_LOG ...) return;` nên app **không gửi một byte nào**. Chủ dự án đã chọn chạy app bằng `file://` từ máy tính (chỉ tải `php/` lên hosting), nên `API_LOG` phải là **URL tuyệt đối** dạng `https://<tên-miền>/php/ghi.php` — đường dẫn tương đối không dùng được với `file://`. `ORIGIN_CHO_PHEP` đã có sẵn `'null'` nên CORS thông.

Hệ quả của lựa chọn `file://`: app **không** chạy 24/7 — nó chỉ ghi khi có tab trình duyệt đang mở. Đổi lại `API_TOKEN` không bị phơi công khai. Muốn ghi liên tục thì phải có một máy để tab mở suốt.

**2. Xác nhận lược đồ đã dựng trên ĐÚNG database mới.** Ảnh chụp panel hosting cho thấy database `buwsofujhosting_coin_db` vừa được tạo, nên rất có thể `db/tao-database.sql` **chưa** chạy trên nó. Kiểm trước khi làm gì tiếp:

```sql
SHOW TABLES;                              -- phải thấy keo, phi, v_keo
SHOW COLUMNS FROM keo LIKE 'cham_luc';    -- phải có 1 dòng
```

Thiếu thì Import lại `db/tao-database.sql` qua tab **Import** (đừng dán vào tab SQL — xem bẫy CodeMirror ở nhật ký).

### Hai rủi ro hạ tầng đã lường trước

**cURL đi ra ngoài có thể bị chặn.** Hosting miễn phí hay chặn PHP gọi ra internet, mà `cham.php` bắt buộc phải gọi `www.okx.com` để kéo nến. Bị chặn thì mọi kèo kẹt trạng thái `mo` vĩnh viễn. Phải chạy tay `php cham.php` một lần để thử trước khi cài cron — in ra `0 keo dang mo` là được.

**Tần suất cron.** Gói miễn phí thường không cho chạy mỗi phút. 5–15 phút/lần vẫn dùng được, chỉ chậm hơn chứ không sai, vì `cham.php` chấm bằng nến lịch sử chứ không bằng giá tại thời điểm chạy.

### Đang chờ kết quả

Một đợt rà soát trước deploy (5 chiều: đường ống ghi, lỗi PHP, độ chính xác máy phân tích, hiệu năng 24/7, bảo mật) đã chạy trong phiên 2026-08-09 nhưng **kết quả chưa được ghi lại vào đây**. Nếu phiên mới không có kết quả đó thì nên chạy lại rà soát trước khi deploy thật.

**Đã tự kiểm chứng và OK:** ánh xạ 39 trường app gửi ↔ 39 trường `ghi.php` nhận ↔ cột trong bảng `keo` khớp hoàn toàn; các cột kết quả (`kq`, `gia_ra`, `mfe_bp`…) đúng là chỉ cron mới ghi được, client không chạm tới.

---

## Dự án là gì

**Mục tiêu cuối:** một **bot phân tích giá coin** hoàn chỉnh gồm ba phần — giao diện, cơ sở dữ liệu, và thuật toán AI ra quyết định.

**Đang ở đâu:** mới có phần giao diện + máy nhận định bằng công thức chấm điểm thủ công. Chưa có database, chưa có thuật toán học máy nào.

Hiện tại là công cụ đọc **sổ lệnh gộp 4 sàn futures USDT** (Binance, Bybit, OKX, Bitget) kèm máy nhận định tự đưa ra hướng nghiêng LONG/SHORT. Toàn bộ giao diện và comment trong code đều bằng **tiếng Việt** — giữ nguyên quy ước này khi sửa.

Điểm khác biệt so với một widget order book thường: nó **không tin sổ lệnh một cách mù quáng**. Có hẳn một bộ máy theo dõi từng "tường" (mốc tiền lớn) qua thời gian để phân biệt tiền thật với lệnh ảo treo làm cảnh.

### Lộ trình ba giai đoạn

| GĐ | Nội dung | Trạng thái |
|---|---|---|
| 1 | Giao diện + thu thập dữ liệu 4 sàn + máy nhận định thủ công | ✅ xong |
| 2 | **Database** — luật sinh kèo, bảng log, cron chấm sổ, trang báo cáo | 🔧 code xong, **chờ deploy + tích mẫu** |
| 3 | **Thuật toán AI** — thay công thức trọng số tay bằng mô hình học từ dữ liệu thật | ⛔ bị chặn bởi GĐ 2 |

**Giai đoạn 3 phụ thuộc tuyệt đối vào giai đoạn 2.** Máy nhận định hiện tại **không phải AI** — nó là tổ hợp tuyến tính 6 tín hiệu với trọng số do người gán tay (0.30/0.20/0.15/0.15/0.10/0.10). Muốn thay bằng mô hình học máy thì cần **dữ liệu có nhãn**, mà nhãn chỉ sinh ra được từ tầng log ở giai đoạn 2. Không có vài trăm tới ngót nghìn mẫu độc lập đã chấm sổ thì mọi mô hình đều là khớp nhiễu (overfit), và nó sẽ cho ra một con số đẹp hơn công thức tay trong khi thực tế tệ hơn.

Vì vậy: **đừng đề xuất nhảy thẳng sang machine learning trước khi database chạy ổn định và tích đủ mẫu.** Nếu chủ dự án yêu cầu làm sớm, hãy nói rõ ràng buộc này trước rồi mới làm.

## Chạy & phát triển

**Không có build system, không dependency, không test, không lint.** Toàn bộ là một file HTML tĩnh với CSS + JS thuần nhúng trong `<style>`/`<script>`.

Chạy: mở thẳng `index.html` bằng trình duyệt. Không cần server (mọi API đều là public CORS-enabled hoặc WebSocket).

Kiểm tra cú pháp JS (không có test tự động, đây là lưới an toàn duy nhất) — tách phần `<script>` ra file tạm rồi:

```bash
node --check <file-tam>.js
```

Kiểm tra chạy thật: mở trong trình duyệt, xem Console không có lỗi, và xác nhận cả 4 sàn lên trạng thái `live` (kiểm tra nhanh bằng `Object.values(conns).map(c => c.key + ':' + c.state)` trong Console).

Git: repo nằm ở thư mục `so-lenh/`. Thư mục cha `Coin/` **không** phải repo.

## Kiến trúc

Một file, ~1470 dòng, cộng thư mục `db/` (SQL) và `php/` (tầng log). **Điểm mấu chốt phải hiểu trước tiên: app theo dõi NHIỀU COIN SONG SONG.**

### Khái niệm "engine"

Một **engine** = toàn bộ trạng thái phân tích của đúng một coin. Trước 2026-08-09 mọi thứ là biến toàn cục và app chỉ chạy được một coin; nay chúng nằm gọn trong một object do `taoEngine(sym)` (dòng 171) tạo ra:

```
E = { sym, gen, timers, conns{4 sàn}, flowSec, liqs, midHist,
      wallBook, lvlWatch, brkAlert, lastMidForBrk, market, pred, engineStart }
```

Registry là `engines` (dòng 194), khoá theo mã coin. **Mọi hàm phân tích nhận `E` làm tham số đầu** — `analyze(E, A)`, `updateWalls(E, A)`, `wallTrust(E, side, p)`, `aggregate(E)`, `pushTrade(E, n)`…

`E.gen` là bộ đếm thế hệ **riêng của từng coin**: `dungEngine()` tăng `E.gen`, mọi callback WebSocket cũ tự thấy `g !== E.gen` rồi im lặng thoát. Đây là cơ chế chống race duy nhất — **đừng bỏ nó khi sửa**.

Số coin theo dõi là hằng số `SO_COIN` ở dòng 143 (mặc định 8). Mỗi coin tốn **5 WebSocket** + 2 lệnh REST định kỳ, nên chi phí tăng tuyến tính. Đã đo thực tế với 8 coin: 40 WebSocket, một vòng phân tích đầy đủ hết **~6ms trên ngân sách 2000ms** (0,3%) — nút thắt là băng thông và `JSON.parse`, không phải CPU tính toán.

### ⚠ SÀN GIAO DỊCH LÀ OKX — hai loại sổ, đừng nhầm

Chủ dự án **đặt lệnh trên OKX**. Vì vậy `aggregate(E)` trả về **HAI bộ sổ**, dùng vào hai việc khác nhau — nhầm chỗ là ra tín hiệu sai:

| Bộ | Trường | Dùng cho |
|---|---|---|
| **Sổ gộp 4 sàn** | `bids` / `asks` | tín hiệu áp lực thị trường (`sBook`), `bookM` để chuẩn hoá dòng tiền, và `updateWalls()` theo dõi vòng đời tường (giữ được xác nhận chéo sàn `cx`) |
| **Sổ riêng OKX** | `bidsOkx` / `asksOkx` | **mọi mức giá có thể giao dịch**: `bestWall` chọn giá vào/chốt, `pickLevels` vẽ bản đồ mốc, ETA đo tiền chắn đường, tường tầm xa |

Trong sổ riêng OKX, `m` là **tiền thật trên OKX** còn `ex` vẫn là bitmask gộp 4 sàn — nên vẫn biết mốc đó được mấy sàn xác nhận. Cả hai bộ dùng chung `step` nên khoá tra `wallTrust` khớp nhau, không cần làm gì thêm.

**Vì sao bắt buộc tách:** đo thực tế trên 3 coin, sổ OKX chỉ chiếm **14–44%** tiền của sổ gộp. Một "tường $100k" gộp có khi chỉ có $14k trên OKX — đặt chốt lời ở đó là đặt vào chỗ gần như trống, giá đi xuyên qua. Trước 2026-08-09 app dùng sổ gộp cho cả hai việc, tức mọi mức giá đưa ra đều bị thổi phồng.

Danh sách coin thì **vốn đã chỉ có OKX** — máy quét lấy từ `/api/v5/market/tickers?instType=SWAP` và chỉ nhận cặp `-USDT-SWAP`.

### Bốn tầng

**1. Tầng kết nối (dòng 199–499)** — mỗi sàn một hàm `startXxx(E, g, ...)`, ghi vào `E.conns[key]` với `bids`/`asks` là `Map<priceStr, qty>` theo **đơn vị gốc của sàn**, kèm `pMul`/`qMul` để quy đổi. Cố ý KHÔNG ghép nhiều coin vào một socket dù cả 4 sàn đều hỗ trợ — làm vậy phải viết lại toàn bộ phần dò biến thể tên hợp đồng vốn rất dễ vỡ (xem Bẫy đã gặp), đổi lại chỉ tiết kiệm số socket vốn không phải nút thắt.

**2. Tầng gộp (`aggregate(E)`, dòng 969)** — trộn 4 sàn thành một sổ chung: loại sàn lệch giá >15% (sai hệ số hợp đồng), tự tính bước giá gom mốc, lọc lệnh bụi. Trả về `{bids, asks, mid, step}`, mỗi hàng có `ex` là **bitmask sàn nào đang có tiền ở mốc đó**.

**3. Tầng phân tích (dòng 500–880)** — `updateWalls(E, A)` rồi `analyze(E, A)`, chạy cho **tất cả** engine mỗi 2 giây.

**4. Tầng hiển thị (dòng 1095–1364)** — `render()` → `renderLists()` → `theCoin(E, K)` dựng một thẻ đầy đủ cho mỗi coin có tín hiệu. **Không còn khối chi tiết riêng** — mọi thứ nằm trong thẻ.

**Vòng đời engine (dòng 922–950):** `batDauEngine(sym, thuTu)` / `dungEngine(sym)`. Tham số `thuTu` chỉ để rải lệnh gọi REST 400ms một nhịp, tránh 8 engine bắn cùng lúc vào OKX.

### Chọn tập coin — `dongBoEngine()` (dòng 1406)

Mỗi 60 giây máy quét OKX xếp hạng toàn bộ ticker SWAP theo `heat`, rồi đồng bộ tập engine với top `SO_COIN`. **Có hai chốt chống nhảy loạn, đừng gỡ:**

- Coin đang theo dõi chỉ bị gỡ khi rớt khỏi **top `SO_COIN + 4`** (vùng đệm).
- Mỗi vòng quét gỡ **tối đa 2 coin**. Phần thêm vào thì không giới hạn (lúc mới mở trang phải bật đủ ngay).

Lý do: mỗi lần gỡ engine là mất sạch `wallBook` đã tích được của coin đó.

**Không còn nút TỰ ĐỘNG** — thay bằng cơ chế **GHIM** (`scan.ghim`, dòng 1372). Bấm một dòng trong QUÉT OKX là ghim / bỏ ghim coin đó; máy quét **không bao giờ gỡ coin đã ghim**, coin không ghim vẫn xoay vòng tự động như thường. Ghim một coin chưa theo dõi mà tập đã đầy thì gỡ coin **không ghim** yếu nhất để nhường chỗ; nếu cả `SO_COIN` chỗ đều đã ghim thì không nhận thêm (phải bỏ ghim bớt). Bỏ ghim KHÔNG gỡ engine ngay — nó chạy tiếp tới khi rớt khỏi vùng đệm, để khỏi phí `wallBook` đã tích. Dấu 📌 = ghim, ▶ = đang theo dõi.

Lý do bỏ nút: nút cũ là một trạng thái bật/tắt toàn cục **có thể mắc kẹt** — bấm tay một coin là tự động tắt vĩnh viễn, muốn bật lại phải có nút. Cơ chế ghim không có trạng thái toàn cục nào nên không kẹt được.

### Giao diện

Bố cục **chỉ còn đúng 2 khối**: **QUÉT OKX** → **lưới 2 cột LONG | SHORT**. Không thanh tiêu đề, không footer, không khối chi tiết. Tự xuống 1 cột dưới 900px.

⚠ App **không còn dòng miễn trừ trách nhiệm nào** ("nhận định từ dữ liệu, không phải lời khuyên") sau khi footer bị bỏ theo yêu cầu. Nếu sau này chia sẻ cho người khác dùng thì cân nhắc thêm lại.

**Mỗi coin có tín hiệu là MỘT THẺ tự chứa** (`theCoin(E, K)`, dòng 1137), gồm theo thứ tự: đầu thẻ (mã coin · giá · %24h · điểm S · thanh tin cậy) → cảnh báo funding/sổ mỏng nếu có → cảnh báo vượt mốc ⚡ nếu có → **VÀO / CHỐT** kèm mô tả tường → dòng phụ (trần · sàn · ETA · tầm xa) → dòng căn cứ (khung · dòng tiền · funding · thanh lý · biên) → **BẢN ĐỒ MỐC** 3 kháng cự + 3 đỡ. Viền trái xanh/đỏ theo hướng kèo.

**Coin KHÔNG có tín hiệu thì không hiện ở đâu cả.** Dải "đứng ngoài / đang khởi động" đã bị bỏ theo yêu cầu, nên phần chẩn đoán dồn hết vào dòng chú thích `#scanNote` ngay cạnh tiêu đề QUÉT OKX: số coin đang theo dõi, số kết nối sàn còn sống (vd `27/32 sàn live`), số coin chưa ấm máy. **Nếu thấy hai bảng trống hoàn toàn thì đọc dòng đó trước khi nghĩ là app hỏng** — 90 giây đầu sau khi mở trang chắc chắn trống.

`keoCua(E)` (dòng 1110) là chỗ **định nghĩa giá vào / giá chốt** — không phát minh logic mới, chỉ đọc lại hai tường mà `analyze()` đã chọn: kèo LONG vào tại tường **đỡ** dưới (`pred.sup`) và chốt tại tường **kháng** trên (`pred.res`); kèo SHORT ngược lại. Trả `null` nếu coin không đủ điều kiện phát tín hiệu — đây cũng chính là bộ lọc quyết định coin nào được hiện.

`pred.phat` (đặt trong `analyze`) gom toàn bộ điều kiện phát tín hiệu về **một chỗ duy nhất**: hết warmup 90s, `|S| ≥ 25`, ≥2 tín hiệu hoạt động, không `flowConflict`, không `satNoConfirm`, và có đủ cả `sup` lẫn `res`. Quan sát thực tế: điều kiện hay chặn nhất là `satNoConfirm` và **thiếu tường trong tầm `cap`** — nhiều coin có `|S|` khá cao vẫn không ra thẻ vì không tìm được tường đủ tin cậy.

### Kế hoạch giao dịch — cắt lỗ, R:R, mốc so sánh

`keoCua(E)` trả về đủ **ba** mức giá chứ không phải hai:

- `vao` = tường cùng phía (đỡ nếu long, kháng nếu short)
- `chot` = tường đối diện
- `cat` = **vượt qua tường vào** một khoảng `HE_SO_CAT × cap × mid`, mặc định `HE_SO_CAT = 0.30` (dòng 1096)

Logic của cắt lỗ: vào tại tường đỡ mà tường đó thủng nghĩa là **giả định gốc sai** — đó mới là điểm vô hiệu, không phải tường đối diện. Đặt cắt lỗ đúng tại tường đối diện là đặt stop ở chỗ chính app dự đoán giá sẽ bật.

⚠ `HE_SO_CAT = 0.30` là **tham số tự chọn, chưa có dữ liệu nào chứng minh**. Phải đo lại khi có sổ log rồi mới chỉnh.

Thẻ coin hiển thị kèm hai con số mà mọi tỷ lệ thắng phải đọc cùng:

- **R:R** = `|chot − vao| / |vao − cat|`
- **`baseline`** = `|vao − cat| / (|chot − vao| + |vao − cat|)` — với giá đi ngẫu nhiên không xu hướng, đây chính là xác suất chạm chốt trước. Hiển thị dưới dạng *"cần thắng > N% mới hơn ngẫu nhiên"*. **Đây là mốc so sánh đúng, không phải 50%.**

### Ba nhịp tim

| Nhịp | Chu kỳ | Việc |
|---|---|---|
| Phân tích | 2000ms (dòng 1065) | lặp qua **mọi** engine: `aggregate` → `updateWalls` → `analyze` |
| Vẽ | 1000ms (dòng 1364) | `render()` → `renderLists()`, dựng lại toàn bộ thẻ |
| Quét OKX | 60000ms (dòng 1466) | xếp hạng coin nóng + `dongBoEngine()` |

Tách nhịp phân tích khỏi nhịp vẽ là có chủ đích. **Đừng gộp lại.**


## Máy nhận định (`analyze`, dòng 699)

Điểm tổng hợp `S` (thang ±100) từ 6 tín hiệu, mỗi tín hiệu chuẩn hóa về −1..1:

| Tín hiệu | Trọng số | Nguồn |
|---|---|---|
| `sFlow30` — dòng tiền khớp 30 phút | 0.30 | aggTrade/publicTrade 4 sàn |
| `sLiq` — thanh lý | 0.20 | forceOrder (Binance) |
| `sFlow5` — dòng tiền khớp 5 phút | 0.15 | như trên |
| `sBook` — sổ lệnh đã lọc theo độ tin | 0.15 | `aggregate()` × `wallTrust()` |
| `sOi` — Open Interest so 25 phút trước | 0.10 | OKX REST |
| `sFund` — funding **ngược đám đông** | 0.10 | OKX REST |

Bốn chốt chặn buộc máy đứng ngoài (nay gom hết vào cờ `pred.phat` cuối `analyze`):

- `|S| < 25` hoặc dưới 2 tín hiệu hoạt động → chưa rõ hướng
- `flowConflict` — dòng 5' ngược hẳn dòng 30' (thị trường đảo nhịp)
- `satNoConfirm` — dòng tiền kịch kim cả 2 khung mà sổ lệnh lẫn OI đều không xác nhận (nghi đuổi đuôi cú chạy)
- `satPen` — bão hòa thì giảm 20% trọng số dòng tiền

**Bốn chốt này không phải lý thuyết** — chúng được thêm vào sau khi phân tích log kèo thật, số liệu cụ thể ghi ngay trong comment tại dòng 736 và 743 ("log 79 kèo", "bão hòa 50% vs vừa 62%"). Đừng gỡ ra vì "trông thừa".

⚠ Nhưng cũng đừng coi chúng là chân lý: n=79 chia đôi thì sai số của HIỆU khoảng 11 điểm phần trăm, nên chênh lệch 7 điểm ghi trong comment **chưa phân biệt được với may rủi**. Đây là giả thuyết chưa kiểm chứng, cần đo lại bằng mẫu lớn hơn khi có tầng log MySQL.

Khung thời gian `frameH` tự chọn theo độ nóng: coin chạy ≥3% trong 1h thì lấy khung 1h, không thì 2h, không nữa thì 4h. Biên `cap` là **trung vị** biên dao động của khung đó trong 48h qua (nến 15m từ OKX).

## Bộ theo dõi tường (dòng 540–618) — phần cốt lõi

Đây là thứ làm nên giá trị của app. Mỗi mốc tiền lớn được lưu vào `wallBook` (key = `b:giá` hoặc `a:giá`, giá lấy 6 chữ số có nghĩa) và chấm điểm tin cậy 0–100 qua `wallTrust()`:

- Sống lâu → cộng điểm, **nhưng chỉ nửa điểm nếu chưa từng bị giá thử** (treo lệnh ảo ở xa chẳng tốn gì)
- `sv` chịu được giá áp sát rồi giá rời đi mà tường còn → **+15/lần**
- `fl` bị khớp thật, ăn mòn dần → **+8/lần** (tiền thật)
- `pl` biến mất nguyên khối khi giá áp sát → **−30/lần** (lệnh ảo, phạt nặng)
- Xuất hiện trên ≥2 sàn → +10, ≥3 sàn → +5

`E.wallBook` **chỉ sống trong RAM** và mất khi tải lại trang, nhưng **không còn bị reset khi máy quét đổi coin** — chừng nào coin còn nằm trong tập theo dõi thì tường tiếp tục tích luỹ độ tin. Đây là lợi ích lớn nhất của kiến trúc đa coin: nó xoá bỏ lỗi "kèo mù" (tường còn ở mốc tin 30 vì vừa đổi coin) mà đợt phân tích thiết kế log ước tính chiếm 20–30% số kèo ở bản một-coin. Sau mỗi lần F5 thì vẫn phải chờ vài phút cho tường lên tin.

Ngưỡng "áp sát"/"rời xa" tính theo **bước giá** (`3×step` / `8×step`), có trần theo % — không dùng % cố định. Lý do ghi ở comment dòng 534: ngưỡng % cố định từng làm sổ dày như BTC bị chấm oan hàng loạt.

## Bẫy đã gặp (đừng dẫm lại)

Những cái này đã tốn công phát hiện, đều có comment tại chỗ trong code:

- **Binance**: base URL `/stream` cũ đã bị khai tử 23/04/2026 — vẫn mở được socket nhưng **câm lặng vĩnh viễn**. Phải dùng `/market/stream`.
- **Tên hợp đồng**: coin meme có biến thể `1000X`, `X1000`, `10000X`, `1MX` khác nhau tùy sàn. Mỗi sàn có bảng `*_VARIANTS` riêng, dò lần lượt. Dò xong phải set `pMul`/`qMul` cho khớp giá thật.
- **OKX**: khối lượng tính theo **hợp đồng**, phải gọi REST lấy `ctVal` rồi nhân — quên là sai số hàng nghìn lần.
- **Bitget**: gói `snapshot` đầu tiên của kênh `trade` là **50 lệnh LỊCH SỬ** (có thể cũ hàng giờ). Phải bỏ, chỉ nhận `update`.
- **Phân biệt lỗi mạng với cặp không tồn tại**: sau 6s không có dữ liệu, nếu socket **vẫn đang mở** mà im lặng thì cặp không tồn tại → thử biến thể tiếp theo; nếu socket đã rớt thì là lỗi mạng → thử lại cùng cặp.
- **Chu kỳ funding không phải lúc nào cũng 8h** — nhiều coin meme là 4h/2h/1h. Phải tự tính từ `prevFundingTime`/`fundingTime` rồi quy đổi về mức /8h trước khi so sánh.
- **Lấy mẫu lịch sử**: muốn lấy mốc "25 phút trước" thì phải duyệt **ngược từ cuối mảng**. Dùng `find()` từ đầu mảng sẽ lấy nhầm phần tử cũ nhất (2–4 tiếng) và làm lệch cả tín hiệu OI.
- **Bước giá đổi làm bucket "đổi tên"** — tường không hề rút nhưng key cũ biến mất. `updateWalls` có đoạn kiểm tra vùng giá đó còn ≥40% tiền không, để xóa bản ghi lặng lẽ thay vì phạt oan.

## Bản đồ API — cái gì gọi được, cái gì không

Kết quả một đợt kiểm chứng 5 hướng + 2 vòng phản biện (2026-08-09), **có test live bằng curl và WebSocket thật**, không suy đoán. Đọc mục này trước khi đề xuất "thêm dữ liệu từ API X".

### ⛔ BỨC TƯỜNG CORS — chia đôi mọi thứ

Trên cùng tên miền `www.okx.com`, chỉ **một phần** endpoint trả header `Access-Control-Allow-Origin`:

| Gọi được từ file HTML tĩnh | Bị chặn CORS, phải chờ backend GĐ2 |
|---|---|
| `/api/v5/market/*` · `/api/v5/public/*` · **toàn bộ WebSocket** | **toàn bộ `/api/v5/rubik/stat/*`** · `/api/v5/copytrading/*` · `/api/v5/support/announcements` · `/api/v5/system/status` · `/api/v5/rfq/*` |

⚠ **Bẫy kiểm thử đã bị dính một lần:** mở app bằng `file://` trong khung xem trước của một ứng dụng desktop (Electron tắt web security) thì fetch tới `rubik` **chạy được**, tạo cảm giác sai là không sao. Trên Chrome thật thì hỏng. Luôn kiểm CORS bằng `curl -I -H "Origin: null"` chứ đừng tin khung xem trước.

### Ba kênh WebSocket OKX chưa dùng — đều không cần đăng nhập, không dính CORS

Đây là phát hiện quan trọng nhất, vì nó **xoá phần lớn lý do phải chờ backend**:

| Kênh | Đo thực tế | Giá trị |
|---|---|---|
| `liquidation-orders` (`instType=SWAP`) | 1 subscription cho **toàn sàn** | Tín hiệu `sLiq` **trọng số 0.20 — nặng thứ hai** — hiện chỉ lấy từ `forceOrder` của Binance. Chủ dự án đặt lệnh trên OKX mà tín hiệu thanh lý không có một byte nào từ OKX |
| `open-interest` | 8–9 gói/phút/coin, payload có sẵn `oi`, `oiCcy`, **`oiUsd`** | Thay hẳn việc poll REST `/public/open-interest` mỗi 60 giây: độ phân giải tăng ~8 lần mà **giảm** số request. `oiUsd` cho phép so chéo 8 coin công bằng |
| `instruments` (`instType=SWAP`) | 1 subscription | Phát hiện niêm yết mới / tạm dừng ngay lập tức → cờ **chế độ** (coin vừa list = sổ mỏng = siết ngưỡng tường), không phải cờ hướng |

Ngoài ra `bbo-tbt` chạy được không cần đăng nhập (~10 gói/giây) — dùng bắt chính xác mili-giây giá chạm tường.

### 🧱 Trần dữ liệu sổ lệnh ĐÃ CHẠM

`books-l2-tbt` và `books50-l2-tbt` trả `{"code":"60011","msg":"Please log in"}` — cần API key **và hạng VIP4/VIP5**. Tức **vĩnh viễn ngoài tầm**, backend GĐ2 cũng không mở được nếu tài khoản không đủ hạng.

App đang dùng `books` (400 mức, 100ms) — **đó chính là mức cao nhất công khai**. Hệ quả thẳng thắn: chống lệnh mua/bán giả phải giải bằng **thuật toán trên dữ liệu đang có**, không có API nào cứu được.

### Bốn yêu cầu — phán quyết

| Yêu cầu | Có API? | Phán quyết |
|---|---|---|
| **Lệnh giả** | Không có gì mới | Trần đã chạm. Đây lại đúng là lĩnh vực **duy nhất** app có lợi thế thông tin thật, vì nó cần quan sát sổ lệnh liên tục qua thời gian — thứ không ai bán dưới dạng API |
| **Khung giờ** | Có đủ, CORS mở, `history-candles` lùi >5 năm | **Hướng theo giờ là nhiễu thuần và VĨNH VIỄN không đo được** (dưới). Biên độ theo giờ thì thật, nhưng bị EWMA ~20 dòng thay thế gần hết |
| **Sự kiện** | Một phần | Tin tức có **kỳ vọng ÂM** (dưới). Chỉ giữ 3 thứ với vai trò **phòng thủ** |
| **Cá mập** | Có, nhưng không phải cá mập | Xem dưới — đây là chỗ dễ hiểu nhầm nhất |

### Cá mập — sự thật đầy đủ

OKX **có** công khai vị thế đang mở của từng lead trader copy trading (`instId`, `posSide`, giá vào, size, đòn bẩy), không cần API key. Nhưng:

- **Quy mô không phải cá mập.** Đo thực tế: lead trader AUM lớn nhất **toàn sàn** là 8.495.794 USDT, người thứ nhì 2.638.651, từ vị trí thứ tư đã dưới 1 triệu. Riêng open interest của `BTC-USDT-SWAP` là 2.060.264.771 USDT → nhóm này chiếm **~0,4%**. Đây là "theo dõi KOL copy trading", không phải theo dõi cá mập.
- Nhóm endpoint này **không có CORS** → cần backend.
- ~1/3 lead trader ẩn chi tiết vị thế (trả về trường rỗng).
- **Không tồn tại cách nào xem vị thế của một tài khoản OKX bất kỳ.** Chỉ tài khoản tự nguyện đăng ký làm lead trader. Cá mập thật, quỹ, market maker đều không có mặt trong dữ liệu này.

Hyperliquid thì công khai toàn bộ vị thế, miễn phí, CORS `*`, cả REST lẫn WebSocket — nhưng suy diễn chéo sàn HL → OKX **không hợp lệ**: chỉ thấy MỘT CHÂN của giao dịch (basis trade / phòng hộ delta-neutral hoàn toàn có thể có chân kia ở nơi khác), không phân biệt được market maker với trader định hướng (ví đứng đầu leaderboard có khối lượng allTime hơn 126 tỷ USD — đó là MM, tồn kho là sản phẩm phụ chứ không phải quan điểm), và ví HL đã bị cả thị trường theo dõi nên hết lợi thế.

⚠ **Chỉ số `long-short-position-ratio-contract-top-trader` có thể là OFFSET CẤU TRÚC chứ không phải tín hiệu.** Nhóm "top 5% theo giá trị vị thế" theo định nghĩa là nơi tập trung dày nhất basis trade và phòng hộ — short của họ không phải quan điểm giá. Nếu dùng **mức**, app sẽ lệch short vĩnh viễn mà người dùng tưởng là phát hiện. Chỉ được dùng **z-score của độ thay đổi** so với phân phối 30 ngày, và trước đó phải vẽ chuỗi hiệu số 90 ngày để kiểm tra: nếu nó dao động quanh một trung bình khác 0 và ít biến động → đó là cấu trúc, vứt luôn.

### Khung giờ — hướng thì vĩnh viễn không đo được

Đo trên **17.999 nến 1H BTC** (20/07/2024–09/08/2026): `|t|` lớn nhất trong 24 giờ chỉ **2,66**, ngưỡng Bonferroni cho 24 phép thử là **2,87** → không giờ nào sống sót. Out-of-sample dấu trùng **13/24** (tung đồng xu được 12/24).

Và con số đóng đinh: với σ = 49 bps mỗi giờ, để phát hiện một lợi thế thật cỡ 2 bps cần **2.401 mẫu mỗi ô giờ = 6,6 năm dữ liệu mỗi coin**. Chế độ thị trường crypto không tồn tại 6,6 năm. Đây không phải "chưa đủ dữ liệu" mà là **không bao giờ đủ**. Đừng chờ tích luỹ.

**Biên độ** theo giờ thì có thật và có cơ chế nhân quả: 14:00 UTC biến động gấp 2,26 lần 10:00 UTC, permutation p<0,002, out-of-sample r=0,927, tương quan chéo BTC/DOGE/SOL 0,95–0,98, và đỉnh **dịch từ 14:00 sang 15:00 UTC theo mùa giờ Mỹ** — gắn đúng giờ mở NYSE.

Nhưng hồ sơ 24 ô giờ tốn ~470 request, ~8 phút khởi động lạnh, 12–15 MB, buộc dùng IndexedDB, phải xử lý DST — trong khi một **EWMA biên độ 24–48 giờ (~20 dòng)** bắt được gần hết hiệu ứng đó *và* bắt thêm được đổi chế độ biến động, tin tức, coin vừa list. Lợi thế duy nhất của hồ sơ tĩnh là nó **biết trước** (13:55 đã biết 14:00 sắp sôi động). Nếu làm thì: EWMA làm nền + hệ số giờ nhỏ đã làm trơn 3 giờ, và phải đo xem có cải thiện gì không.

✅ **Phát hiện âm-tính giá trị cao:** *"giờ funding là giờ biến động mạnh"* là **niềm tin SAI**. Đo trên OKX: khối lượng +60,2% nhưng biên độ chỉ +9,6% (t=1,33, không có ý nghĩa). **Đừng cộng điểm S vì sắp tới giờ funding.**

### Sự kiện — tin tức có kỳ vọng ÂM

Tin niêm yết làm giá chạy trong **100–500 ms**. Đối thủ là bot cùng phòng máy với sàn. App poll 60 giây qua cầu nối RSS bên thứ ba có cache → chậm hơn **3–5 bậc độ lớn**. Cú bơm niêm yết có hình gai nhọn rồi hồi mạnh, nên một cờ "có tin tốt" bật ở t+60s không đưa bạn vào đầu sóng mà vào **đúng đỉnh ngay trước nhịp hồi**. Xây nó xong app sẽ **tệ hơn** app hiện tại.

Đã test và loại toàn bộ: CoinGecko news 401, CryptoCompare 401, CoinDesk Data cần key, CryptoPanic bị Cloudflare 403, TradingEconomics guest 410, FRED bắt buộc key, BLS chặn 403. **Không còn API tin tức hay lịch vĩ mô nào vừa miễn phí, vừa không cần key, vừa có CORS.**

Ba thứ giữ được, **chỉ với vai trò phòng thủ**:
1. WS `instruments` — coin vừa list → siết ngưỡng tường, hạ trọng số sổ lệnh. Cờ chế độ, không phải cờ hướng.
2. Lịch FOMC/CPI **nhúng cứng** — giá trị duy nhất là **KHÔNG giao dịch** trong cửa sổ ±5 phút. Không có thông tin gì về hướng.
3. Unlock token (DefiLlama nhánh dataset tĩnh, còn miễn phí) — **huy hiệu riêng cạnh coin, tuyệt đối không cộng vào S**: chân trời là ngày còn engine là phút, và mốc unlock của dự án không có vesting on-chain chỉ là ước lượng.

### ⚠ Nguyên tắc bị vi phạm xuyên suốt — đọc kỹ trước khi thêm tín hiệu

**KHÔNG BAO GIỜ trộn các chân trời thời gian khác nhau vào MỘT điểm số vô hướng.**

Cá mập là chân trời ngày, unlock là ngày, tỷ lệ long/short trễ 288 giây là giờ, dòng tiền khớp là 5 phút. Trộn hết vào `S` sẽ: phá huỷ khả năng diễn giải (S = 40 nghĩa là gì?), khiến backtest bất khả thi vì không tách được đóng góp, và tạo một **offset đứng yên** mà người dùng đọc thành "thị trường đang nghiêng".

Quy tắc: **chỉ tín hiệu có chân trời ≈ chân trời engine mới được vào `S`.** Mọi thứ chậm hơn phải là bộ chỉnh ngưỡng hoặc một lớp hiển thị riêng.

Lỗi thứ hai: **đa kiểm định ở cấp danh mục.** ~15 tín hiệu ứng viên × 8 coin × vài biến thể tham số → vượt 300 phép thử. Ở p<0,05 sẽ có khoảng 15 tín hiệu "chạy được" hoàn toàn do may rủi.

### Thứ tự đề xuất

**Làm được ngay** (CORS đã verify với `Origin: null`, chi phí gần bằng 0):
1. WS `liquidation-orders` — vá lỗ hổng lớn nhất, tín hiệu nặng thứ hai đang thiếu dữ liệu OKX
2. WS `open-interest` — thay REST poll, tốt hơn mà rẻ hơn
3. `listTime` từ `/public/instruments` → **cổng chặn tuổi coin**. Coin 3 ngày tuổi thì mọi thống kê là rác. Tỷ lệ giá trị trên số dòng code cao nhất trong cả tài liệu này
4. WS `instruments` → cờ chế độ coin mới list
5. EWMA biên độ 24–48h thay cho hồ sơ 24 khung giờ

**Chờ backend GĐ2:** bộ 3 long/short ratio (chỉ sau khi kiểm tra offset cấu trúc), `taker-volume-contract` để đối chiếu xem WebSocket có mất gói âm thầm không.

**Vứt hẳn:** toàn bộ `copytrading/*`, block trade / RFQ (100% là quyền chọn BTC/ETH, không có leg perp nào), nhóm option max pain cho altcoin, Whale Alert, Arkham, Nansen, Dune, Etherscan, mọi API tin tức, và hồ sơ HƯỚNG theo 24 khung giờ.

## Trạng thái & hướng đi

Hiện tại app **không lưu gì xuống đĩa** — toàn bộ tầng ghi log localStorage đã bị gỡ (xem Nhật ký thay đổi). Dữ liệu chỉ sống trong RAM và mất khi tải lại trang.

Kế hoạch: deploy lên hosting (1Panel + MySQL) và ghi log qua một tầng API PHP.

✅ **Lược đồ ĐÃ DỰNG THẬT trên hosting (2026-08-09)** — chạy file DDL qua phpMyAdmin, xác nhận bằng `information_schema`: `keo` (BASE TABLE), `phi` (BASE TABLE), `v_keo` (VIEW). MySQL của hosting hỗ trợ derived table trong VIEW nên không phải viết lại. Bảng hiện **rỗng** — chưa có gì ghi vào vì `API_LOG` phía app còn để trống (xem mục "Việc tiếp theo phải làm").

Số cột đúng của bảng `keo` là **49** (đã đếm lại trong `db/tao-database.sql`: 8 định danh + 8 giá + 13 trạng thái máy + 8 chất lượng tường + 4 bối cảnh + 8 kết quả). Con số "47 cột" từng ghi ở đây là lần xác nhận **trước khi** thêm `kq_dong` và `cham_luc`, nay đã sai.

⚠ **Bẫy chưa kiểm chứng:** DDL dùng `CREATE TABLE IF NOT EXISTS keo`. Nếu file được chạy vào một database **đã có** bảng `keo` bản 47 cột thì MySQL bỏ qua lặng lẽ, KHÔNG thêm `kq_dong`/`cham_luc`, và `cham.php` sẽ lỗi khi ghi hai cột đó. Chủ dự án báo đã tạo **database mới** nên đúng ra không dính, nhưng phải xác nhận bằng `SHOW COLUMNS FROM keo LIKE 'cham_luc';` trước khi cài cron.

⚠ **Việc phải làm ĐẦU TIÊN, trước cả khi tạo bảng: viết lại LUẬT SINH KÈO.** Sau đợt dọn log, `analyze()` **không còn sinh kèo nào** — nó chỉ ghi đè biến `pred` mỗi 2 giây. Không có luật sinh, không có hạn giờ, không có bộ chấm. Cắm API vào `analyze()` lúc này là ghi lại một trạng thái mà bản thân app không coi là kèo. Luật đề xuất: mỗi nhịp 2 giây, nếu coin hiện tại chưa có kèo đang mở và `bestWall` trả về đủ cả `sup` lẫn `res` thì phát một kèo (~20–40 kèo/ngày, và chúng thật sự độc lập với nhau).

**Ba lỗi trong code phải sửa TRƯỚC khi bật log** — đây là dữ liệu mất là mất vĩnh viễn, không backfill được:

1. `pushTrade(n)` cộng dồn **có dấu** → không tách được mua gross và bán gross, nên `f30 = 0` lẫn lộn "chợ chết" với "hai phe đánh nhau ngang cơ". Cũng mất luôn thông tin sàn nguồn.
2. `aggregate()` có tính `bb`/`ba` (best bid/ask thật) nhưng **không trả về** — hàng đầu của `bids`/`asks` là mốc đã gom theo `step` và đã lọc bụi, không phải best bid/ask. Sửa 1 dòng ở lệnh `return`.
3. Độ phủ sổ lệnh 4 sàn khác nhau (Binance chỉ 20 mức, OKX 400) nên bitmask `ex` ở mốc xa **luôn** thiếu Binance — không phải vì hết tiền mà vì feed không với tới. Đừng diễn giải `⚠1 sàn` ở mốc xa như bằng chứng tường giả.

### Quyết định thiết kế đã chốt cho giai đoạn 2

Kết quả một đợt phân tích 5 góc nhìn chuyên môn + 3 vòng phản biện (2026-08-09). Năm chuyên gia đề xuất ~150 trường trên 15 bảng; phản biện cắt còn **1 bảng ~36 cột + 1 VIEW + 3 file PHP**. Giữ nguyên quy mô này, đừng phình ra.

**Ba quyết định kiến trúc:**

1. **Chấm sổ chạy ở cron PHP, tuyệt đối không ở trình duyệt.** Máy quét vẫn xoay vòng tập coin (mỗi 60 giây, gỡ tối đa 2 con rớt khỏi vùng đệm), nên kèo của coin bị gỡ sẽ bị bỏ rơi giữa chừng — và việc mất tích đó *không ngẫu nhiên*, nó tương quan với "có coin khác đang bùng nổ". Chấm ở client là tự tạo thiên lệch sống sót không sửa được về sau. Chi phí thật của cron: 1–3 request/phút tới OKX, thừa an toàn với rate limit. *(Kiến trúc đa coin + ghim làm nhẹ vấn đề này đi nhiều so với bản một-coin cũ, nhưng không xoá được nó.)*
2. **Ghi cả kèo bị 4 chốt chặn loại bỏ** (cột `chan` dạng bitmask) ngay từ ngày đầu, chấm y hệt kèo thật. Đây là nhóm đối chứng duy nhất cho 4 chốt chặn. Dữ liệu bị chặn mà không ghi là mất vĩnh viễn.
3. **Không ghi trạng thái tường liên tục** — chỉ ghi khi tường kết thúc vòng đời (bị rút / bị ăn hết / biến mất). Ghi mỗi 2 giây là hàng chục nghìn lượt ghi mỗi giờ cho dữ liệu bị đè ngay.

**Hai nguyên tắc sàng lọc cột** (áp vào là tự loại 110/150 trường):

- Thứ gì **tái tạo được từ nến** thì không lưu — BTC beta, benchmark, mốc +1h/+4h/+24h, funding tích luỹ, MFE/MAE chi tiết đều backfill được từ `(inst_id, giờ vào, giờ ra)`.
- Thứ gì là **hàm thuần của cột khác** thì đặt trong VIEW — `conf`, `agree`, `act`, `scale`, mọi khoảng cách, RR, R. Đổi công thức chỉ sửa VIEW, không `ALTER TABLE`, không hỏng dữ liệu cũ.

**Cột bắt buộc, nhóm theo mức thiết yếu:**

| Mức | Cột | Vì sao |
|---|---|---|
| Không có là **hệ thống không chạy** | `inst_id` (vd `1000PEPE-USDT-SWAP`) | cron không biết kéo nến hợp đồng nào |
| | `uid` ULID sinh ở JS | chống ghi trùng khi mạng chập và client gửi lại |
| | `han_ms` hạn tuyệt đối | không lưu "số giờ" |
| Không có là **số đo vô nghĩa** | khoảng cách tới TP và tới SL | để tính RR và mốc so sánh baseline |
| | `chan` bitmask 4 chốt | nhóm đối chứng |
| | `ver` hash 6 trọng số | trộn dữ liệu trước/sau lần chỉnh trọng số là mọi so sánh thành rác |
| | `tuoi_may` (giây từ `resetEngine`) | **sức giải thích cao nhất** — `wallBook` reset mỗi lần đổi coin nên có thể 20–30% số kèo là "kèo mù", mọi tường còn ở mốc tin 30 |
| | 6 tín hiệu thành **6 cột rời, cho phép NULL** | NULL = thiếu dữ liệu, khác hẳn 0 = trung tính |
| Nên có | `bid`/`ask` thô, `tgt_trust`/`sv`/`fl`/`pl`, `ex_mask`, `book_usd`, `cap`, `frame_h`, `mfe_bp`/`mae_bp`, `ghi_chu` | chất lượng tường đóng băng lúc ra kèo; MFE/MAE gần như miễn phí vì cron đã phải quét nến |

**Cắt hẳn khỏi giai đoạn 2:** toàn bộ tầng bot (chưa có bot, chưa có API key — nối vào sau qua `uid`, chi phí tương thích ngược bằng 0); bảng ghi trạng thái mỗi 30 giây (1 triệu dòng/năm cho một nhóm đối chứng mà kèo bóng cho rẻ hơn 50 lần); mọi cột dẫn xuất kiểu `r_realized` (đặt tên đó cho một sổ giấy có phí là hằng số giả định thì vài tháng sau chính mình sẽ đọc nó như tiền thật).

### Nguyên tắc đo lường — đọc trước khi báo cáo bất kỳ con số nào

- **Tỷ lệ thắng trần trụi là con số dối nhất trong hệ thống này.** TP và SL đều là *tường*, khoảng cách bất đối xứng và đổi theo từng kèo. Thắng 65% ở RR 0.6 là cháy tài khoản; thắng 45% ở RR 2.5 là in tiền.
- **Luôn so với mốc nội tại** `p_baseline = d_opp / (d_tgt + d_opp)` — với giá đi ngẫu nhiên không xu hướng, đó chính là xác suất chạm TP trước SL. Thắng 60% khi baseline là 65% nghĩa là máy **tệ hơn tung đồng xu**. So với 50% là so sai mốc.
- **TP và SL phải dùng cùng một quy ước chấm.** Siết TP mà để SL ở mức "chạm" là tự làm đẹp số.
- **Cả TP lẫn SL rơi vào cùng một cây nến ⇒ kết quả riêng `nhập_nhằng`**, không được ném bỏ. Ném bỏ là làm lệch số *lên*, vì nến biến động mạnh thường quét stop trước rồi mới chạy.
- **`mid` gộp 4 sàn không giao dịch được** — nó là composite chéo sàn, có thể cho spread âm. Đừng dùng nó làm giá vào/ra rồi gọi kết quả là lợi nhuận.
- **In cỡ mẫu cạnh mọi con số.** Phân biệt 55% với 50% cần ~780 kèo độc lập; với 20–40 kèo/ngày là vài tuần. Không có cảnh báo này thì lại chỉnh trọng số sau 30 kèo — đúng vòng lặp đã sinh ra 4 chốt chặn hiện tại.
- **Kèo bóng `|S| < 25` KHÔNG phải đối chứng có ý nghĩa** — khi đó hướng chỉ là dấu của một số gần 0, tỷ lệ thắng gần như chắc chắn ~50% và không nói gì về chốt chặn. Chỉ `flowConflict` và `satNoConfirm` mới là đối chứng thật. Cách sạch hơn: hạ ngưỡng ghi, ghi hết, rồi áp 4 chốt bằng SQL lúc phân tích.
- **Phải có trang đọc số** (`bao_cao.php`, chừng 80 dòng). Không có thì DB chỉ là cái hố ghi — mà băng "đúng x/y" trong app đã bị xoá cùng tầng log cũ, nên hiện không còn vòng phản hồi nào.

## Tầng log (giai đoạn 2) — đã viết xong, chờ deploy

Hướng dẫn triển khai 6 bước: `php/HUONG-DAN.md`. Trạng thái: **code xong, lược đồ đã dựng trên hosting, chưa nối dây** (`API_LOG` để trống = tắt hẳn).

| Tệp | Việc |
|---|---|
| `db/tao-database.sql` | **FILE DUY NHẤT** — bảng `keo` 49 cột + bảng `phi` + VIEW `v_keo` + dữ liệu nền. Chạy một lần qua tab **Import** (đừng dán vào tab SQL). Không ghi cứng tên database, chạy lại bao nhiêu lần cũng an toàn |
| `php/config.php` | kết nối DB + token + CORS. ✅ đã điền. **KHÔNG commit** — đã untrack khỏi git |
| `php/config.mau.php` | bản mẫu rỗng, **file duy nhất được commit**. Sửa cấu trúc config thì phải sửa ở CẢ HAI |
| `php/.htaccess` | chặn HTTP đọc trực tiếp `config*.php` — phòng trường hợp handler PHP hỏng và Apache trả .php dạng văn bản thuần. Vô tác dụng trên nginx thuần |
| `php/ghi.php` | nhận kèo theo lô, chống trùng bằng `uid`. Cột kết quả cố ý KHÔNG cho client ghi |
| `php/cham.php` | cron mỗi phút, chấm bằng nến 1m OKX |
| `php/bao-cao.php` | trang đọc số |

### Luật sinh kèo (`sinhKeo`, dòng 1303)

Mỗi nhịp 2 giây, mỗi coin: nếu **chưa có kèo đang mở**, đã hết warmup, có đủ `sup` lẫn `res`, và `|S| ≥ NGUONG_GHI` thì phát một kèo. Hạn = `frameH` giờ. Ra khoảng 20–40 kèo/ngày trên 8 coin và chúng **độc lập** với nhau.

⚠ **`NGUONG_GHI = 15` thấp hơn ngưỡng hiển thị 25 — có chủ đích.** Kèo bị 4 chốt chặn loại bỏ **vẫn được ghi và vẫn được chấm**, đánh dấu bằng bitmask `chan` (1 = |S|<25, 2 = dưới 2 tín hiệu, 4 = flow ngược, 8 = bão hoà không xác nhận). `chan = 0` là kèo thật. Đây là nhóm đối chứng duy nhất để biết chốt chặn đang cứu hay đang cắt mất kèo thắng — lọc lại bằng SQL lúc phân tích. Cách này thay cho ý tưởng "kèo bóng" ban đầu, vốn phải BỊA hướng khi `|S|` gần 0.

`VER_TRONG_SO` (dòng 1101) là hash **tự sinh** từ chính mảng tham số quyết định, nên không bao giờ quên bump khi chỉnh trọng số.

Giá gửi lên dưới dạng **chuỗi `toFixed(15)`** — khớp `DECIMAL(30,15)` và tránh ký hiệu mũ (`1.23e-8`) mà MySQL không nhận. Coin có giá từ 0,0000000123 tới 90000.

### Hai quy ước chấm

`cham.php` chấm mỗi kèo **hai lần**: `kq` theo quy ước **chạm** (bóng nến tới giá) và `kq_dong` theo quy ước **đóng nến vượt hẳn**. Cả hai áp cùng cách cho chốt lời lẫn cắt lỗ — siết một đầu là tự làm đẹp số. Chênh lệch giữa hai con số là **thước đo độ nhạy**: quá 5 điểm phần trăm nghĩa là kết luận phụ thuộc vào quy ước chứ không phụ thuộc vào máy.

⚠ **Bẫy phân trang nến đã dính một lần khi viết:** với tham số `before` và `limit=300`, nếu khoảng cần lấy dài hơn 300 nến thì OKX trả 300 cây **mới nhất** chứ không phải 300 cây ngay sau mốc — thủng đoạn giữa mà không báo lỗi. Phải phân trang **lùi** bằng `after`. Cũng dùng `/market/candles` (giữ 1.440 nến 1m = 24h) chứ không phải `/market/history-candles`, và bỏ qua nến chưa đóng (`confirm = '0'`).

## Nhật ký thay đổi

Commit gần nhất trước khi có file này: `5d4e6f7` (máy quét OKX + lãi tự động).

- **2026-08-09 (14)** — **Cấu hình tầng log theo thông tin hosting chủ dự án cung cấp.**
  `php/config.php` điền đủ 4 giá trị (DB `buwsofujhosting_coin_db`, user
  `buwsofujhosting_coin_user`, token 32 ký tự sinh bằng CSPRNG chứ không tự nghĩ);
  `API_TOKEN` trong index.html điền khớp. `API_LOG` **vẫn để trống** — chủ dự án chọn chạy app
  bằng `file://` nên cần URL tuyệt đối, mà tên miền hosting thì chưa có.
  **Sửa một lỗ bảo mật thật, phát hiện trong lúc làm:** `php/config.php` đang được git theo dõi
  và repo có remote **công khai** `github.com/Phuong2001-maker/so-lenh` → commit tiếp theo là
  mật khẩu DB + token lên GitHub. Lịch sử kiểm lại còn sạch (mọi giá trị trong commit `1d93f1d`
  đều rỗng), nên chỉ cần chặn từ đây: `git rm --cached php/config.php` (file local còn nguyên,
  **đã staged, chưa commit**), thêm `php/config.php` vào `.gitignore`, và tạo
  `php/config.mau.php` làm bản mẫu rỗng để lần clone sau vẫn biết phải điền gì.
  ⚠ Sửa cấu trúc config từ nay phải sửa ở **cả hai** file.
  Thêm `php/.htaccess` chặn HTTP đọc trực tiếp `config*.php` — không phải phòng người ta gọi
  file PHP, mà phòng lúc handler PHP hỏng khiến Apache trả `.php` dạng văn bản thuần.
  **Ghi lại hai điều chưa xử lý:** (a) `bao-cao.php` require config nhưng **không** gọi
  `kiemTraToken()` nên ai có URL đều đọc được toàn bộ sổ kèo — cố ý để mở được bằng trình duyệt
  thường, muốn khoá thì dùng HTTP basic auth ở `.htaccess` chứ đừng thêm token; (b) database
  trong ảnh chụp panel là **mới tạo**, nên `db/tao-database.sql` rất có thể chưa chạy trên nó.
  Đã kiểm `node --check` trên khối `<script>`: **OK**. Không lint được PHP (máy vẫn không có PHP).
  Số dòng index.html giữ nguyên **1470** — cố ý sửa đúng 1 dòng tại chỗ để mọi mốc dòng trong
  tài liệu này không bị lệch.

- **2026-08-09 (13)** — Phiên đọc tài liệu, **không đụng code**. Đối chiếu lại tài liệu với
  thực tế trên đĩa: `index.html` đúng 1470 dòng, các mốc dòng trong tài liệu (`taoEngine` 171,
  `HE_SO_CAT` 1096, `VER_TRONG_SO` 1101, `keoCua` 1110, `sinhKeo` 1303, `API_LOG`/`API_TOKEN`
  149–150) đều còn khớp; `API_LOG` xác nhận vẫn là chuỗi rỗng nên tầng ghi vẫn tắt hẳn.
  **Sửa một chỗ SAI tại chỗ:** mục "Trạng thái & hướng đi" ghi bảng `keo` có 47 cột — đếm lại
  trong `db/tao-database.sql` là **49**; con số cũ là lần xác nhận trước khi thêm `kq_dong` và
  `cham_luc`. Cùng chỗ đó còn ghi bảng rỗng "vì thiếu luật sinh kèo và ba file PHP" — cũng sai,
  cả hai đã viết xong từ lần (11)/(12); lý do thật là `API_LOG` để trống.
  **Ghi thêm một bẫy chưa kiểm chứng:** DDL dùng `CREATE TABLE IF NOT EXISTS`, nên nếu chạy vào
  database đã có bảng `keo` bản 47 cột thì hai cột mới KHÔNG được thêm và `cham.php` sẽ lỗi lúc
  ghi — phải kiểm bằng `SHOW COLUMNS FROM keo LIKE 'cham_luc';` trước khi cài cron.

- **2026-08-09 (12)** — Gộp 3 file SQL thành **một file duy nhất** `db/tao-database.sql`
  theo yêu cầu (chủ dự án tạo database mới, muốn chạy một lần là xong). Xoá
  `01-tao-bang.sql`, `02-truy-van-bao-cao.sql`, `03-nang-cap.sql`.
  File mới: bảng `keo` **49 cột** (đã gồm `kq_dong` ngay từ đầu) + bảng `phi` + VIEW
  `v_keo` + dòng phí nền, **không ghi cứng tên database** nên chạy vào DB nào đang chọn,
  và chạy lại bao nhiêu lần cũng an toàn. Truy vấn tham khảo để trong khối `/* */` cuối
  file, không tự chạy. **Thêm cột `cham_luc`** (lúc cron chấm xong) — trước đây không có
  cách nào giám sát sức khoẻ của chính bộ chấm sổ, mà "không ai giám sát resolver" là một
  trong ba lỗ hổng critic đã cảnh báo; `cham.php` đã cập nhật để ghi cột này.
  `config.php` nay để trống `DB_NAME` để điền tên database mới.
  **Ghi lại một bẫy vận hành:** dán SQL vào tab SQL của phpMyAdmin hay báo
  *"Missing value in the form!"* dù câu lệnh đúng — do trình soạn thảo CodeMirror không
  đồng bộ được nội dung vào textarea ẩn. Dùng tab **Import** thì không dính.

- **2026-08-09 (11)** — **Viết xong tầng log giai đoạn 2.** 1355 → 1470 dòng + thư mục `php/`.
  Thêm `sinhKeo()` (luật sinh kèo, ngưỡng ghi 15 và bitmask `chan` thay cho ý tưởng kèo bóng),
  `VER_TRONG_SO` (hash tự sinh từ tham số), hàng đợi gửi theo lô 10 giây fire-and-forget với
  timeout 5s. Bốn file PHP: `config.php`, `ghi.php` (nhận lô, chống trùng bằng `uid`),
  `cham.php` (cron, chấm hai quy ước, có `GET_LOCK` chống hai tiến trình), `bao-cao.php`.
  Cộng `db/03-nang-cap.sql` thêm cột `kq_dong`.
  **Đã kiểm chứng bằng app chạy thật:** 5 kèo sinh ra trên 8 coin, `chan` phân loại đúng
  (BEAT/PEOPLE `chan=0` kèo thật, MUBARAK `chan=8` bão hoà không xác nhận, ESP/BICO `chan=1`),
  giá đúng dạng chuỗi 15 số lẻ không có ký hiệu mũ, ULID 26 ký tự, đủ 39 trường.
  **Một lỗi tự bắt được khi rà lại:** phân trang nến bằng `before` sẽ thủng đoạn giữa khi
  khoảng dài hơn 300 nến — đã đổi sang phân trang lùi bằng `after`.
  **Chưa lint được PHP** (máy không có PHP) — chỉ rà tay, cần chạy thử trên hosting.

- **2026-08-09 (10)** — **Bước 0: sửa đầu vào trước khi bật đo** + định nghĩa kế hoạch
  giao dịch đầy đủ. 1251 → 1354 dòng. Thêm thư mục `db/` chứa DDL.
  **Năm việc bước 0** (đều là sửa dữ liệu hỏng, không phải thêm tính năng — phải xong TRƯỚC
  khi ghi dòng log đầu tiên vì mất là mất vĩnh viễn):
  (a) **WS `liquidation-orders`** — MỘT socket dùng chung toàn sàn, định tuyến về engine theo
  `instId`. Vá lỗ hổng lớn nhất: `sLiq` trọng số 0.20 trước chỉ có dữ liệu Binance. Đã kiểm
  chứng subscription được chấp nhận và dữ liệu thật chảy về (PEOPLE nhận 10 sự kiện $7.366).
  (b) **`pushTrade` tách gross** mua/bán — `flowSec` nay là `{t, n, mua, ban}`. Trước đây
  `f30 = 0` lẫn lộn "chợ chết" với "hai phe ngang cơ"; đo thực tế mua $85.883 / bán $153.342.
  (c) **`aggregate` trả `bbOkx`/`baOkx`** — best bid/ask THẬT của OKX, trước chỉ tính mà không
  trả ra nên spread thật không có.
  (d) **`listTime`** bắt từ endpoint `/public/instruments` mà `startOkx` vốn đã gọi → tuổi coin.
  Hiển thị cảnh báo nếu coin < 7 ngày. **Cố ý KHÔNG chặn cứng** — chặn hay không là quyết định
  nên dựa trên dữ liệu, mà dữ liệu thì chưa có; hiện chỉ cảnh báo và sẽ ghi vào log để đo sau.
  (e) **WS `open-interest`** thay poll REST — độ phân giải ~8 lần cao hơn mà giảm request,
  có sẵn `oiUsd`. `pollMarket` nay chỉ còn gọi funding.
  **Kế hoạch giao dịch:** thêm `cat` (cắt lỗ, vượt qua tường vào 0.30 × biên khung), `rr`, và
  **`baseline`** — xác suất chạm chốt trước nếu giá đi ngẫu nhiên, hiển thị thành "cần thắng
  > N% mới hơn ngẫu nhiên". Không có ba thứ này thì bảng log đo ra con số vô nghĩa.
  **DDL:** `db/01-tao-bang.sql` (bảng `keo` 47 cột + bảng `phi` + VIEW `v_keo`) và
  `db/02-truy-van-bao-cao.sql` (7 truy vấn, gồm khoảng tin cậy Wilson và ba con số biên cho
  nhóm nhập nhằng). Chưa có tầng PHP nên chưa ghi gì — mới chỉ là lược đồ.

- **2026-08-09 (9)** — Kiểm chứng API cho 4 yêu cầu (chống lệnh giả / khung giờ / sự kiện /
  cá mập). Không đụng code — kết quả ghi thành mục **"Bản đồ API"** phía trên.
  **Ba phát hiện đảo ngược, đều test live:** (a) OKX có WS `liquidation-orders` và
  `open-interest` không cần đăng nhập, không dính CORS — xoá phần lớn lý do phải chờ backend,
  và vá được lỗ hổng `sLiq` (trọng số 0.20) hiện chỉ có dữ liệu Binance; (b) `books-l2-tbt`
  cần VIP4/VIP5 nên **trần dữ liệu sổ lệnh đã chạm** — chống lệnh giả phải giải bằng thuật
  toán, không có API nào cứu; (c) OKX **có** công khai vị thế lead trader nhưng AUM lớn nhất
  toàn sàn chỉ 8,5 triệu USDT so với 2,06 tỷ OI của BTC — **0,4%**, tức là KOL copy trading
  chứ không phải cá mập. **Hai kết luận âm-tính đáng giá:** hướng theo khung giờ cần 6,6 năm
  dữ liệu mỗi coin nên vĩnh viễn không đo được; và "giờ funding là giờ biến động mạnh" là
  niềm tin sai (biên độ chỉ +9,6%, t=1,33). Cũng ghi lại nguyên tắc bị vi phạm xuyên suốt:
  không trộn chân trời thời gian khác nhau vào một điểm số vô hướng.

- **2026-08-09 (8)** — **Giá vào / giá chốt nay lấy từ SỔ RIÊNG OKX**, không còn từ sổ gộp
  4 sàn. 1231 → 1251 dòng. `aggregate(E)` trả thêm `bidsOkx`/`asksOkx` (tiền thật trên OKX,
  nhưng vẫn mang `ex` bitmask gộp để biết mấy sàn xác nhận); `bestWall`, `pickLevels`,
  ETA và tường tầm xa đều chuyển sang dùng bộ này. Sổ gộp giữ nguyên cho `sBook`, `bookM`
  và `updateWalls` (để không mất xác nhận chéo sàn `cx` trong `wallTrust`).
  **Lý do — đo thực tế:** sổ OKX chỉ chiếm **14–44%** tiền của sổ gộp trên 3 coin thử.
  Mọi mức giá app đưa ra trước đây đều bị thổi phồng, và tường "lớn" có thể gần như không
  tồn tại trên sàn mà chủ dự án thật sự đặt lệnh. Nhãn cảnh báo đổi từ "⚠1 sàn" thành
  "⚠ chỉ OKX có" / "N sàn cùng có" cho đúng nghĩa mới.
  Danh sách coin vốn đã chỉ lấy từ ticker OKX nên không phải sửa gì.

- **2026-08-09 (7)** — Bỏ footer (đoạn giải thích cách đọc số + dòng miễn trừ trách nhiệm)
  cùng CSS của nó. 1241 → 1231 dòng. Giao diện nay **chỉ còn 2 khối**: QUÉT OKX và lưới
  LONG/SHORT. Lưu ý đã ghi ở mục Giao diện: app không còn dòng miễn trừ trách nhiệm nào.

- **2026-08-09 (6)** — Bỏ nốt thanh tiêu đề trên cùng (tên app + dòng trạng thái + nút
  TỰ ĐỘNG). Giao diện còn đúng 2 khối: QUÉT OKX → hai bảng LONG/SHORT. 1258 → 1241 dòng.
  **Thay nút TỰ ĐỘNG bằng cơ chế GHIM.** Nút cũ là một trạng thái bật/tắt toàn cục có thể
  **mắc kẹt**: bấm tay một coin là tự động tắt vĩnh viễn, mà bỏ nút đi thì không còn cách
  bật lại ngoài tải lại trang. Cơ chế ghim (`scan.ghim`) không có trạng thái toàn cục nào:
  coin ghim thì máy quét không được gỡ, coin không ghim vẫn xoay vòng bình thường.
  Dòng chẩn đoán (số coin · số sàn live · số coin chưa ấm máy) chuyển vào chú thích
  `#scanNote` cạnh tiêu đề QUÉT OKX — vẫn cần vì hai bảng trống 90 giây đầu, không có nó
  thì không phân biệt được với app hỏng. **Đã kiểm chứng:** ghim một coin chưa theo dõi
  thì nó được bật và gỡ đúng con không-ghim yếu nhất; bỏ ghim thì engine vẫn chạy tiếp.

- **2026-08-09 (5)** — Rút gọn giao diện còn 3 khối: thanh đầu → QUÉT OKX → hai bảng
  LONG/SHORT. Logic giữ nguyên tuyệt đối. 1451 → 1258 dòng.
  **Đã bỏ:** dải `#idleStrip` (đứng ngoài / đang khởi động), toàn bộ khối chi tiết
  (`renderChiTiet`, `renderPredict`, `renderLvl`, `renderEx`, `exSig`, `coinChon`, `tRow`,
  `sigRow` cùng mọi phần tử `#pLean`/`#pBasis`/`#pAlert`/`#pGrid`/`#lm*`/`#d*`), khối CÁN CÂN
  SỔ LỆNH (và hai trường `pred.buyM`/`pred.sellM` chỉ phục vụ nó), CSS của 4 chấm trạng thái sàn.
  **Đã chuyển:** vùng giao dịch và bản đồ mốc **vào thẳng từng thẻ coin** trong bảng LONG/SHORT
  qua hàm mới `theCoin(E, K)` — trước đây chúng là hai panel riêng chỉ hiện cho một coin.
  QUÉT OKX chuyển lên trên cùng; bấm một dòng nay là bật/tắt theo dõi coin đó.
  **Bù lại phần mất:** vì coin không có tín hiệu nay hoàn toàn vô hình, dòng `#tSub` trên thanh
  đầu gánh thêm phần chẩn đoán (số coin theo dõi · số sàn live · số coin chưa ấm máy).
  **Nhịp vẽ hạ từ 200ms xuống 1000ms** vì mỗi lần vẽ giờ dựng lại toàn bộ thẻ kèm bản đồ mốc;
  dữ liệu vốn chỉ đổi mỗi 2 giây nên không mất thông tin. Đo lại: dựng lại cả hai bảng hết 0,1ms.

- **2026-08-09 (4)** — **ĐA COIN.** Chuyển từ phân tích một coin sang theo dõi song song
  `SO_COIN` coin (mặc định 8), hiển thị hai bảng LONG / SHORT kèm giá vào và giá chốt.
  Thuật toán chấm điểm giữ NGUYÊN, chỉ đổi chỗ chứa trạng thái. 1281 → 1451 dòng.
  **Refactor:** mọi biến toàn cục (`conns`, `gen`, `wallBook`, `flowSec`, `liqs`, `midHist`,
  `market`, `pred`, `lvlWatch`, `brkAlert`, `engineStart`) gom vào object engine do
  `taoEngine(sym)` tạo; mọi hàm phân tích nhận `E` làm tham số đầu; `gen` thành `E.gen`
  riêng từng coin. Bỏ `start()`, `curSym`, `resetEngine()` — thay bằng `batDauEngine()` /
  `dungEngine()` / `dongBoEngine()`. **Thêm:** `keoCua(E)` định nghĩa giá vào (tường cùng
  phía) và giá chốt (tường đối diện); cờ `pred.phat` gom điều kiện phát tín hiệu về một chỗ
  để bảng và ô chi tiết không nói lệch nhau; hysteresis chọn coin (vùng đệm top+4, gỡ tối đa
  2 con/vòng). **Lợi ích ngoài dự tính:** `wallBook` không còn bị reset mỗi 15 phút, tức xoá
  được lỗi "kèo mù" đã ghi ở mục Nguyên tắc đo lường. **Đã đo thực tế:** 8 engine, 40
  WebSocket, 28/32 sàn live, một vòng phân tích đầy đủ ~6ms trên ngân sách 2000ms.
  **Lỗi đã sửa trong lúc làm:** điều kiện dừng vòng lặp trong `dongBoEngine` viết sai làm
  chỉ bật được 5/8 engine.

- **2026-08-09 (3)** — Chốt định hướng dự án và mở rộng tài liệu (không đụng code).
  Chủ dự án xác định mục tiêu cuối là **bot phân tích giá coin gồm 3 phần: giao diện + database +
  thuật toán AI**, và yêu cầu AI làm việc trong repo này đóng vai **Kiến trúc sư Hệ thống kiêm
  Chuyên gia AI về Crypto** → thêm mục Vai trò ở đầu file, thêm lộ trình 3 giai đoạn, siết lại quy
  tắc bắt buộc (cập nhật *bất cứ thứ gì*, không riêng code). Ghi lại toàn bộ kết luận đợt phân tích
  thiết kế log (5 góc nhìn + 3 vòng phản biện) vào mục Trạng thái & hướng đi: 3 quyết định kiến trúc,
  2 nguyên tắc sàng lọc cột, bảng cột bắt buộc theo mức thiết yếu, và 7 nguyên tắc đo lường.
  **Phát hiện quan trọng đã sửa tại chỗ:** mục "3 điểm cắm API" viết ở lần cập nhật trước là SAI —
  `analyze()` không còn sinh kèo nào sau đợt dọn log, nên phải viết lại luật sinh kèo trước khi
  tạo bảng. Cũng ghi thêm 3 lỗi trong code phải sửa trước khi bật log (`pushTrade` mất gross và mất
  sàn nguồn; `aggregate` không trả `bb`/`ba`; độ phủ sổ 4 sàn khác nhau làm bitmask `ex` sai lệch
  ở mốc xa). Hoãn phần DDL và 3 file PHP sang lượt sau theo yêu cầu.

- **2026-08-09 (2)** — Gỡ 3 phần giao diện theo yêu cầu + thiết kế lại bố cục (1360 → 1281 dòng).
  **Đã bỏ triệt để:** (a) ô nhập mã coin — `start()` nay nhận mã qua tham số, coin chỉ đến từ máy quét
  hoặc chế độ TỰ ĐỘNG, kèm theo là các handler gõ phím và `typeTimer`; (b) panel SĂN ĐỈNH — xoá
  `renderTop()`, cả khối chấm 6 dấu hiệu kiệt sức trong `analyze()`, `pred.top`, `market.pumpLow`,
  và `L30`/`S30` trong `liqStats()`; (c) bảng giá sổ lệnh — xoá `rowHtml()`, `fmtQty()`, `#book`/`#asks`/
  `#bids`/`#mid` cùng toàn bộ CSS của chúng. **Thiết kế lại:** thanh trên cùng dính, khối nhận định
  làm điểm nhấn, lưới 2 cột, bản đồ mốc có thanh tỉ lệ độ lớn tường + huy hiệu độ tin màu, cán cân
  sổ lệnh đổi từ con số khô sang thanh tỉ lệ mua/bán. **Thêm mới:** `renderEx()` — 4 chấm trạng thái
  sàn trên thanh đầu, bù cho việc mất bảng giá (trước đây nhìn bảng trống là biết sàn chưa lên).
  **Hệ quả cần biết:** giờ chỉ với tới được 10 coin trong máy quét + coin do TỰ ĐỘNG chọn; muốn xem
  coin bất kỳ phải thêm lại ô nhập hoặc nới số dòng trong `renderScan()`.

- **2026-08-09 (1)** — Gỡ sạch toàn bộ tầng ghi log (−235 dòng, 1586 → 1360). Xóa: `persistEngine`/`loadEngineState` và 3 key localStorage (`plog_`/`walls_`/`thist_`), biến `predLog` cùng 5 chỗ ghi sổ (kèo hướng, `zone`, `top`, `far`, `brk`), vòng chấm đúng/sai, `resolveFromCandles` (chấm hồi tố bằng nến 15m), 5 hàm `selfScore*` và 4 chỗ hiển thị "đúng x/y", băng điểm `thist`, `exportLog` + nút ⬇ Log. **Giữ lại `wallBook` trong RAM** vì `wallTrust()` bắt buộc cần — nay reset khi đổi coin. Mất theo: cảnh báo xác nhận hồi tố "tường bị ĂN thật / tường TỰ RÚT" sau khi vượt mốc (cảnh báo ⚡ ngay lúc vượt vẫn còn). Lý do: chuẩn bị thay bằng MySQL.
