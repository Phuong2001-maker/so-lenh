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

## 📍 ĐANG LÀM DỞ ĐẾN ĐÂU (cập nhật 2026-08-09, cuối phiên)

**Giai đoạn 2 (database) đã xong code và đã qua một đợt rà soát 25 agent. Đang kẹt ở khâu hạ tầng.**

| Việc | Trạng thái |
|---|---|
| Lược đồ MySQL (`db/tao-database.sql`) | ✅ đã chạy trên hosting |
| `php/config.php` | ✅ đã điền (có `.gitignore` che, bản mẫu là `config.mau.php`) |
| `API_LOG` / `API_TOKEN` trong index.html | ✅ đã điền |
| Tải `php/` lên hosting | ✅ **xong, đã kiểm bằng curl thật** — xem bảng dưới |
| Tên miền + bản ghi DNS | ✅ xong — `zewvir85` A → `103.75.186.15`, site addon PHP 8.1 đã tạo |
| VIEW `v_keo` + ENUM `khong_khop` | ✅ đã Import lại SQL, `bao-cao.php` từ 500 → **200** |
| Cấp SSL | ✅ **xong** — ZeroSSL, "SSL Tự động" nên tự gia hạn. Đã kiểm TLS bắt tay + cả 4 endpoint qua HTTPS |
| CORS từ `file://` | ✅ preflight OPTIONS trả **204** với `access-control-allow-origin: null` |
| Đường ống ghi dữ liệu | ✅ **CHẠY THẬT** — xem phần kiểm chứng dưới |
| Cron `cham.php` | ✅ **CHẠY THẬT** — xác nhận `MAX(cham_luc)` lệch `NOW()` chỉ **53 giây** |
| **Bot 24/24 + trang xem chỉ-đọc** | ✅ **ĐANG CHẠY TRÊN SERVER** (2026-08-10 09:35) — xem dưới |

### ✅ ĐANG CHẠY THẬT 24/24 — trạng thái lúc bàn giao

Bot Node chạy nền trên hosting, **không cần máy nào bật**. Xác nhận từ ngoài internet:

| | |
|---|---|
| Tuổi ảnh trạng thái | 1 giây (bot ghi đúng nhịp 2 giây) |
| Coin / sàn | 8 coin · 30/32 sàn live |
| Log | 6 gửi / **0 lỗi** / 0 mất / 0 thiếu nến |
| Socket thanh lý | gói tin 5 giây trước, không lỗi |
| Máy quét OKX | 35 giây trước, 0 lỗi |
| RAM | ~117 MB |

**Hai cron trên hosting** (1Panel → Tính năng nâng cao → Công việc Cron):

| Lịch | Lệnh | Việc |
|---|---|---|
| `* * * * *` | `php /home/buwsofujhosting/domains/zewvir85.hiteckqualityconstruction.com.au/php/cham.php` | chấm kèo |
| `* * * * *` | `cd /home/buwsofujhosting/bot && ./chay.sh > /dev/null 2>&1` | tự bật lại bot nếu chết |

Cron chấm sổ dùng **PHP CLI** chứ không `curl`: không để token trong crontab, và không bị giới hạn thời gian chờ của web. `cham.php` tự bỏ qua kiểm token khi gọi bằng dòng lệnh (`CHAY_CLI`).

Cron bot đặt **mỗi phút** thay vì mỗi 5 phút — cố ý giữ: `chay.sh` có `flock` + kiểm PID qua `/proc` nên khi bot đang chạy nó thoát ngay, chi phí gần bằng 0, mà bù lại bot chết thì được bật lại trong vòng 1 phút.

**Ba đường dùng hằng ngày:**

| Việc | Cách |
|---|---|
| Xem lệnh | `https://zewvir85.hiteckqualityconstruction.com.au/` |
| Xem số | `https://zewvir85.hiteckqualityconstruction.com.au/php/bao-cao.php` |
| Ghim coin | sửa `~/bot/ghim.txt`, bot đọc lại sau 30 giây, **không cần restart** |

⚠ **`gui` đứng yên KHÔNG phải kẹt.** Mỗi coin chỉ được có một kèo mở tại một thời điểm, hạn 1–4 giờ → nhịp ra kèo ~20–40/ngày. Thấy `gui` không tăng trong một giờ là bình thường; thứ phải theo dõi là `loi` và `mat`.

## 🎉 GIAI ĐOẠN 2 ĐÃ HOÀN TẤT (2026-08-10 01:12)

Cả ba khâu chạy thật, đã kiểm chứng chứ không suy đoán: app ghi kèo (21 kèo), cron chấm mỗi phút, trang báo cáo đọc được. **Việc duy nhất chủ dự án phải làm từ giờ là để tab `index.html` mở.**

Cron dùng `curl` chứ không `php` CLI — vì đường HTTP đã kiểm chứng chạy thật, còn PHP CLI trong cron thì chưa biết có sẵn không. Lệnh trong 1Panel → Tính năng nâng cao → Công việc Cron:

```
* * * * * curl -s -o /dev/null "https://zewvir85.hiteckqualityconstruction.com.au/php/cham.php?token=<API_TOKEN>"
```

⚠ Nếu về sau số kèo mở lên hàng trăm và lần chấm quá thời gian chờ của web, đổi sang PHP CLI — không giới hạn thời gian và không để token trong crontab:
`php /home/buwsofujhosting/domains/zewvir85.hiteckqualityconstruction.com.au/php/cham.php`

**Câu kiểm sức khoẻ cron** (chạy định kỳ, đây là lý do cột `cham_luc` tồn tại):

```sql
SELECT MAX(cham_luc) AS lan_cham_cuoi, NOW() AS gio_server FROM keo;
```

Lệch dưới 2 phút là khoẻ. Lệch hàng giờ = cron chết, mà **trang báo cáo vẫn trông bình thường** nên không có cách nào khác để phát hiện.

### Mốc quay lại xem dữ liệu

- **~100 kèo:** kiểm **lỗi thô** — % kèo có R:R < 1, % sinh khi máy nguội (`tuoi_may < 300`), tỷ lệ `khong_khop`. KHÔNG chỉnh trọng số.
- **~800 kèo:** mới đủ để nói về tỷ lệ thắng ở mức tin cậy 95%.

**✅ Đường ống đã chạy thật (2026-08-10, ~31 phút đầu):** 9 kèo trên 9 coin khác nhau (MMT, NEIRO, SHELL, PUMP, PEOPLE, ESP, BICO, BEAT, MUBARAK) — đúng luật "mỗi coin một kèo mở". Toàn bộ `kq='mo'` vì chưa có cron.

**Bằng chứng bitmask `chan` ghi trung thực** — đối chiếu `chan` với `s` thì khớp hoàn toàn: 6 kèo `chan=0` đều có `|S|` ≥ 31; `PEOPLE chan=3` (1+2) với S=−16.09 đúng là `|S|<25`; `BICO chan=8` với S=−26.09 thì bit 1 **không** bật vì `|S|≥25`; `SHELL chan=9` (1+8) với S=20.00 đúng `|S|<25`. Sự khớp này quan trọng hơn việc có dòng dữ liệu — nó chứng minh nhóm đối chứng dùng được về sau.

⚠ **n=9 và chưa kèo nào có kết quả — không đọc bất cứ điều gì từ đây.** Phân biệt 55% với 50% cần ~780 kèo độc lập.

## 🏗 KIẾN TRÚC HIỆN TẠI — TÁCH GHI KHỎI XEM (2026-08-10)

**Đây là mục quan trọng nhất của tài liệu. Đọc trước khi sửa bất cứ gì.**

```
Bot (Node, server, 24/24) ──ghi──> MySQL        ← tiến trình DUY NHẤT được ghi
      │
      └─ ghi đè mỗi 2 giây ──> trangthai.json
                                     │
                    Trang xem ←──đọc─┘          ← chỉ đọc, KHÔNG ghi gì
```

| Phần | Ở đâu | Việc |
|---|---|---|
| **Bot** | `~/bot/bot.js` (NGOÀI thư mục web) | giữ 40 WebSocket, phân tích, sinh kèo, ghi DB, ghi ảnh trạng thái |
| **Ảnh trạng thái** | `<docroot>/trangthai.json`, ~16 KB | bot ghi đè mỗi 2 giây |
| **Trang xem** | `https://zewvir85.hiteckqualityconstruction.com.au/` | tải ảnh mỗi 2 giây rồi vẽ |

Triển khai: `bot/HUONG-DAN.md` (8 bước).

### Cờ `LA_BOT` — một file, hai chế độ

`index.html` chạy cả hai vai. Chế độ do **cờ `__BOT__`** quyết, và `bot.js` là thứ duy nhất đặt được cờ đó:

- `LA_BOT = true` → chạy engine, `API_LOG` có URL, ghi DB, **không vẽ gì** (bỏ hẳn nhịp vẽ)
- `LA_BOT = false` → **không** bật engine, `API_LOG = ''`, chỉ tải `trangthai.json` rồi vẽ

**Trình duyệt KHÔNG BAO GIỜ vào được chế độ bot.** Đây là bảo đảm ở tầng thiết kế, không phải quy ước: mở link công khai bao nhiêu lần cũng không thể ghi một byte nào vào DB.

⚠ **Giữ MỘT bản hàm vẽ.** `theCoin`/`renderLists`/`renderScan` dùng chung cho cả hai chế độ, và `anhTrangThai()`/`napTrangThai()` là cặp đối xứng nằm cạnh nhau. Tách thành hai bản vẽ là vài tuần sau sửa một bên quên bên kia, rồi trang hiển thị một thứ mà DB ghi thứ khác — không cách nào phát hiện.

