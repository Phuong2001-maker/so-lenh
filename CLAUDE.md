# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## ⚠ QUY TẮC BẮT BUỘC — chủ dự án yêu cầu

**Mỗi lần thay đổi code trong repo này, PHẢI cập nhật lại file `CLAUDE.md` ngay trong cùng lượt làm việc**, trước khi báo cáo hoàn thành. Tối thiểu:

- Ghi một dòng vào mục **Nhật ký thay đổi** ở cuối file (ngày + việc đã làm + lý do).
- Sửa lại các mục Kiến trúc / Số dòng / Bẫy đã gặp nếu thay đổi làm chúng sai.

Mục đích: mở chat mới, AI đọc đúng file này là hiểu ngay dự án đang ở đâu, không phải đọc lại 1300 dòng.

---

## Dự án là gì

Công cụ đọc **sổ lệnh gộp 4 sàn futures USDT** (Binance, Bybit, OKX, Bitget) kèm một máy phân tích tự đưa ra nhận định nghiêng LONG/SHORT. Toàn bộ giao diện và comment trong code đều bằng **tiếng Việt** — giữ nguyên quy ước này khi sửa.

Điểm khác biệt so với một widget order book thường: nó **không tin sổ lệnh một cách mù quáng**. Có hẳn một bộ máy theo dõi từng "tường" (mốc tiền lớn) qua thời gian để phân biệt tiền thật với lệnh ảo treo làm cảnh.

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

Một file, ~1360 dòng, chia 4 tầng chồng lên nhau. Đọc theo thứ tự này sẽ hiểu nhanh nhất:

**1. Tầng kết nối (dòng 162–453)** — mỗi sàn một hàm `startXxx(sym, g, ...)` riêng, tất cả ghi vào `conns[key]` với `bids`/`asks` là `Map<priceStr, qty>` theo **đơn vị gốc của sàn**, kèm `pMul`/`qMul` để quy đổi. Biến `gen` là bộ đếm thế hệ: mỗi lần đổi coin thì `gen++`, mọi callback cũ tự thấy `g !== gen` và im lặng thoát — đây là cơ chế chống race duy nhất, **đừng bỏ nó khi sửa**.

**2. Tầng gộp (`aggregate()`, dòng 978)** — trộn 4 sàn thành một sổ chung: loại sàn lệch giá >15% (sai hệ số hợp đồng), tự tính bước giá gom mốc, lọc lệnh bụi. Trả về `{bids, asks, mid, step}`, mỗi hàng có `ex` là **bitmask sàn nào đang có tiền ở mốc đó** (dùng để cảnh báo "⚠1 sàn").

**3. Tầng phân tích (dòng 453–953)** — `updateWalls()` rồi `analyze()`. Đọc kỹ mục dưới.

**4. Tầng hiển thị (dòng 957–1240)** — `render()` gọi lại `aggregate()` rồi vẽ 4 panel: Bản đồ mốc, Săn đỉnh, Ô nhận định, Sổ lệnh.

Điều phối đổi coin nằm ở `start(force)` (dòng 1244): tăng `gen`, đóng hết socket cũ, `resetEngine()`, rồi khởi động lại cả 4 sàn.

Cộng thêm **máy quét OKX (dòng 1262–1355)** chạy độc lập: quét toàn bộ ticker SWAP mỗi 60s, xếp top tăng/giảm, và ở chế độ TỰ ĐỘNG sẽ tự nạp coin "nóng" nhất vào phân tích (tối đa 15 phút đổi một lần, và chỉ đổi khi con mới nóng hơn 1.5×).

### Hai nhịp tim

| Nhịp | Chu kỳ | Việc |
|---|---|---|
| Phân tích | 2000ms (dòng 955) | `aggregate()` → `updateWalls()` → `analyze()` |
| Vẽ | 100ms (dòng 1231) | `render()` — chỉ đọc `pred`, không tính toán nặng |

Tách đôi như vậy có chủ đích: giao diện mượt mà máy phân tích không chạy quá dày. **Đừng gộp lại.**

## Máy nhận định (`analyze`, dòng 675)

Điểm tổng hợp `S` (thang ±100) từ 6 tín hiệu, mỗi tín hiệu chuẩn hóa về −1..1:

| Tín hiệu | Trọng số | Nguồn |
|---|---|---|
| `sFlow30` — dòng tiền khớp 30 phút | 0.30 | aggTrade/publicTrade 4 sàn |
| `sLiq` — thanh lý | 0.20 | forceOrder (Binance) |
| `sFlow5` — dòng tiền khớp 5 phút | 0.15 | như trên |
| `sBook` — sổ lệnh đã lọc theo độ tin | 0.15 | `aggregate()` × `wallTrust()` |
| `sOi` — Open Interest so 25 phút trước | 0.10 | OKX REST |
| `sFund` — funding **ngược đám đông** | 0.10 | OKX REST |

Bốn chốt chặn buộc máy đứng ngoài (đều nằm cuối `analyze` và trong `renderPredict`):

