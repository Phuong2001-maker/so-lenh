# Triển khai tầng log — 6 bước

## 1. Tạo database

Vào phpMyAdmin → bấm **tên database** ở thanh trái → tab **Import** → **Choose File**
→ chọn `db/tao-database.sql` → **Import**.

Một file duy nhất, chạy một lần là xong: 2 bảng + 1 view + dữ liệu nền. Chạy lại bao
nhiêu lần cũng an toàn, không xoá dữ liệu cũ. File không ghi cứng tên database nên
dùng được với bất kỳ database nào đang chọn.

⚠ **Đừng dán vào tab SQL.** Trình soạn thảo CodeMirror của phpMyAdmin hay không đồng bộ
được nội dung vào form và báo *"Missing value in the form!"* dù câu lệnh hoàn toàn đúng.
Đường Import không đi qua nó.

## 2. Điền `config.php`

Mở `php/config.php`, điền 5 chỗ:

| Hằng số | Lấy ở đâu |
|---|---|
| `DB_NAME` | **tên database mới** bạn vừa tạo |
| `DB_USER` | 1Panel → Quản lý MySQL → tên tài khoản CSDL |
| `DB_PASS` | mật khẩu tài khoản đó |
| `API_TOKEN` | **tự nghĩ** một chuỗi ngẫu nhiên ≥ 32 ký tự |
| `ORIGIN_CHO_PHEP` | giữ nguyên nếu mở app bằng `file://`; thêm tên miền nếu đưa app lên hosting |

## 3. Tải thư mục `php/` lên hosting

Đặt vào thư mục web (thường là `public_html/` hoặc `wwwroot/`). Ví dụ được đường dẫn:

```
https://onehost-cloudhn112404.000nethost.com/php/ghi.php
```

⚠ Nếu hosting cho phép, đặt `config.php` **ngoài** thư mục web rồi sửa dòng `require` trong 3 file kia. Không thì ít nhất tạo file `.htaccess` cạnh nó với nội dung `Require all denied` để chặn truy cập trực tiếp.

## 4. Nối app với server

Mở `index.html`, tìm hai hằng số ở đầu khối `<script>` (khoảng dòng 145):

```
const API_LOG   = '';    → dán URL ghi.php ở bước 3
const API_TOKEN = '';    → dán ĐÚNG token đã đặt ở bước 2
```

Để trống `API_LOG` là tắt hẳn việc ghi, app chạy y như cũ.

## 5. Cài cron chấm sổ

1Panel → Cron → thêm tác vụ chạy **mỗi phút**:

```
php /duong/dan/day/du/php/cham.php
```

Chạy tay một lần trước để xem có lỗi không — nó in ra số kèo đang mở và kết quả từng cái.

## 6. Xem báo cáo

Mở `https://.../php/bao-cao.php` bằng trình duyệt.

---

## Kiểm tra đường ống có thông không

Sau khi làm xong bước 4, mở app và chờ khoảng 2 phút. Rồi trong phpMyAdmin:

```sql
SELECT coin, dir, s, chan, kq, FROM_UNIXTIME(ts/1000) AS luc_ghi
FROM keo ORDER BY id DESC LIMIT 10;
```

Có dòng hiện ra là thông. Không có thì mở Console trình duyệt (F12) xem lỗi mạng — thường là sai token (401) hoặc thiếu tên miền trong `ORIGIN_CHO_PHEP` (lỗi CORS).

## Những chỗ đã biết là còn hạn chế

**Hàng đợi gửi nằm trong RAM.** Tải lại trang là mất các kèo chưa gửi được. Chấp nhận ở bản đầu vì gửi theo lô mỗi 10 giây, cửa sổ mất mát nhỏ. Nếu thấy mất nhiều thì chuyển sang IndexedDB.

**Mỗi coin chỉ một kèo mở tại một thời điểm**, giải phóng khi hết hạn (1–4 giờ tuỳ khung). Nếu giá chạm chốt lời sớm thì cron đã đóng kèo trên server nhưng client không biết, nên phải chờ hết hạn mới sinh kèo mới. Đổi lại: các kèo thật sự độc lập với nhau, không phải 10 quan sát của cùng một cấu trúc sổ lệnh.

**Ngưỡng ghi là 15, thấp hơn ngưỡng hiển thị 25** — có chủ đích. Kèo bị 4 chốt chặn loại bỏ vẫn được ghi và vẫn được chấm, đánh dấu bằng cột `chan`. Đây là nhóm đối chứng duy nhất để về sau biết chốt chặn đang cứu hay đang cắt mất kèo thắng.

**Chi phí trong báo cáo là giả định** (taker 5bp/chiều + trượt 2bp), chưa đo thực tế. Đổi thì thêm một dòng vào bảng `phi`, không phải sửa dữ liệu cũ.

**Đây là sổ giấy.** Chưa có lệnh thật nào, nên "Tổng R sau phí" là **trần trên** — thực tế sẽ thấp hơn vì còn độ trễ đặt lệnh, khớp một phần, và chính lệnh của mình làm dịch tường.