### `bot.js` NẠP `index.html` chứ không chép code

`bot.js` đọc khối `<script>` của `index.html` rồi chạy bằng indirect eval. Nên chỉ tồn tại **một** bản thuật toán. Chép sang thành bản thứ hai là: sửa một bên quên bên kia, số bot ghi lệch số trang hiện, và dữ liệu trước/sau lần chép không so sánh được.

Chi phí gần bằng 0 vì cả 1500 dòng chỉ chạm DOM đúng **10 chỗ** (4 `innerHTML`, 3 `textContent`, `document.title`, `getElementById`, `querySelectorAll`) — bản giả lập DOM trong `bot.js` chỉ vài dòng, mà bot còn không vẽ nên gần như không gọi tới.

⚠ **Dùng eval chứ KHÔNG dùng module `vm`.** `vm.createContext` tạo realm khác, làm mọi `instanceof` / `Array.isArray` xuyên realm sai **âm thầm** — đúng loại lỗi tệ nhất cho tiến trình chạy nền không ai xem.

⚠ **`index.html` phải giữ dòng cuối** `if (LA_BOT) globalThis.__botAPI = {...}`. Xoá là bot thoát ngay với thông báo rõ ràng, nhưng đừng để mất.

### Ba hạn chế đã XOÁ được nhờ kiến trúc này

1. ~~Phải treo máy~~ → bot chạy trên server, máy tắt thoải mái.
2. ~~Kèo chồng nhau khi khởi động lại~~ → chỉ một tiến trình ghi, và nó không khởi động lại khi có người mở trang.
3. ~~`wallBook` mất mỗi lần F5~~ → tường tích luỹ liên tục nhiều ngày. Đây là lợi ích lớn nhất về **chất lượng dữ liệu**: độ tin tường sẽ cao hơn hẳn, mà `tuoi_may` là biến có sức giải thích cao nhất theo chính tài liệu này.

Thêm nữa: bot chạy sẵn nên mở trang là **thấy lệnh ngay**, không còn chờ 90 giây ấm máy.

### Hạn chế MỚI sinh ra — đã biết và chấp nhận

- **Trang xem công khai.** Ai có link đều xem được. Chưa đặt mật khẩu theo yêu cầu chủ dự án.
- **Không bấm ghim trên trang được nữa.** Ghim đặt bằng `~/bot/ghim.txt`, bot đọc lại mỗi 30 giây. Khối xử lý click đã **xoá** — giữ lại là một nút nhìn như bấm được mà thật ra bị ghi đè sau 2 giây, tệ hơn không có nút. Muốn nút trở lại thì cần endpoint điều khiển có xác thực, tức phải nhét token vào trang công khai — đúng thứ vừa bỏ được.
- **`file://` không dùng được nữa.** Mở `index.html` từ máy sẽ báo `⛔ CHƯA TẢI ĐƯỢC trangthai.json` vì trình duyệt chặn `fetch` giữa hai file cục bộ. Dùng link.
- **Bot chết là hỏng IM LẶNG.** Trang vẫn vẽ mấy thẻ cũ, người xem tưởng thị trường yên. Nên trang **luôn hiện tuổi của ảnh**, và quá 30 giây thì thay dòng chú thích bằng cảnh báo đỏ. Kèm cron `*/5` tự bật lại bot.

### 🔐 SỰ CỐ TOKEN BỊ LỘ (2026-08-10) — đã xử lý, đọc để không lặp lại

Quét từng commit trong lịch sử git: **mật khẩu DB KHÔNG bị lộ** (`php/config.php` được untrack trước khi điền). Nhưng **`API_TOKEN` cũ (`36crT…`) VÀ URL endpoint `ghi.php` nằm trong `index.html` ở 3 commit `e95e67e` / `2a50184` / `6be817b`, mà `origin/main` = `6be817b` — tức ĐÃ push lên `github.com/Phuong2001-maker/so-lenh`.**

Cách nó xảy ra: một phiên làm việc **song song** commit `index.html` khi token còn nằm trong file. Thay đổi đưa token ra khỏi `index.html` diễn ra SAU đó. Không ai làm gì sai một cách rõ ràng — chỉ là hai việc đúng làm sai thứ tự.

Hậu quả nếu không xử lý: ai đọc được repo đều có URL + token, tức **POST được kèo bịa vào bảng `keo`** — làm bẩn mẫu theo cách không phát hiện được sau này, đúng thứ dự án này không chịu nổi.

**Đã xử lý:** đổi token mới, chỉ nằm trong `php/config.php` (đã `.gitignore`). Token cũ thành vô giá trị nên **không cần viết lại lịch sử git**. Kiểm lại: không file nào trong repo còn chứa token cũ, và `index.html` không còn chứa token nào.

⚠ **Ba quy tắc rút ra:**
1. **Đừng bao giờ dán token thật vào file được commit** — kể cả file hướng dẫn (`bot/HUONG-DAN.md` từng có nó trong lệnh `echo … > token.txt`, đã thay bằng chỗ trống).
2. **Trước khi commit, quét bí mật:** `grep -rln '<token>' . --exclude-dir=.git`
3. **CLAUDE.md đã push có tên DB + tên user** (`buwsofujhosting_coin_db` / `…_coin_user`). Không phải mật khẩu, và MySQL chỉ nghe localhost, nên rủi ro thấp — nhưng nếu repo đang public thì nên chuyển sang **private**, vì đó là thông tin trinh sát miễn phí.

### Bộ lưới an toàn chống hỏng IM LẶNG — đừng gỡ cái nào

Đợt rà soát 70 agent (2026-08-10) tìm ra **24 lỗi thật**, phần lớn cùng một họ: hệ thống *chạy tiếp nhưng sai âm thầm*. Mỗi lưới dưới đây sinh ra từ một lỗi cụ thể, và **đã test cho kêu thật** (11/11 mục):

| Lưới | Chống điều gì |
|---|---|
| `lastData` tách khỏi `lastMsg` | socket còn trả `pong` nhưng kênh books đã im → sổ ĐÓNG BĂNG mà nhãn vẫn `live`, rồi `wallTrust` càng cộng điểm vì tường "sống lâu" → bot sinh kèo từ sổ hàng giờ trước |
| Canh chừng **nối lại thật**, không chỉ đổi nhãn | socket nửa-mở không sinh `onclose` → nằm `wait` vô thời hạn |
| Thử lại state `off` mỗi 5 phút | mất OKX = mất `bidsOkx` = `bestWall` null = coin đó **không bao giờ sinh kèo nữa**, mà `pred.ready` vẫn true và 3 sàn kia vẫn `live`. Coin GHIM không có đường tự hồi phục |
| Cắt `flowSec`/`liqs` **tại chỗ nạp** | hai mảng này chỉ được cắt trong `flowStats`/`liqStats`, mà hai hàm đó không chạy khi `aggregate()` trả null → tăng vô hạn. Đây là đường rò rỉ bộ nhớ thật sự duy nhất |
| `scan.lucQuet`/`loi` + cảnh báo `!!` | `pollScan` từng `catch(e){}` rỗng → bot sống, ảnh vẫn ghi, mà **0 coin được theo dõi vĩnh viễn**, dòng log trông y như bình thường |
| Đếm `thieuNen` | thiếu nến 48h thì `sinhKeo` bỏ mọi kèo trong khi MỌI chỉ báo đều xanh và trang vẫn vẽ đủ thẻ — DB không nhận một dòng nào |
| `logStat.mat` + trần 500 | trần 40 cũ ném kèo **chưa gửi** mà không đếm; token lệch một lần là mất 100% kèo mới trong khi bot vẫn chạy |
| Đo độ trễ bằng **một đồng hồ** | bản đầu trừ đồng hồ trình duyệt cho đồng hồ server → điện thoại lệch NTP là cảnh báo bot-chết im vĩnh viễn (hoặc kêu oan vĩnh viễn, rồi người xem học cách phớt lờ) |
| So `ver` bot ↔ trang | sửa thuật toán mà quên restart bot → bot ghi DB bằng bản CŨ với cột `ver` cũ, trang vẽ theo bản mới. **Không có cách nào khác phát hiện** |
| `veChuThich()` gọi TRƯỚC khi vẽ thẻ | dòng chẩn đoán từng nằm ở cuối `renderLists` → một ngoại lệ khi dựng thẻ là bịt miệng đúng cái lưới an toàn |
| `render()` bọc try + hiện lỗi ra màn hình | `render()` từng nằm ngoài try → trang đóng băng, lỗi chỉ trong Console mà người xem điện thoại không mở |
| Bỏ ảnh không mới hơn | mạng tắc → phản hồi về sai thứ tự → ảnh CŨ ghi đè ảnh MỚI, thẻ nhảy lùi và độ trễ nhảy loạn |
| `AbortController` 1500ms | không có trần thì fetch dồn thành đống (trình duyệt chỉ cho 6 kết nối/host) |
| `napTrangThai` đọc phòng vệ, parse xong mới chạm `engines` | ảnh thiếu khối là ném lỗi giữa đường, để lại `engines` xoá nửa vời và cảnh báo báo SAI nguyên nhân |
| Xoay `bot.log` 20 MB + gom lỗi trùng | một lỗi lặp nhịp 2 giây = ~43.000 stack/ngày → đầy quota → `trangthai.json` ghi lỗi, MySQL ngừng ghi. Một lỗi lẻ hạ cả hệ thống |
| Thoát khi >20 ngoại lệ/60 giây | chạy tiếp với trạng thái hỏng tệ hơn chết CÓ dấu hiệu; cron bật lại sạch |
| `flock` + `bot.pid` xác minh qua `/proc` | `pgrep -f bot.js` bắt cả `vi bot.js` → báo "đang chạy" trong khi bot chết từ lâu, và cron lần nào cũng báo thành công |
| Canh chừng socket **thanh lý** (`lqLastMsg`) + bắt `event:error` | nguồn duy nhất của `sLiq` (**trọng số 0.20**, nặng thứ hai) và nằm NGOÀI mọi engine. Đăng ký bị sàn từ chối thì socket vẫn mở, ping vẫn chạy, mà không bao giờ có dữ liệu → mọi kèo về sau có `sig_lq = NULL`, và vài tuần sau không phân biệt được "không có thanh lý" với "feed đã chết". Ở đây dùng `pong` làm dấu hiệu sống là ĐÚNG (khác các sàn khác) vì thanh lý là sự kiện thưa |
| Canh chừng **ws2 của Binance** (`lastData2`) + `tWs2` một biến | ws2 (aggTrade/forceOrder) sống riêng với socket sổ lệnh nên chết nửa-mở mà depth vẫn `live` → mất nguồn Binance cho `sFlow30` (0.30) + `sFlow5` (0.15) = **45% tổng trọng số**, điểm S lệch hệ thống mà không chỉ số nào nói. Và timer nối lại từng `push` vào `c.timers` — mảng đó chỉ được dọn bởi `clearConn`, mà đường nối lại của ws2 không gọi `clearConn` → mảng lớn dần theo tuần |