- `|S| < 25` hoặc dưới 2 tín hiệu hoạt động → chưa rõ hướng
- `flowConflict` — dòng 5' ngược hẳn dòng 30' (thị trường đảo nhịp)
- `satNoConfirm` — dòng tiền kịch kim cả 2 khung mà sổ lệnh lẫn OI đều không xác nhận (nghi đuổi đuôi cú chạy)
- `satPen` — bão hòa thì giảm 20% trọng số dòng tiền

**Bốn chốt này không phải lý thuyết** — chúng được thêm vào sau khi phân tích log kèo thật, số liệu cụ thể ghi ngay trong comment tại dòng 723–733 ("log 79 kèo", "bão hòa 50% vs vừa 62%"). Đừng gỡ ra vì "trông thừa".

Khung thời gian `frameH` tự chọn theo độ nóng: coin chạy ≥3% trong 1h thì lấy khung 1h, không thì 2h, không nữa thì 4h. Biên `cap` là **trung vị** biên dao động của khung đó trong 48h qua (nến 15m từ OKX).

## Bộ theo dõi tường (dòng 500–579) — phần cốt lõi

Đây là thứ làm nên giá trị của app. Mỗi mốc tiền lớn được lưu vào `wallBook` (key = `b:giá` hoặc `a:giá`, giá lấy 6 chữ số có nghĩa) và chấm điểm tin cậy 0–100 qua `wallTrust()`:

- Sống lâu → cộng điểm, **nhưng chỉ nửa điểm nếu chưa từng bị giá thử** (treo lệnh ảo ở xa chẳng tốn gì)
- `sv` chịu được giá áp sát rồi giá rời đi mà tường còn → **+15/lần**
- `fl` bị khớp thật, ăn mòn dần → **+8/lần** (tiền thật)
- `pl` biến mất nguyên khối khi giá áp sát → **−30/lần** (lệnh ảo, phạt nặng)
- Xuất hiện trên ≥2 sàn → +10, ≥3 sàn → +5

`wallBook` **chỉ sống trong RAM**, reset sạch khi đổi coin hoặc tải lại trang. Hệ quả cần biết: sau mỗi lần F5, mọi tường về mốc tin 30 và phải mất vài phút mới "lên tin" lại.

Ngưỡng "áp sát"/"rời xa" tính theo **bước giá** (`3×step` / `8×step`), có trần theo % — không dùng % cố định. Lý do ghi ở comment dòng ~524: ngưỡng % cố định từng làm sổ dày như BTC bị chấm oan hàng loạt.

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

## Trạng thái & hướng đi

Hiện tại app **không lưu gì xuống đĩa** — toàn bộ tầng ghi log localStorage đã bị gỡ (xem Nhật ký thay đổi). Dữ liệu chỉ sống trong RAM và mất khi tải lại trang.

Kế hoạch: deploy lên hosting (1Panel + MySQL, DB `buwsofujhosting_coin_db` đã tạo sẵn) và ghi log qua một tầng API PHP. Ba điểm cắm API khi làm:

1. `analyze()` — nơi trước đây sinh kèo mới
2. Cuối `analyze()` — nơi trước đây cập nhật kết quả kèo (chấm đúng/sai)
3. `updateWalls()` — trạng thái tường

Lưu ý thiết kế đã thống nhất: **không ghi trạng thái tường liên tục** (13 tường × mỗi 2 giây = hàng chục nghìn lượt ghi/giờ cho dữ liệu bị đè ngay). Chỉ ghi khi tường kết thúc vòng đời — bị rút, bị ăn hết, hoặc biến mất. Sáu tín hiệu thành phần phải tách **sáu cột riêng**, không nhét chung JSON, vì mục đích chính của bảng log là lọc/gộp nhóm theo chúng để hiệu chỉnh trọng số.

## Nhật ký thay đổi

Commit gần nhất trước khi có file này: `5d4e6f7` (máy quét OKX + lãi tự động).

- **2026-08-09** — Gỡ sạch toàn bộ tầng ghi log (−235 dòng, 1586 → 1360). Xóa: `persistEngine`/`loadEngineState` và 3 key localStorage (`plog_`/`walls_`/`thist_`), biến `predLog` cùng 5 chỗ ghi sổ (kèo hướng, `zone`, `top`, `far`, `brk`), vòng chấm đúng/sai, `resolveFromCandles` (chấm hồi tố bằng nến 15m), 5 hàm `selfScore*` và 4 chỗ hiển thị "đúng x/y", băng điểm `thist`, `exportLog` + nút ⬇ Log. **Giữ lại `wallBook` trong RAM** vì `wallTrust()` bắt buộc cần — nay reset khi đổi coin. Mất theo: cảnh báo xác nhận hồi tố "tường bị ĂN thật / tường TỰ RÚT" sau khi vượt mốc (cảnh báo ⚡ ngay lúc vượt vẫn còn). Lý do: chuẩn bị thay bằng MySQL.
