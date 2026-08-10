# Bot chạy 24/24 trên server — triển khai

Sau khi làm xong 8 bước dưới, anh **tắt máy tính thoải mái**, dữ liệu vẫn chảy vào MySQL.
Muốn xem lệnh thì mở link, và mở link **không ghi gì** vào database.

```
Bot (Node, server, 24/24)  ──ghi──>  MySQL          (tiến trình DUY NHẤT được ghi)
        │
        └──ghi đè mỗi 2 giây──>  trangthai.json
                                       │
                       Trang xem  <──đọc──┘         (chỉ đọc, không ghi gì)
```

---

## 1. Node trên server — đã xong

Đã tải và kiểm chứng ngày 2026-08-10:

```bash
~/node-v20.20.0-linux-x64/bin/node -v      # phải ra v20.20.0
```

Không ra thì tải lại:

```bash
cd ~ && curl -sLO https://nodejs.org/dist/v20.20.0/node-v20.20.0-linux-x64.tar.gz && tar -xzf node-v20.20.0-linux-x64.tar.gz
```

## 2. Tải `index.html` MỚI lên thư mục web

Đè lên bản cũ tại:

```
/home/buwsofujhosting/domains/zewvir85.hiteckqualityconstruction.com.au/index.html
```

⚠ Bản mới **không còn chứa `API_TOKEN`** — nên đặt công khai trên web là an toàn. Bản cũ có token, đừng để sót.

## 3. Tải thư mục `bot/` lên **`/home/buwsofujhosting/bot/`**

**NGOÀI thư mục web**, ngang cấp với `domains/` và `public_html/`. Đặt trong thư mục web là phơi `token.txt` ra internet.

Gồm: `bot.js`, `chay.sh`, `dung.sh`, `ghim.txt`, `HUONG-DAN.md`.

## 4. Tạo `~/bot/token.txt`

Một dòng duy nhất, đúng chuỗi `API_TOKEN` trong `php/config.php`, không dấu ngoặc:

```bash
echo 'DAN_TOKEN_VAO_DAY' > ~/bot/token.txt
chmod 600 ~/bot/token.txt
```

⚠ **Đừng dán token thật vào file hướng dẫn này** — nó được commit lên GitHub. Đã dính một lần: token cũ nằm trong `index.html` khi một phiên làm việc song song commit, và commit đó đã push lên repo công khai. Lấy token từ `php/config.php` (file đó bị `.gitignore` cố ý).

## 5. Cho phép chạy 2 script

```bash
chmod +x ~/bot/chay.sh ~/bot/dung.sh
```

## 6. CHẠY THỬ trước — chưa ghi vào DB

Bắt buộc làm bước này. Bật thẳng bản thật rồi mới phát hiện sai là dữ liệu đã vào bảng, mà kèo đã ghi thì không xoá sạch được nếu không mất cả mẫu tốt.

```bash
cd ~/bot && KHONG_GHI=1 ./chay.sh
```

Đợi 2 phút rồi kiểm 3 thứ:

```bash
tail -20 ~/bot/bot.log
ls -l ~/domains/zewvir85.hiteckqualityconstruction.com.au/trangthai.json
curl -s -o /dev/null -w "%{http_code}\n" https://zewvir85.hiteckqualityconstruction.com.au/trangthai.json
```

| Cần thấy | Nghĩa |
|---|---|
| `bot da chay. Ghi anh trang thai -> ...` | nạp được `index.html` |
| dòng `N coin · 32/32 san live · ... RAM xxMB` | các sàn đã kết nối |
| `trangthai.json` có và **thời gian sửa đổi mới liên tục** | vòng ghi ảnh chạy |
| `curl` trả **200** | trang xem đọc được |

Rồi mở `https://zewvir85.hiteckqualityconstruction.com.au/` — phải thấy bảng QUÉT OKX và dòng `XEM · dữ liệu Ns trước`.

## 7. Chạy thật

```bash
cd ~/bot && ./dung.sh && ./chay.sh
```

Kiểm sau 3 phút: dòng log phải có `log N gui/0 loi`. Nếu `loi` tăng thì sai token — so lại `~/bot/token.txt` với `php/config.php`.

## 8. Tự bật lại khi bot chết — BẮT BUỘC

Không có bước này thì bot chết lúc 2 giờ sáng là mất cả đêm dữ liệu mà không ai biết.

1Panel → Tính năng nâng cao → Công việc Cron → thêm, lịch `*/5 * * * *`:

```
cd /home/buwsofujhosting/bot && ./chay.sh > /dev/null 2>&1
```

`chay.sh` tự từ chối nếu bot đang chạy, nên cron này chỉ có tác dụng khi bot đã chết. Nó cũng lo luôn việc bật lại sau khi server khởi động lại.

---

## Vận hành hằng ngày

| Việc | Lệnh |
|---|---|
| Xem log trực tiếp | `tail -f ~/bot/bot.log` |
| Kiểm còn sống | `pgrep -f bot.js` |
| Dừng | `cd ~/bot && ./dung.sh` |
| Bật | `cd ~/bot && ./chay.sh` |
| Ghim coin | sửa `~/bot/ghim.txt` — bot đọc lại sau 30 giây, **không cần bật lại** |
| Sửa thuật toán | sửa `index.html` rồi `./dung.sh && ./chay.sh` |

**Sức khoẻ hệ thống — 2 câu, chạy định kỳ:**

```bash
tail -2 ~/bot/bot.log            # dòng cuối phải trong vòng 1 phút
```

```sql
SELECT MAX(cham_luc) lan_cham_cuoi, NOW() gio_server FROM keo;   -- lệch < 2 phút
```

Câu đầu giám sát bot (bên ghi), câu sau giám sát cron (bên chấm). Hai bên chết độc lập nhau, phải xem cả hai.

---

## Ba điều đã biết là hạn chế

**Trang xem công khai.** Ai có link đều xem được lệnh. Tên miền khó đoán nên rủi ro thấp; muốn khoá thì đặt HTTP basic auth cho subdomain.

**Không bấm ghim trên trang được nữa.** Ghim đặt bằng `ghim.txt`. Lý do ghi trong chính file đó.

**`file://` không dùng được nữa.** Mở `index.html` từ máy sẽ báo `⛔ CHƯA TẢI ĐƯỢC trangthai.json` — vì trình duyệt chặn `fetch` giữa hai file cục bộ. Dùng link, đó là thiết kế.