⚠ **Bài học kiến trúc quan trọng nhất:** `moSocketThanhLy()` bị bỏ sót cổng `LA_BOT` đúng vì nó là socket **dùng chung toàn sàn**, nằm NGOÀI vòng đời engine. **Mọi thứ đặt ở cấp cao nhất, ngoài `batDauEngine`, phải tự kiểm `LA_BOT`.**

⚠ **Đừng viết `*` `/` `5` liền nhau trong comment khối** — nó đóng comment sớm. Đã dính một lần khi ghi "cron mỗi 5 phút".

### Hai bên giám sát ĐỘC LẬP — phải xem cả hai

```bash
tail -2 ~/bot/bot.log     # bên GHI: dòng cuối phải trong vòng 1 phút
```
```sql
SELECT MAX(cham_luc), NOW() FROM keo;   -- bên CHẤM: lệch < 2 phút
```

Bot chết thì không có kèo mới. Cron chết thì kèo kẹt `mo`. Hai thứ chết độc lập nhau và **không thứ nào tự báo**.

---

### CORS — bẫy so khớp Origin CHÍNH XÁC TỪNG KÝ TỰ

`config.php` so Origin bằng `in_array(..., true)`. Danh sách cho phép `['null', 'http://localhost', 'http://127.0.0.1']` **không kèm cổng**, nên thêm `:5500` là thành chuỗi khác. Đã test thật:

| Origin | CORS |
|---|---|
| `null` (`file://`) · `http://127.0.0.1` | ✅ cho phép |
| `http://127.0.0.1:5500` · `http://localhost:5500` (Live Server) | ⛔ **BỊ CHẶN** |

Đã dính một lần: mở app bằng Live Server thì `log: 0 gửi` mãi mà **không có thông báo lỗi nào trên giao diện** — trình duyệt chặn ở tầng CORS, app chỉ thấy fetch thất bại.

⚠ **Từ kiến trúc bot (2026-08-10) chuyện này gần như không còn liên quan:** bot gọi `ghi.php` bằng Node nên **không có CORS** (CORS là cơ chế của trình duyệt), còn trang xem chỉ đọc `trangthai.json` **cùng tên miền**. Giữ mục này lại vì nếu sau này thêm endpoint nào cho trình duyệt gọi chéo tên miền thì lại dính đúng bẫy đó.

### Lịch sử: ba cách chạy đã thử rồi bỏ

Ghi lại để không ai đi lại đường cũ:

| Cách | Vì sao bỏ |
|---|---|
| Mở tab `file://` bằng tay | đóng tab là ngừng ghi; `wallBook` mất mỗi lần F5 |
| Chrome `--headless=new` + `.bat` | chạy được thật (7–8 tiến trình, ~486 MB, kèo tăng 10→11 trong 5 phút) nhưng vẫn phải bật máy 24/7, và tốn 8× RAM so với Node |
| Cron hút REST mỗi phút thay WebSocket | độ phân giải tụt 2 giây → 60 giây, phá đúng phần chống lệnh ảo — thứ đáng giá nhất của app |

⚠ **Nếu vì lý do gì phải quay lại chạy app trong tab trình duyệt nền, BỐN cờ này là bắt buộc:** `--disable-background-timer-throttling`, `--disable-renderer-backgrounding`, `--disable-backgrounding-occluded-windows`, `--disable-features=CalculateNativeWinOcclusion`. Chrome hạ nhịp `setInterval` xuống 1 lần/phút cho tab nền; vòng phân tích là 2 giây nên app **vẫn chạy nhưng sinh kèo từ dữ liệu cũ cả phút** mà không báo lỗi gì — hỏng im lặng, không phát hiện được từ dữ liệu.

### ✅ ĐÃ SỬA: kèo chồng nhau khi khởi động lại

*(Trước 2026-08-10)* Danh sách "kèo đang mở" chỉ nằm trong RAM client, không đọc từ server, nên mỗi lần tắt/bật lại app là nó quên hết rồi sinh kèo mới cho coin **đang có kèo mở** — hai kèo chồng thời gian trên cùng một coin thì **không độc lập**, phá đúng giả định mà mọi phép đo tỷ lệ thắng dựa vào.

Kiến trúc bot xoá gốc rễ vấn đề: chỉ còn **một** tiến trình ghi, và nó **không** khởi động lại khi có người mở trang xem.

Vẫn nên kiểm định kỳ, vì mỗi lần khởi động lại bot (sửa thuật toán, cron bật lại sau khi bot chết) lại tạo ra đúng tình huống cũ:

```sql
SELECT coin, COUNT(*) n FROM keo WHERE kq='mo' GROUP BY coin HAVING n > 1;
```

Có dòng thì **đừng xoá** — lọc bằng SQL lúc phân tích. Cách sửa gốc còn lại: `sinhKeo` hỏi server danh sách kèo đang mở lúc khởi động (**chưa làm**).

**Đã kiểm bằng `curl` thật qua HTTP (không suy đoán), chỉ có 4 file PHP + `.htaccess` trong `/domains/zewvir85.hiteckqualityconstruction.com.au/php/`:**

| Đường dẫn | Mã | Nghĩa |
|---|---|---|
| `/php/ghi.php` | **405** | ✅ sống. 405 là ĐÚNG — chỉ nhận POST. Trả được 405 nghĩa là `require config.php` thành công |
| `/php/cham.php` | **401** | ✅ sống, đòi token khi gọi qua HTTP |
| `/php/config.php` | **403** | ✅ `.htaccess` đang hoạt động. Trước khi có nó là **200 với thân rỗng** — PHP thực thi nên không lộ mã, nhưng 403 mới là đúng |
| `/php/bao-cao.php` | **200** | ✅ HTML đúng. Trước đó **500** vì VIEW `v_keo` là bản cũ, thiếu `khong_khop` / `thang01_dong` / `chot_bp` / `spread_bp` — Import lại `db/tao-database.sql` là hết |

⚠ **Bẫy đã dính: import DDL rồi cập nhật DDL sau thì database bị tụt lại.** Chủ dự án import `tao-database.sql` TRƯỚC khi đợt rà soát 25 agent thêm `khong_khop` vào VIEW và 2 ENUM, nên DB có VIEW cũ trong khi `bao-cao.php` là bản mới → 500 với thân trang RỖNG (hosting tắt `display_errors`, không có thông tin gì để lần). **Mỗi lần sửa `db/tao-database.sql` là phải Import lại**, file idempotent nên chạy lại vô hại.

⚠ **Hosting CHÈN script vào mọi trang HTML:** `<script src="/_osh/collect.js" async>`. Đã kiểm: nó **không** chèn vào phản hồi `application/json`, `ghi.php` trả đúng `{"ok":false,"loi":"chi nhan POST"}` nên đường ống JSON không bị bẩn. Nhưng đừng bao giờ giả định — thêm endpoint JSON mới thì kiểm lại bằng `curl` chứ đừng tin.

⚠ **Cách kiểm mà không cần chờ DNS lan:** `curl --resolve <domain>:80:103.75.186.15 http://<domain>/...` — buộc curl đi thẳng IP, bỏ qua mọi cache DNS. Và `nslookup <domain> ns1.inet.vn` hỏi thẳng nameserver gốc để biết bản ghi đã lưu đúng chưa, khỏi chờ `8.8.8.8` cập nhật.

⚠ **Hai bẫy vận hành của File Manager 1Panel:** (a) mặc định **ẩn** file bắt đầu bằng dấu chấm — phải bấm **Hidden files** mới thấy `.htaccess`, đừng kết luận là chưa tải lên; (b) ô *If file exist* để **Skip** thì gặp file đã tồn tại nó **báo đỏ và DỪNG cả lô** — lần đầu chỉ 2/5 file lên được. Chọn **Overwrite!** để tải lô.

### ⛔ Việc tiếp theo

**Đã ĐỔI đích khỏi `worldcup2026.click`.** Domain đó dùng nameserver **Cloudflare**, nên phải sửa DNS ở Cloudflare (không phải iNET) và phải tắt Proxy về xám mới cấp được SSL — hai chỗ dễ sai. `hiteckqualityconstruction.com.au` quản DNS ngay tại iNET, đơn giản hơn hẳn. Đánh đổi đã biết và đã chấp nhận: endpoint log crypto nằm chung tên miền với một công ty xây dựng đang hoạt động.

`index.html` dòng 149 đã trỏ sẵn vào `https://zewvir85.hiteckqualityconstruction.com.au/php/ghi.php`.

**Bốn bước còn lại, theo đúng thứ tự:**

1. **iNET OnePortal → domain `hiteckqualityconstruction.com.au` → Bản ghi → Thêm bản ghi:** loại `A`, tên `zewvir85`, nội dung `103.75.186.15`, TTL 5 phút. Chỉ **thêm mới**, đừng sửa 4 bản ghi sẵn có (`@ A 103.75.186.15`, `www CNAME`, 2 × `TXT` trong đó có `google-site-verification`).
2. **1Panel → Thêm tên miền** `zewvir85.hiteckqualityconstruction.com.au`, kiểu `addon`, PHP `8.1`.
3. **SSL Certificates → cấp Let's Encrypt.** Chỉ làm được sau khi bước 1 đã phân giải.
4. **Tải nguyên thư mục `php/` vào docroot**, bật hiện file ẩn để `.htaccess` cũng lên.

Kiểm từng bước, đừng làm dồn:

```bash
nslookup zewvir85.hiteckqualityconstruction.com.au    # phải ra 103.75.186.15
curl -i https://zewvir85.hiteckqualityconstruction.com.au/php/ghi.php
```

`curl` trả **401** là ĐÚNG — nghĩa là PHP chạy được, chỉ thiếu header token. Trả `Could not resolve host` là DNS chưa xong; trả 403/404 là `php/` chưa đúng chỗ.

⚠ Bản ghi gốc `@ A` của domain này **vốn đã trỏ đúng `103.75.186.15`** — cùng server với hosting, nên không phải xin IP mới.

### ⚠ Rủi ro hạ tầng còn nguyên, chưa kiểm chứng được

**cURL đi ra ngoài** — `cham.php` bắt buộc gọi được `www.okx.com`. Hosting miễn phí hay chặn. Bị chặn thì kèo kẹt `mo` rồi thành `bo_qua` hàng loạt mà trang báo cáo vẫn hiển thị bình thường. Phải chạy tay `php cham.php` một lần trước khi cài cron.

**Cron chạy được không** — nếu host chỉ cho cron gọi URL chứ không có PHP CLI thì `cham.php` nay đã hỗ trợ token qua query string: `https://<tên-miền>/php/cham.php?token=<API_TOKEN>`. Đừng chia sẻ URL đó.

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

Một file, ~1490 dòng, cộng thư mục `db/` (SQL) và `php/` (tầng log). **Điểm mấu chốt phải hiểu trước tiên: app theo dõi NHIỀU COIN SONG SONG.**

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

**1. Tầng kết nối (dòng 199–512)** — mỗi sàn một hàm `startXxx(E, g, ...)`, ghi vào `E.conns[key]` với `bids`/`asks` là `Map<priceStr, qty>` theo **đơn vị gốc của sàn**, kèm `pMul`/`qMul` để quy đổi. Cố ý KHÔNG ghép nhiều coin vào một socket dù cả 4 sàn đều hỗ trợ — làm vậy phải viết lại toàn bộ phần dò biến thể tên hợp đồng vốn rất dễ vỡ (xem Bẫy đã gặp), đổi lại chỉ tiết kiệm số socket vốn không phải nút thắt.

**2. Tầng gộp (`aggregate(E)`, dòng 975)** — trộn 4 sàn thành một sổ chung: loại sàn lệch giá >15% (sai hệ số hợp đồng), tự tính bước giá gom mốc, lọc lệnh bụi. Trả về `{bids, asks, mid, step}`, mỗi hàng có `ex` là **bitmask sàn nào đang có tiền ở mốc đó**.

**3. Tầng phân tích (dòng 513–886)** — `updateWalls(E, A)` rồi `analyze(E, A)`, chạy cho **tất cả** engine mỗi 2 giây.

**4. Tầng hiển thị (dòng 1101–1387)** — `render()` → `renderLists()` → `theCoin(E, K)` dựng một thẻ đầy đủ cho mỗi coin có tín hiệu. **Không còn khối chi tiết riêng** — mọi thứ nằm trong thẻ.

**Vòng đời engine (dòng 928–956):** `batDauEngine(sym, thuTu)` / `dungEngine(sym)`. Tham số `thuTu` chỉ để rải lệnh gọi REST 400ms một nhịp, tránh 8 engine bắn cùng lúc vào OKX.

### Chọn tập coin — `dongBoEngine()` (dòng 1429)

Mỗi 60 giây máy quét OKX xếp hạng toàn bộ ticker SWAP theo `heat`, rồi đồng bộ tập engine với top `SO_COIN`. **Có hai chốt chống nhảy loạn, đừng gỡ:**

- Coin đang theo dõi chỉ bị gỡ khi rớt khỏi **top `SO_COIN + 4`** (vùng đệm).
- Mỗi vòng quét gỡ **tối đa 2 coin**. Phần thêm vào thì không giới hạn (lúc mới mở trang phải bật đủ ngay).

Lý do: mỗi lần gỡ engine là mất sạch `wallBook` đã tích được của coin đó.

**Không còn nút TỰ ĐỘNG** — thay bằng cơ chế **GHIM** (`scan.ghim`, dòng 1395). Bấm một dòng trong QUÉT OKX là ghim / bỏ ghim coin đó; máy quét **không bao giờ gỡ coin đã ghim**, coin không ghim vẫn xoay vòng tự động như thường. Ghim một coin chưa theo dõi mà tập đã đầy thì gỡ coin **không ghim** yếu nhất để nhường chỗ; nếu cả `SO_COIN` chỗ đều đã ghim thì không nhận thêm (phải bỏ ghim bớt). Bỏ ghim KHÔNG gỡ engine ngay — nó chạy tiếp tới khi rớt khỏi vùng đệm, để khỏi phí `wallBook` đã tích. Dấu 📌 = ghim, ▶ = đang theo dõi.

Lý do bỏ nút: nút cũ là một trạng thái bật/tắt toàn cục **có thể mắc kẹt** — bấm tay một coin là tự động tắt vĩnh viễn, muốn bật lại phải có nút. Cơ chế ghim không có trạng thái toàn cục nào nên không kẹt được.

### Giao diện

Bố cục **chỉ còn đúng 2 khối**: **QUÉT OKX** → **lưới 2 cột LONG | SHORT**. Không thanh tiêu đề, không footer, không khối chi tiết. Tự xuống 1 cột dưới 900px.

⚠ App **không còn dòng miễn trừ trách nhiệm nào** ("nhận định từ dữ liệu, không phải lời khuyên") sau khi footer bị bỏ theo yêu cầu. Nếu sau này chia sẻ cho người khác dùng thì cân nhắc thêm lại.

**Mỗi coin có tín hiệu là MỘT THẺ tự chứa** (`theCoin(E, K)`, dòng 1143), gồm theo thứ tự: đầu thẻ (mã coin · giá · %24h · điểm S · thanh tin cậy) → cảnh báo funding/sổ mỏng nếu có → cảnh báo vượt mốc ⚡ nếu có → **VÀO / CHỐT** kèm mô tả tường → dòng phụ (trần · sàn · ETA · tầm xa) → dòng căn cứ (khung · dòng tiền · funding · thanh lý · biên) → **BẢN ĐỒ MỐC** 3 kháng cự + 3 đỡ. Viền trái xanh/đỏ theo hướng kèo.

**Coin KHÔNG có tín hiệu thì không hiện ở đâu cả.** Dải "đứng ngoài / đang khởi động" đã bị bỏ theo yêu cầu, nên phần chẩn đoán dồn hết vào dòng chú thích `#scanNote` ngay cạnh tiêu đề QUÉT OKX: số coin đang theo dõi, số kết nối sàn còn sống (vd `27/32 sàn live`), số coin chưa ấm máy. **Nếu thấy hai bảng trống hoàn toàn thì đọc dòng đó trước khi nghĩ là app hỏng** — 90 giây đầu sau khi mở trang chắc chắn trống.

`keoCua(E)` (dòng 1116) là chỗ **định nghĩa giá vào / giá chốt** — không phát minh logic mới, chỉ đọc lại hai tường mà `analyze()` đã chọn: kèo LONG vào tại tường **đỡ** dưới (`pred.sup`) và chốt tại tường **kháng** trên (`pred.res`); kèo SHORT ngược lại. Trả `null` nếu coin không đủ điều kiện phát tín hiệu — đây cũng chính là bộ lọc quyết định coin nào được hiện.

`pred.phat` (đặt trong `analyze`) gom toàn bộ điều kiện phát tín hiệu về **một chỗ duy nhất**: hết warmup 90s, `|S| ≥ 25`, ≥2 tín hiệu hoạt động, không `flowConflict`, không `satNoConfirm`, và có đủ cả `sup` lẫn `res`. Quan sát thực tế: điều kiện hay chặn nhất là `satNoConfirm` và **thiếu tường trong tầm `cap`** — nhiều coin có `|S|` khá cao vẫn không ra thẻ vì không tìm được tường đủ tin cậy.

### Kế hoạch giao dịch — cắt lỗ, R:R, mốc so sánh

`keoCua(E)` trả về đủ **ba** mức giá chứ không phải hai:

- `vao` = tường cùng phía (đỡ nếu long, kháng nếu short)
- `chot` = tường đối diện
- `cat` = **vượt qua tường vào** một khoảng `HE_SO_CAT × cap × mid`, mặc định `HE_SO_CAT = 0.30` (dòng 1102)

Logic của cắt lỗ: vào tại tường đỡ mà tường đó thủng nghĩa là **giả định gốc sai** — đó mới là điểm vô hiệu, không phải tường đối diện. Đặt cắt lỗ đúng tại tường đối diện là đặt stop ở chỗ chính app dự đoán giá sẽ bật.

⚠ `HE_SO_CAT = 0.30` là **tham số tự chọn, chưa có dữ liệu nào chứng minh**. Phải đo lại khi có sổ log rồi mới chỉnh.

Thẻ coin hiển thị kèm hai con số mà mọi tỷ lệ thắng phải đọc cùng:

- **R:R** = `|chot − vao| / |vao − cat|`
- **`baseline`** = `|vao − cat| / (|chot − vao| + |vao − cat|)` — với giá đi ngẫu nhiên không xu hướng, đây chính là xác suất chạm chốt trước. Hiển thị dưới dạng *"cần thắng > N% mới hơn ngẫu nhiên"*. **Đây là mốc so sánh đúng, không phải 50%.**

### Ba nhịp tim

| Nhịp | Chu kỳ | Việc |
|---|---|---|
| Phân tích | 2000ms (dòng 1071) | lặp qua **mọi** engine: `aggregate` → `updateWalls` → `analyze` |
| Vẽ | 1000ms (dòng 1387) | `render()` → `renderLists()`, dựng lại toàn bộ thẻ |
| Quét OKX | 60000ms (dòng 1489) | xếp hạng coin nóng + `dongBoEngine()` |

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

### 📊 LÔ DỮ LIỆU ĐẦU TIÊN (84 kèo, 8,5 giờ) — số đã xoá, GIỮ LẠI 4 GIẢ THUYẾT

App cũ (kiến trúc trong-trình-duyệt) chạy liên tục 2026-08-09 23:58 → 08:35 và ghi **84 kèo**, trong đó **26 kèo thật đã phân định**. Bảng `keo` đã `TRUNCATE` để bắt đầu sạch với kiến trúc bot. Ghi lại các con số ở đây vì **chúng là dữ liệu thật đầu tiên** và đáng dùng làm giả thuyết đối chiếu.

**Nhóm thật (n=26):** thắng 26,9% · baseline 38,1% · **hơn ngẫu nhiên −11,2%** · Wilson 95% **13,7–46,1%** · Tổng R sau phí **−9,95 R** (KV/kèo −0,343 R) · R:R TB 2,06 · spread 4,8 bp.

⛔ **KHÔNG kết luận được gì từ lô này, vì hai lý do độc lập:**
1. **Độ nhạy quy ước chấm THẤT BẠI:** chạm 26,9% vs đóng nến 38,5% → **chênh 11,5 điểm**, gấp đôi ngưỡng 5 điểm mà chính tài liệu này đặt ra. Nghĩa là kết luận phụ thuộc quy ước chấm chứ không phụ thuộc máy.
2. Wilson 13,7–46,1% **có chứa** baseline 38,1%.

**Bốn giả thuyết cần kiểm lại ở n ≈ 100 — đây là giá trị thật của lô này:**

| Giả thuyết | Số lô đầu | Ghi chú |
|---|---|---|
| **Máy nguội tệ hơn máy ấm** | nguội (<5') −15,5% (n=17) · ấm −3,1% (n=9) | **Khớp đúng dự đoán của tài liệu** về "kèo mù". Ứng viên số 1 để thêm chốt: không phát kèo trong 5 phút đầu mỗi coin |
| **21% lệnh chờ KHÔNG khớp** | 7/33 kèo thật | Tường vào đặt quá xa giá. Lỗi **cấu trúc**, ít phụ thuộc nhiễm bẩn nhất → đáng tin nhất trong bốn cái |
| **Nhóm BỊ CHẶN thắng hơn nhóm thật** | +4,6% (n=27) vs −11,2% (n=26) | 4 chốt chặn có thể đang **cắt mất kèo thắng** — đúng câu hỏi mà cột `chan` sinh ra để trả lời |
| **Tường tin cao KHÔNG tốt hơn** | tin ≥55 −12,9% (n=10) · tin 35–54 −10,2% (n=16) | Công thức `+15/+8/−30` có thể chỉ là trang trí |

Hai chỗ **ngược trực giác**, chưa đủ mẫu nhưng phải theo dõi: `|S|` 40–59 (−26,8%, n=9) **tệ hơn** `|S|` 25–39 (−3,0%, n=17); và tường có **nhiều** tiền OKX hơn lại thua nhiều hơn (OKX ≥70%: −29,7%, n=8) — cái sau thách thức chính giả định nền của lần đổi sang sổ riêng OKX.

⚠ **Đừng chỉnh trọng số vì bảng này.** Mọi ô đều n < 20. Đúng vòng lặp "chỉnh sớm sau vài chục kèo" đã đẻ ra 4 chốt chặn hiện tại — mà chính chúng giờ đang bị nghi là cắt mất kèo thắng.

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

### Luật sinh kèo (`sinhKeo`, dòng 1316)

Mỗi nhịp 2 giây, mỗi coin: nếu **chưa có kèo đang mở**, đã hết warmup, có đủ `sup` lẫn `res`, và `|S| ≥ NGUONG_GHI` thì phát một kèo. Hạn = `frameH` giờ. Ra khoảng 20–40 kèo/ngày trên 8 coin và chúng **độc lập** với nhau.

⚠ **`NGUONG_GHI = 15` thấp hơn ngưỡng hiển thị 25 — có chủ đích.** Kèo bị 4 chốt chặn loại bỏ **vẫn được ghi và vẫn được chấm**, đánh dấu bằng bitmask `chan` (1 = |S|<25, 2 = dưới 2 tín hiệu, 4 = flow ngược, 8 = bão hoà không xác nhận). `chan = 0` là kèo thật. Đây là nhóm đối chứng duy nhất để biết chốt chặn đang cứu hay đang cắt mất kèo thắng — lọc lại bằng SQL lúc phân tích. Cách này thay cho ý tưởng "kèo bóng" ban đầu, vốn phải BỊA hướng khi `|S|` gần 0.

`VER_TRONG_SO` (dòng 1101) là hash **tự sinh** từ chính mảng tham số quyết định, nên không bao giờ quên bump khi chỉnh trọng số.

Giá gửi lên dưới dạng **chuỗi `toFixed(15)`** — khớp `DECIMAL(30,15)` và tránh ký hiệu mũ (`1.23e-8`) mà MySQL không nhận. Coin có giá từ 0,0000000123 tới 90000.

### Hai quy ước chấm

`cham.php` chấm mỗi kèo **hai lần**: `kq` theo quy ước **chạm** (bóng nến tới giá) và `kq_dong` theo quy ước **đóng nến vượt hẳn**. Cả hai áp cùng cách cho chốt lời lẫn cắt lỗ — siết một đầu là tự làm đẹp số. Chênh lệch giữa hai con số là **thước đo độ nhạy**: quá 5 điểm phần trăm nghĩa là kết luận phụ thuộc vào quy ước chứ không phụ thuộc vào máy.

⚠ **Bẫy phân trang nến đã dính một lần khi viết:** với tham số `before` và `limit=300`, nếu khoảng cần lấy dài hơn 300 nến thì OKX trả 300 cây **mới nhất** chứ không phải 300 cây ngay sau mốc — thủng đoạn giữa mà không báo lỗi. Phải phân trang **lùi** bằng `after`. Cũng dùng `/market/candles` (giữ 1.440 nến 1m = 24h) chứ không phải `/market/history-candles`, và bỏ qua nến chưa đóng (`confirm = '0'`).

## Đợt rà soát trước deploy (2026-08-09) — 12 lỗi đã sửa

25 agent, 5 chiều rà soát + vòng phản biện đối kháng. 16 phát hiện sống sót, 4 bị bác bỏ.
**Đã sửa 12, còn 4 là rủi ro hạ tầng không sửa được bằng code.**

### Lỗi nặng nhất — chấm điểm cho lệnh CHƯA TỪNG KHỚP

`vao` là **tường ở phía bên kia giá**: kèo LONG vào tại tường đỡ *nằm dưới* giá lúc phát, kèo SHORT vào tại tường kháng *nằm trên* (vì `bestWall` lọc `d > 0`). Đó là **lệnh chờ**, chưa khớp. `cham.php` cũ duyệt nến từ `ts` rồi kiểm chốt lời ngay mà không hề hỏi giá đã quay lại chạm `vao` chưa.

Kịch bản: phát kèo LONG lúc giá 100, vào 98, chốt 104. Giá chạy thẳng lên 105 không quay lại 98 → ghi `thang`, R = 10. Thực tế lệnh chờ ở 98 **không bao giờ khớp**, lãi thật = 0. Vì sai lệch này luôn thuận chiều thị trường, nó thổi phồng kết quả **có hệ thống**.

Đã sửa: thêm cờ `$daKhop`, chỉ bật kiểm chốt lời/cắt lỗ và tích MFE/MAE **sau khi** giá chạm `vao`. Thêm trạng thái `khong_khop` vào ENUM (bị loại khỏi mọi phép tính tỷ lệ thắng, hiện thành cột riêng trên báo cáo — con số này cao nghĩa là tường vào đang đặt quá xa giá).

### Mười một lỗi còn lại đã sửa

| Tệp | Lỗi | Hậu quả nếu để nguyên |
|---|---|---|
| `cham.php` | `layNen()` trả mảng rỗng không có lối thoát `bo_qua` | Cron chết > 24h là kèo kẹt `mo` vĩnh viễn; `ORDER BY han_ms ASC LIMIT 60` khiến đám xác chết chiếm hết chỗ → **bộ chấm sổ chết im lặng** |
| `cham.php` | Đóng sổ ngay khi quy ước CHẠM xong | `kq_dong` kẹt `'mo'` vĩnh viễn (vì `cl <= h` nên quy ước ĐÓNG không bao giờ xong trước) → vô hiệu hoá bảng đo độ nhạy |
| `cham.php` | MFE/MAE tính cả đoạn sau khi đã thoát lệnh | "Giá tốt nhất từng chạm" thành số vô nghĩa |
| `cham.php` | Phân trang hỏng giữa chừng vẫn chấm trên tập nến thiếu | Kết quả sai và **đóng sổ vĩnh viễn**. Nay `$loi` là trả `null` để lượt sau thử lại |
| `cham.php` | Cron chỉ chạy được bằng PHP CLI | Host không có CLI là bó tay. Nay nhận token qua query string |
| `ghi.php` | Không chặn kèo chồng lấn | `E.keoMo` chỉ ở RAM, F5 là mất → nhiều kèo cùng coin cùng lúc, **không độc lập**, phồng cỡ mẫu giả. Nay chặn ở server |
| `index.html` | 6 cột tín hiệu không bao giờ `NULL` | "Thiếu dữ liệu" bị ghi thành `0` = "trung tính" — dạy mô hình sau này một điều sai |
| `index.html` | `cap`/`frame_h` rơi về hằng số 0.03 / 4h khi chưa có nến | Số **bịa** được ghi vào DB như số đo. Nay không phát kèo khi thiếu `market.ranges` |
| `index.html` | Đường ghi log không có tín hiệu sống nào | Sai token hay sai URL là mất 100% dữ liệu mà không biết. Nay hiện `log: N gửi · N LỖI` trên giao diện |
| `index.html` | `splice(0, n)` xoá hàng đợi theo vị trí | Trần 40 cắt đầu hàng đợi trong lúc chờ mạng → xoá nhầm kèo chưa gửi. Nay xoá theo `uid` |
| `bao-cao.php` | `Tổng R` lọc `thang01 IS NOT NULL` | Vứt hết kèo `het_han` (R gần 0) khỏi tổng → **thổi phồng lãi lỗ**. Bảng độ nhạy cũng trừ hai trung bình trên hai mẫu số khác nhau |

### Bốn thứ bị bác bỏ khi kiểm chứng

Kèo bị `ghi.php` từ chối vẫn trả HTTP 200 · `layNen` trả tập thiếu như thể đầy đủ · token nằm trong JS công khai nên vô nghĩa · một dòng lỗi làm hỏng cả lô 50 kèo. Đọc lại code thì cả bốn đều không dựng lại được kịch bản hỏng.

### Chưa sửa được bằng code

Phụ thuộc tuyệt đối vào cURL đi ra ngoài · tần suất cron của gói hosting · `ts`/`han_ms` lấy từ đồng hồ máy client (có `ts_srv` để đối chiếu nhưng chưa có cảnh báo tự động khi lệch).

## Nhật ký thay đổi

Commit gần nhất trước khi có file này: `5d4e6f7` (máy quét OKX + lãi tự động).

⚠ **Đánh số mục phải liên tục và mới nhất ở TRÊN.** Đã có một lần hai phiên làm việc song song
cùng đánh số `(13)` và mục mới bị chèn xuống giữa file — đọc từ trên xuống thành ra sai thứ tự
thời gian. Trước khi thêm mục, kiểm số lớn nhất bằng `grep -n "^- \*\*2026-" CLAUDE.md | head -3`.

- **2026-08-10 (25)** — **🚀 TRIỂN KHAI XONG. Bot chạy 24/24 trên server, máy tính tắt được.**
  Không đụng code — toàn bộ là việc triển khai và kiểm chứng. Chi tiết trạng thái ở mục
  "✅ ĐANG CHẠY THẬT 24/24".
  Thứ tự đã làm: đổi token trên server → tải `index.html` mới (bản không còn token) → tạo
  `~/bot/` **ngoài** thư mục web → `token.txt` (tạo qua File Manager chứ không Terminal, để token
  không vào `.bash_history`) → `chmod +x` → chạy thử `KHONG_GHI=1` → chạy thật → 2 cron.
  **Xác nhận token cũ đã chết:** POST bằng token cũ trả **401 "token sai"**, token mới trả **200**.
  **Xác nhận trang công khai không còn token:** đã grep bản trên server, `API_TOKEN` giờ là
  `(typeof __API_TOKEN__ ...) : ''`.
  **Đã `TRUNCATE keo`** theo yêu cầu chủ dự án, bỏ 84 kèo của kiến trúc cũ. Bốn giả thuyết rút ra
  từ lô đó đã ghi vào mục "📊 LÔ DỮ LIỆU ĐẦU TIÊN" — xoá dòng chứ không xoá kiến thức. Giữ `phi`.
  **Bắt được một lỗi âm thầm khi soi trang Cron:** cron `cham.php` cũ vẫn dùng **token đã lộ**,
  tức bộ chấm sổ đã chết từ lúc đổi token mà không dấu hiệu gì — kèo sẽ kẹt `mo` vĩnh viễn. Đã
  thay bằng PHP CLI. Đây đúng loại hỏng mà cột `cham_luc` tồn tại để phát hiện.
  **Đã dọn:** `test-song.log` (tiến trình nhân chứng sống **gần 8 tiếng** — bằng chứng mạnh nhất
  về việc hosting giữ được tiến trình chạy dài) và file nén Node 44,9 MB.

- **2026-08-10 (24)** — **Vá 2 phát hiện còn sót của đợt rà soát — nay đã đóng đủ 24/24.**
  1832 → 1903 dòng index.html, bot.js 258 → 270. Cả hai đều làm **bẩn tín hiệu** chứ không làm
  sập gì, nên chúng thuộc loại tệ nhất: chạy tiếp mà số sai.
  (a) **Socket thanh lý** không có canh chừng và **nuốt luôn phản hồi lỗi của sàn**. Đây là nguồn
  duy nhất của `sLiq` (trọng số 0.20) và nằm ngoài mọi engine nên không bộ canh chừng theo-sàn
  nào che. Đăng ký bị từ chối thì socket vẫn mở, ping vẫn chạy, mà không bao giờ có dữ liệu →
  `sig_lq` toàn NULL. Thêm `lqLastMsg`/`lqLastData`/`lqLoiCuoi`, bắt `d.event === 'error'`, đóng
  socket khi im quá 60 giây để `onclose` tự nối lại, và đưa cả ba vào ảnh trạng thái + dòng sức
  khoẻ. Ở socket này dùng `pong` làm dấu hiệu sống là **đúng** (khác hẳn 4 sàn kia) vì thanh lý là
  sự kiện thưa — im hàng giờ là bình thường.
  (b) **ws2 của Binance** (aggTrade/forceOrder) sống riêng với socket sổ lệnh: chết nửa-mở thì
  depth vẫn `live` trong khi mất nguồn Binance cho `sFlow30` + `sFlow5` = **45% tổng trọng số**.
  Thêm `c.lastData2` + canh chừng 180 giây (coin nào cũng phải có khớp lệnh trong 3 phút ở mức
  thanh khoản >200k USD/24h) và `c.moLaiWs2` để mở lại. Đổi timer nối lại từ `c.timers.push(...)`
  sang **một biến `c.tWs2`** có `clearTimeout` — mảng `c.timers` chỉ được dọn bởi `clearConn` mà
  đường nối lại của ws2 không gọi `clearConn`, nên mảng lớn dần theo tuần. `clearConn` nay dọn
  `tWs2` bằng tay, vì nó nằm ngoài mảng.
  **Kiểm lại toàn bộ:** thẻ coin 14/14, lưới an toàn 12/12, bot chạy 75 giây với **32/32 sàn
  live**, không dòng `!! CANH BAO` nào, ảnh 19,2 KB. Xác nhận khối `lq` trong ảnh có số thật
  (`lastData` 11 giây trước) — tức thanh lý đang chảy và giám sát được.

- **2026-08-10 (23)** — **Rà soát bảo mật lịch sử git → ĐỔI TOKEN.** Chi tiết ở mục
  "🔐 SỰ CỐ TOKEN BỊ LỘ". Quét từng commit: mật khẩu DB **không** lộ; nhưng `API_TOKEN` cũ và URL
  `ghi.php` nằm trong `index.html` ở 3 commit **đã push** lên GitHub. Đã đổi token mới (chỉ nằm
  trong `php/config.php`, đã gitignore), và bỏ token thật khỏi `bot/HUONG-DAN.md`.
  Cũng rải phần mở socket theo nhịp 400ms trong `batDauEngine`: trước đây 32 cú bắt tay WebSocket
  nổ cùng lúc lúc khởi động nguội, đủ để OKX chặn nhịp — mà một lần bị chặn là sàn đó rơi 'off'
  và coin đó không sinh được kèo nào. Và bỏ một lệnh gọi `anhTrangThai()` thừa trong `inSucKhoe`.

- **2026-08-10 (22)** — **Rà soát đối kháng 70 agent rồi vá 17 lỗi.** 1630 → 1832 dòng index.html,
  bot.js 190 → 258. Bảng đầy đủ ở mục "Bộ lưới an toàn chống hỏng IM LẶNG".
  Rà 4 chiều (chạy dài hạn · JSON méo · độ khớp ảnh trạng thái · gating chế độ), mỗi phát hiện
  bị **2 agent độc lập cố bác bỏ**: 33 phát hiện → **24 sống sót**, 9 bị bác bỏ.
  **Lỗi tôi vừa tạo ra, tệ nhất:** `moSocketThanhLy()` gọi ở cấp cao nhất mà **quên bọc `LA_BOT`**
  → trang xem cũng mở WebSocket OKX rồi ném TypeError mỗi gói tin, vì engine do `napTrangThai()`
  dựng không có `liqs`. Phá đúng bảo đảm vừa viết vào tài liệu. Hai chiều rà soát độc lập cùng
  báo, 2/2 cả hai. Nguyên nhân gốc: socket đó **dùng chung toàn sàn** nên nằm ngoài
  `batDauEngine` — đã ghi thành nguyên tắc.
  **Ba lỗi làm BẨN DỮ LIỆU (có sẵn từ trước, nhưng bot chạy nhiều ngày mới thành nghiêm trọng):**
  (a) `pong` cũng làm mới `lastMsg` → socket nửa-mở giữ nhãn `live` với sổ ĐÓNG BĂNG, rồi
  `wallTrust` càng cộng điểm cho tường chết vì nó "sống lâu" → sinh kèo từ sổ hàng giờ trước;
  (b) state `off` là ngõ cụt vĩnh viễn → mất OKX là coin đó không bao giờ sinh kèo nữa mà mọi
  chỉ báo vẫn xanh; (c) `flowSec`/`liqs` chỉ được cắt trong hàm không chạy khi mất sàn → rò rỉ
  bộ nhớ thật.
  **Hai lỗi làm chính lưới an toàn vô hiệu:** đo độ trễ bằng hai đồng hồ khác nhau (lệch NTP là
  cảnh báo im hoặc kêu oan vĩnh viễn), và `render()` nằm ngoài `try` nên trang đóng băng im lặng.
  **Đã test cho các lưới KÊU THẬT, 11/11 mục:** ảnh sai cấu trúc / rỗng / null / `coins` không
  phải mảng đều ra cảnh báo mà không throw; `ver` lệch ra "BOT ĐANG CHẠY BẢN CŨ"; ảnh cũ bị bỏ
  qua; quá 30 giây ra "DỮ LIỆU CŨ". Cộng test thẻ coin **14/14** phần vẫn nguyên sau khi vá.
  **9 phát hiện bị bác bỏ** — ghi lại để không ai vá thứ không hỏng: vòng phân tích không cần
  try/catch từng coin, `wallBook` đã có dọn 24h, `napGhim` không thực sự kẹt, `keoCua`/`theCoin`
  không throw với ảnh hợp lệ.
  Bắt được một lỗi cú pháp nhờ chính `node --check`: viết `*` `/` `5` trong comment khối làm
  comment đóng sớm.

- **2026-08-10 (21)** — **🏗 TÁCH GHI KHỎI XEM — bot Node 24/24 + trang xem chỉ-đọc.**
  1493 → 1630 dòng index.html, thêm thư mục `bot/` (`bot.js` 190 dòng, `chay.sh`, `dung.sh`,
  `ghim.txt`, `HUONG-DAN.md`). Chi tiết kiến trúc ở mục "🏗 KIẾN TRÚC HIỆN TẠI" đầu file.
  **Yêu cầu gốc:** ghi 24/24 không cần treo máy; mở link để xem thì CHỈ xem, không ghi đè DB;
  và mở ra là thấy lệnh ngay chứ không phải chờ ấm máy. Cả ba đã đạt.
  **Thuật toán KHÔNG đổi một dòng** — `taoEngine`/`aggregate`/`updateWalls`/`wallTrust`/`analyze`/
  `bestWall`/`pickLevels`/`keoCua`/`sinhKeo` nguyên vẹn, nên dữ liệu bot sinh ra so sánh được với
  21 kèo đã có, không tạo đứt đoạn trong bảng.
  **Quyết định kiến trúc đáng ghi:** `bot.js` **NẠP** khối `<script>` của `index.html` rồi chạy
  bằng indirect eval, chứ KHÔNG chép code sang — chỉ tồn tại một bản thuật toán. Rẻ vì cả file
  chỉ chạm DOM 10 chỗ. Dùng eval chứ không `vm` vì `vm` tạo realm khác làm `instanceof` xuyên
  realm sai **âm thầm**.
  **Bảo đảm ở tầng thiết kế:** trình duyệt không bao giờ đặt được cờ `__BOT__`, nên trang công
  khai **không thể** ghi DB. Kèm theo: `API_TOKEN` đã **ra khỏi** `index.html` (bot đọc
  `~/bot/token.txt` ngoài thư mục web) — trước đây để token trong file mà file đó lại đặt công
  khai thì ai xem mã nguồn cũng đọc được.
  **Xoá ba hạn chế cũ:** phải treo máy · kèo chồng nhau khi khởi động lại · `wallBook` mất mỗi F5
  (cái thứ ba là lợi ích lớn nhất về chất lượng dữ liệu — tường tích luỹ nhiều ngày).
  **Đã xoá code thành thừa:** khối `onclick` ghim trong `renderScan` (26 dòng) cùng `data-s`,
  `cursor:pointer` và `:hover` của `.scanRow`. `renderScan` nay chỉ chạy ở chế độ xem, mà chế độ
  xem không sở hữu tập engine nên bấm vào chỉ sửa bản sao trong RAM rồi bị ghi đè sau 2 giây —
  một nút nhìn như bấm được mà không ăn còn tệ hơn không có nút. Ghim chuyển sang `~/bot/ghim.txt`.
  Rà cả file: **không có hàm nào định nghĩa rồi bỏ đó**, `#scanBody` thì CSS dùng làm lưới.
  **Tối ưu:** bot bỏ hẳn nhịp vẽ; trang xem vẽ 2 giây/lần thay vì 1 giây (dữ liệu chỉ mới mỗi
  2 giây nên vẽ dày hơn là vẽ lại đúng thứ cũ); ảnh trạng thái chỉ ~**16 KB** vì chỉ gửi 2 bản
  ghi tường mà `wallInfo()` cần thay vì cả `wallBook` vài trăm bản ghi.
  **Thêm hai lưới an toàn** cho kiểu hỏng im lặng: trang xem **luôn hiện tuổi của ảnh** và quá
  30 giây thì thay dòng chú thích bằng cảnh báo đỏ; ghi ảnh qua file tạm rồi `rename` (nguyên tử)
  để trang xem không đọc phải JSON viết dở. Cộng cờ `KHONG_GHI=1` để chạy thử mà chưa ghi DB.
  **Đã test đầu-cuối trên máy, không phải suy đoán:** bot nạp 67 KB script, 8 coin, 32/32 sàn
  live, ảnh 16,5 KB; chạy `renderLists()` thật trong Node và trong Chrome thật qua HTTP — thẻ coin
  dựng đủ **14/14** phần (đầu thẻ, S, thanh tin cậy, VÀO/CHỐT/CẮT, R:R, baseline, trần/sàn, dòng
  căn cứ, bản đồ mốc, độ tin tường, `wallInfo`, nhãn xác nhận sàn) với dữ liệu thật của BEAT và
  PEOPLE. Xác nhận `LA_BOT=false` thì `API_LOG` và `API_TOKEN` đều là chuỗi rỗng.
  **Chưa làm:** mật khẩu HTTP cho trang xem (chủ dự án nói chưa cần).

- **2026-08-10 (20)** — **🎉 GIAI ĐOẠN 2 HOÀN TẤT.** Không đụng code app — chỉ cài cron và kiểm chứng.
  Cron trong 1Panel → **Tính năng nâng cao → Công việc Cron** (không nằm ngoài sidebar, mất thời
  gian mới tìm ra). Panel này là **crontab chạy lệnh shell**, KHÔNG có kiểu "truy cập URL" —
  dùng `curl -s -o /dev/null "<url>?token=..."` với lịch `* * * * *`. Chọn `curl` chứ không PHP CLI
  vì đường HTTP đã kiểm chứng chạy thật, còn PHP CLI trong cron thì chưa biết có sẵn không.
  **Xác nhận cron chạy thật:** `MAX(cham_luc)` = 01:12:05 so với `NOW()` = 01:12:58 → lệch **53
  giây**. Đây đúng là công dụng của cột `cham_luc`.
  **Rủi ro hạ tầng lớn nhất đã loại:** hosting **CHO** PHP gọi ra `www.okx.com` — `cham.php` chạy
  tay in ra 7 kèo với `gia_ra`, `mfe`, `mae` đầy đủ. Trước đó đây là ẩn số có thể phá cả giai đoạn 2.
  **Số liệu đầu tiên, và cách đọc đúng:** nhóm thật n=4, 0 thắng, baseline 45%, **Wilson 95% =
  0%–49%**. Baseline nằm TRONG khoảng tin cậy → **chưa phân biệt được với ngẫu nhiên, không kết
  luận gì**. Cả 7 kèo đều sinh trong ~30 phút đầu khi máy còn nguội (giao diện ghi "8 chưa ấm
  máy", dải độ tin tường chỉ 35–54) → đây là nhóm kèo **kém thông tin nhất** hệ thống sẽ sinh ra.
  Một điểm đáng ghi để theo dõi, KHÔNG phải kết luận: cả LONG lẫn SHORT đều thua, tất cả ăn trọn
  cắt lỗ (`KV/kèo ≈ −1.11R`), và `mfe` chỉ đạt ~30% đường tới chốt. Nếu mẫu lớn còn giữ hình này
  thì nghi vấn đầu tiên là `HE_SO_CAT = 0.30` quá chặt so với biên nhiễu — **đo, đừng chỉnh**.
  **Phát hiện chưa khai thác:** sidebar 1Panel có **NodeJS** (Cài đặt ứng dụng) và **Terminal** +
  **Quản Lý SSH** (Tính năng nâng cao). Nếu hosting cho chạy tiến trình Node liên tục thì xoá được
  hẳn hạn chế "phải mở tab" mà không cần VPS. Chưa kiểm.

- **2026-08-10 (19)** — **Đường ống chạy thật, và chốt cách chạy là MỞ TAY.** Không đụng code app.
  Xác nhận dữ liệu chảy: **10 → 11 kèo**, trang `bao-cao.php` đọc được và tự cảnh báo đúng mực
  (*"cần khoảng 800 kèo độc lập"*).
  **Đã thử rồi bỏ cách chạy ẩn.** Chrome `--headless=new` + profile riêng `coin-bot` chạy được
  thật (7–8 tiến trình, ~486 MB, không cửa sổ), nhưng chủ dự án chọn mở tay để dễ kiểm soát —
  tiến trình ẩn đã tắt sạch. Giữ lại kiến thức ở mục "Chrome headless — đã thử, đã bỏ", quan
  trọng nhất là **4 cờ chống hạ nhịp**: Chrome hạ `setInterval` xuống 1 lần/phút cho tab nền,
  mà vòng phân tích là 2 giây → app vẫn chạy nhưng sinh kèo từ dữ liệu cũ cả phút, **hỏng im
  lặng, không phát hiện được từ dữ liệu**.
  **Ghi thêm một rủi ro dữ liệu chưa sửa:** danh sách "kèo đang mở" chỉ nằm trong RAM client,
  không đọc từ server, nên tắt/bật lại app có thể sinh kèo **chồng thời gian** trên cùng một
  coin → phá giả định độc lập. Kèm câu SQL kiểm và ghi rõ **đừng xoá, lọc bằng SQL lúc phân
  tích**. Cách sửa gốc là `sinhKeo` hỏi server lúc khởi động — chưa làm.
  Cũng ghi lại: máy có **Node v20.20.0**, `fetch` sẵn, `WebSocket` chỉ cần cờ
  `--experimental-websocket` → bản Node chạy nền **không cần cài npm gì**, để dành cho sau.

- **2026-08-10 (18)** — **Deploy xong hạ tầng, kiểm chứng đầu-cuối bằng `curl` thật.** Không đụng
  code — toàn bộ là việc hạ tầng.
  Bản ghi A `zewvir85 → 103.75.186.15` ở iNET, site addon PHP 8.1 trong 1Panel, 4 file PHP +
  `.htaccess` vào `/domains/zewvir85.hiteckqualityconstruction.com.au/php/`, SSL ZeroSSL kiểu
  "SSL Tự động". Xác nhận qua HTTPS: `ghi.php` 405 JSON sạch, `cham.php` 401, `bao-cao.php` 200,
  `config.php` 403, và preflight OPTIONS trả **204** với `access-control-allow-origin: null` —
  tức app mở bằng `file://` gọi được.
  **Ba bẫy vận hành đã ghi vào mục trạng thái** (đừng dẫm lại): File Manager 1Panel ẩn file
  `.ht*` nên tưởng chưa tải lên; chế độ **Skip** làm cả lô upload **dừng giữa đường** chỉ vì một
  file đã tồn tại (lần đầu chỉ 2/5 file lên); và sửa `db/tao-database.sql` sau khi đã import thì
  DB tụt lại — `bao-cao.php` trả **500 với thân trang RỖNG**, không có gì để lần vì hosting tắt
  `display_errors`. Import lại là hết.
  **Ghi lại phát hiện về hosting:** nó **chèn `<script src="/_osh/collect.js">` vào mọi phản hồi
  HTML**. Đã kiểm là KHÔNG chèn vào `application/json`, nhưng thêm endpoint JSON mới thì phải
  kiểm lại bằng `curl` chứ đừng tin.
  Cũng ghi hai kỹ thuật kiểm không cần chờ DNS lan: `curl --resolve` và hỏi thẳng `ns1.inet.vn`.

- **2026-08-09 (17)** — **Đổi đích deploy sang `zewvir85.hiteckqualityconstruction.com.au`.**
  Chỉ sửa 1 dòng `API_LOG` ở index.html, không đụng logic.
  **Lý do đổi:** `worldcup2026.click` và `filexo.online` đều dùng nameserver **Cloudflare**, còn
  `hiteckqualityconstruction.com.au` dùng `ns*.inet.vn`. Đo bằng `nslookup -type=ns`. Chọn domain
  quản DNS tại iNET để bớt hai chỗ dễ sai (sửa sai panel, và quên tắt Proxy về xám). Bản ghi gốc
  `@ A` của nó **vốn đã trỏ đúng `103.75.186.15`**, cùng server hosting.
  Đánh đổi chấp nhận: endpoint log crypto nằm chung tên miền công ty xây dựng đang hoạt động.
  **Dọn hai chỗ hỏng của tài liệu:** (a) mục nhật ký của đợt rà soát 25 agent bị đánh số trùng
  `(13)` và nằm sai vị trí → chuyển thành `(16)` đặt đúng thứ tự; (b) mục trạng thái đang chỉ
  vào tên miền cũ → viết lại thành 4 bước còn lại kèm cách kiểm từng bước.

- **2026-08-09 (16)** — **Rà soát trước deploy: sửa 12 lỗi.** 1470 → 1493 dòng.
  Chi tiết ở mục "Đợt rà soát trước deploy" phía trên. Nặng nhất là `cham.php` chấm điểm cho
  lệnh chờ chưa từng khớp — sai lệch thuận chiều thị trường nên thổi phồng kết quả có hệ thống;
  đã thêm cờ `$daKhop` và trạng thái `khong_khop`. Kèm hai lỗi làm bộ chấm sổ **chết im lặng**
  (mảng nến rỗng không có lối thoát, và `kq_dong` kẹt `'mo'` vĩnh viễn).
  `tao-database.sql` thêm `khong_khop` vào hai ENUM kèm `ALTER ... MODIFY COLUMN` idempotent
  cho database đã tạo trước đó, và cột dẫn xuất `khong_khop` trong VIEW.
  **Phát hiện khi kiểm chứng đường ống:** tên miền `zewvir85.worldcup2026.click` **không phân
  giải được DNS** — đó là lý do app báo lỗi mạng, không phải lỗi CORS hay token. Mục (17) xử lý
  bằng cách đổi hẳn sang domain khác.

- **2026-08-09 (15)** — Chốt đích deploy và điền `API_LOG`. Không đụng logic, sửa đúng 1 dòng
  nên index.html vẫn **1470 dòng**.
  **Hạ tầng thật (ghi lại để phiên sau không phải hỏi):** 1Panel 3.8.81, IP `103.75.186.15`,
  PHP **8.1** (config.php + 3 file kia chạy tốt, không phải hạ cấp cú pháp), domain chính
  `hiteckqualityconstruction.com.au` trỏ `/public_html`, domain phụ nằm trong `/domains/<tên>`.
  **Chọn subdomain riêng thay vì domain chính**, tên `zewvir85` do CSPRNG sinh chứ không phải
  `coin`: `bao-cao.php` không có mật khẩu nên ai biết URL là đọc được cả sổ kèo — tên khó đoán
  là lớp bảo vệ yếu nhưng gần như miễn phí.
  **Chốt lại lần cuối trong phiên: dùng `zewvir85.worldcup2026.click`**, không dùng
  `hiteckqualityconstruction.com.au`. Chủ dự án có 3 domain ở iNET OnePortal (`portal.inet.vn`):
  `worldcup2026.click`, `filexo.online`, và domain công ty. Hai cái đầu là domain rời không chạy
  gì → dùng chúng thì không trộn endpoint log crypto vào tên miền của một công ty xây dựng đang
  hoạt động.
  **Phát hiện tránh được một buổi gỡ lỗi vô ích:** DNS ba domain chia **hai chỗ** khác nhau —
  hai domain rời dùng nameserver **Cloudflare**, chỉ domain công ty dùng `ns*.inet.vn`. Trang
  *Bản ghi DNS* của `worldcup2026.click` trong iNET hiện **0 bản ghi** không phải vì chưa cấu
  hình mà vì đó là zone chết. Bảng đối chiếu + lưu ý Proxy status phải để xám đã ghi ở mục
  "Việc tiếp theo phải làm".
  **Ghi lại thứ tự bắt buộc dễ làm sai:** 1Panel **không** tạo DNS — phải thêm bản ghi A ở nhà
  cung cấp domain TRƯỚC, xác nhận `nslookup` ra đúng IP, rồi mới cấp Let's Encrypt được. Hai
  domain addon sẵn có đang hiện ⚠ vàng, nghi đúng vì bỏ bước này.
  Không sửa `ORIGIN_CHO_PHEP` vì app chạy `file://` → Origin là `'null'`, vốn đã có trong mảng.

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
