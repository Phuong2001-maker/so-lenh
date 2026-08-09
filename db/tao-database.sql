-- =====================================================================
--  SỔ LỆNH GỘP — TẦNG LOG ĐO TỶ LỆ THẮNG
--  FILE DUY NHẤT. Chạy MỘT LẦN là xong toàn bộ: 2 bảng + 1 view + dữ liệu nền.
--
--  CÁCH CHẠY (khuyến nghị):
--    phpMyAdmin → bấm TÊN DATABASE ở thanh trái → tab Import → Choose File
--    → chọn file này → Import.
--    (Đừng dán vào tab SQL: trình soạn thảo CodeMirror hay không đồng bộ được
--     nội dung vào form và báo "Missing value in the form!" dù câu lệnh đúng.)
--
--  File KHÔNG ghi cứng tên database — nó chạy vào database nào đang được chọn,
--  nên dùng được với database mới mà không phải sửa gì.
--
--  Chạy lại bao nhiêu lần cũng an toàn: IF NOT EXISTS / INSERT IGNORE /
--  CREATE OR REPLACE. Không xoá dữ liệu cũ.
--
--  NGUYÊN TẮC THIẾT KẾ: bảng chỉ lưu thứ KHÔNG tái tạo được — tức trạng thái
--  bên trong máy nhận định tại đúng giây ra kèo. Mọi thứ suy được từ nến, và
--  mọi cột là hàm thuần của cột khác, đều nằm trong VIEW `v_keo`. Đổi công thức
--  chỉ cần sửa VIEW: không ALTER TABLE, không hỏng dữ liệu cũ.
-- =====================================================================


-- ---------------------------------------------------------------------
--  1. BẢNG `keo` — mỗi dòng là một kèo
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS keo (

  -- === định danh ===
  id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  uid             CHAR(26)         NOT NULL                COMMENT 'ULID sinh ở JS — chống ghi trùng khi client gửi lại',
  ts              BIGINT UNSIGNED  NOT NULL                COMMENT 'ms epoch UTC lúc phát kèo, giờ máy client',
  ts_srv          DATETIME(3)      NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT 'giờ server — đối chiếu lệch đồng hồ / look-ahead',
  coin            VARCHAR(20)      NOT NULL,
  inst_id         VARCHAR(40)      NOT NULL                COMMENT 'BẮT BUỘC — cron cần để biết kéo nến hợp đồng nào',
  ver             VARCHAR(16)      NOT NULL                COMMENT 'hash 6 trọng số + các ngưỡng, tự sinh ở JS',
  loai            ENUM('dir','far','top','zone','brk') NOT NULL DEFAULT 'dir',

  -- === ba mức giá của kế hoạch giao dịch ===
  dir             TINYINT          NOT NULL                COMMENT '1 = long, -1 = short',
  mid             DECIMAL(30,15)   NOT NULL                COMMENT 'giá giữa gộp 4 sàn lúc phát',
  bid             DECIMAL(30,15)   NULL                    COMMENT 'best bid THẬT của OKX',
  ask             DECIMAL(30,15)   NULL                    COMMENT 'best ask THẬT của OKX',
  vao             DECIMAL(30,15)   NOT NULL                COMMENT 'giá vào = tường cùng phía, lấy từ SỔ RIÊNG OKX',
  chot            DECIMAL(30,15)   NOT NULL                COMMENT 'giá chốt lời = tường đối diện',
  cat             DECIMAL(30,15)   NOT NULL                COMMENT 'giá cắt lỗ = vượt qua tường vào 0.30 x biên khung',
  han_ms          BIGINT UNSIGNED  NOT NULL                COMMENT 'hạn TUYỆT ĐỐI (ms epoch), không lưu số giờ',

  -- === trạng thái máy nhận định tại giây ra kèo ===
  s               DECIMAL(6,2)     NOT NULL                COMMENT 'điểm tổng hợp, KHÔNG làm tròn — cần phần lẻ để dò lại ngưỡng',
  sig_f30         DECIMAL(5,4)     NULL                    COMMENT 'NULL = thiếu dữ liệu, KHÁC HẲN 0 = trung tính',
  sig_f5          DECIMAL(5,4)     NULL,
  sig_bk          DECIMAL(5,4)     NULL,
  sig_oi          DECIMAL(5,4)     NULL,
  sig_fu          DECIMAL(5,4)     NULL,
  sig_lq          DECIMAL(5,4)     NULL,
  book_usd        BIGINT UNSIGNED  NOT NULL                COMMENT 'tổng tiền sổ GỘP 4 sàn (mẫu số chuẩn hoá dòng tiền)',
  mua30_usd       BIGINT UNSIGNED  NOT NULL                COMMENT 'dòng tiền mua gross 30 phút',
  ban30_usd       BIGINT UNSIGNED  NOT NULL                COMMENT 'bán gross 30 phút — tách riêng để phân biệt chợ chết với hai phe ngang cơ',
  cap             DECIMAL(8,6)     NOT NULL                COMMENT 'biên dao động của khung',
  frame_h         TINYINT UNSIGNED NOT NULL                COMMENT '1 / 2 / 4 giờ',
  ex_mask         TINYINT UNSIGNED NOT NULL                COMMENT 'bitmask sàn đang live lúc phát',

  -- === chất lượng tường, đóng băng lúc ra kèo ===
  vao_trust       TINYINT UNSIGNED  NOT NULL,
  vao_sv          SMALLINT UNSIGNED NOT NULL DEFAULT 0     COMMENT 'số lần tường chịu được giá áp sát',
  vao_pl          SMALLINT UNSIGNED NOT NULL DEFAULT 0     COMMENT 'số lần TỰ RÚT khi giá tới — chỉ báo lệnh giả',
  vao_usd         BIGINT UNSIGNED   NOT NULL               COMMENT 'tiền của tường vào, TRÊN OKX',
  vao_ty_le_okx   DECIMAL(5,4)      NULL                   COMMENT 'tiền OKX / tiền gộp tại mốc đó — đo thực tế 0.14-0.44',
  chot_trust      TINYINT UNSIGNED  NOT NULL,
  chot_usd        BIGINT UNSIGNED   NOT NULL,
  chot_ty_le_okx  DECIMAL(5,4)      NULL,

  -- === bối cảnh không tái tạo được ===
  tuoi_may        INT UNSIGNED     NOT NULL                COMMENT 'giây từ khi engine coin đó bật — máy nguội thì tường còn ở mốc tin 30',
  tuoi_coin       DECIMAL(8,2)     NULL                    COMMENT 'số ngày từ listTime OKX',
  chan            TINYINT UNSIGNED NOT NULL DEFAULT 0      COMMENT 'bitmask chốt chặn: 0=kèo THẬT, 1=|S|<25, 2=dưới 2 tín hiệu, 4=flow ngược, 8=bão hoà không xác nhận',
  ghim            TINYINT(1)       NOT NULL DEFAULT 0      COMMENT '1 = coin do người dùng ghim tay (dấu thiên lệch lựa chọn)',

  -- === kết quả — CHỈ cron được ghi, client không bao giờ ===
  kq              ENUM('mo','thang','thua','het_han','nhap_nhang','khong_khop','bo_qua') NOT NULL DEFAULT 'mo'
                                                           COMMENT 'quy ước CHẠM (bóng nến). khong_khop = lệnh chờ tại `vao` không bao giờ khớp -> KHÔNG tính vào tỷ lệ thắng',
  kq_dong         ENUM('mo','thang','thua','het_han','nhap_nhang','khong_khop','bo_qua') NULL DEFAULT NULL
                                                           COMMENT 'quy ước ĐÓNG NẾN vượt hẳn — so với kq để đo độ nhạy của kết luận',
  gia_ra          DECIMAL(30,15)   NULL,
  ts_ra           BIGINT UNSIGNED  NULL,
  mfe_bp          INT              NULL                    COMMENT 'giá TỐT nhất kèo từng chạm, điểm cơ bản',
  mae_bp          INT              NULL                    COMMENT 'giá XẤU nhất kèo từng chạm, điểm cơ bản',
  cham_luc        DATETIME(3)      NULL                    COMMENT 'lúc cron chấm xong — dùng giám sát sức khoẻ của chính bộ chấm sổ',
  ghi_chu         VARCHAR(120)     NULL                    COMMENT 'đánh dấu tay đợt dữ liệu rác',

  PRIMARY KEY (id),
  UNIQUE KEY uq_uid (uid),
  KEY ix_cham (kq, han_ms),
  KEY ix_doc  (coin, loai, ts)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Nhat ky keo cua may nhan dinh - dung do ty le thang';


-- ---------------------------------------------------------------------
--  2. BẢNG `phi` — tham số chi phí, có hiệu lực theo thời gian
--
--  Tách khỏi bảng kèo để đổi bậc phí KHÔNG phải sửa dữ liệu cũ. Ghi phí cứng
--  vào từng dòng rồi gọi cột là "lợi nhuận thật" là tự lừa mình: vài tháng sau
--  chính mình sẽ đọc một con số giả định như thể là tiền thật.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS phi (
  id          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tu_ngay     DATE           NOT NULL                      COMMENT 'có hiệu lực từ ngày này',
  taker_bps   DECIMAL(6,3)   NOT NULL DEFAULT 5.000        COMMENT 'phí taker MỘT chiều, điểm cơ bản',
  truot_bps   DECIMAL(6,3)   NOT NULL DEFAULT 2.000        COMMENT 'trượt giá giả định cả vòng',
  ghi_chu     VARCHAR(120)   NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_tu_ngay (tu_ngay)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tham so chi phi theo thoi gian - join luc doc, khong ghi cung vao tung keo';

INSERT IGNORE INTO phi (tu_ngay, taker_bps, truot_bps, ghi_chu)
VALUES ('2026-01-01', 5.000, 2.000, 'OKX taker mac dinh - GIA DINH, chua do thuc te');


-- ---------------------------------------------------------------------
--  3. VIEW `v_keo` — mọi cột dẫn xuất nằm ở đây, KHÔNG lưu trong bảng
--
--  Cột quan trọng nhất là `p_baseline`: xác suất chạm chốt trước khi chạm cắt
--  lỗ NẾU giá đi hoàn toàn ngẫu nhiên. Vì chốt lời và cắt lỗ đều là TƯỜNG với
--  khoảng cách bất đối xứng thay đổi từng kèo, tỷ lệ thắng phải so với con số
--  NÀY chứ không phải với 50%. Thắng 60% khi baseline là 65% nghĩa là TỆ HƠN
--  tung đồng xu.
-- ---------------------------------------------------------------------
CREATE OR REPLACE VIEW v_keo AS
SELECT t.*,
       CASE WHEN t.r_truoc_phi IS NULL THEN NULL
            ELSE t.r_truoc_phi - t.phi_r END                              AS r_sau_phi
FROM (
  SELECT
    k.*,
    ABS(k.chot - k.vao)                                                   AS d_chot,
    ABS(k.vao  - k.cat)                                                   AS d_cat,
    ABS(k.chot - k.vao) / NULLIF(ABS(k.vao - k.cat), 0)                   AS rr_du_kien,
    ABS(k.vao - k.cat)
      / NULLIF(ABS(k.chot - k.vao) + ABS(k.vao - k.cat), 0)               AS p_baseline,
    ABS(k.chot - k.vao) / NULLIF(k.vao, 0) * 10000                        AS chot_bp,
    ABS(k.vao  - k.cat) / NULLIF(k.vao, 0) * 10000                        AS cat_bp,
    (k.ask - k.bid)     / NULLIF(k.mid, 0) * 10000                        AS spread_bp,
    (k.ts_ra - k.ts) / 60000                                              AS giu_phut,
    CASE WHEN k.chan = 0 THEN 'that' ELSE 'bong' END                      AS nhom,
    -- lệnh chờ không khớp: kèo chưa từng tồn tại, phải đếm riêng
    CASE WHEN k.kq = 'khong_khop' THEN 1 ELSE 0 END                       AS khong_khop,
    CASE k.kq      WHEN 'thang' THEN 1 WHEN 'thua' THEN 0 ELSE NULL END   AS thang01,
    CASE k.kq_dong WHEN 'thang' THEN 1 WHEN 'thua' THEN 0 ELSE NULL END   AS thang01_dong,
    -- HẾT HẠN KHÔNG PHẢI THUA: kèo đứng yên rồi hết giờ có R gần 0, khác hẳn
    -- kèo chạm cắt lỗ (-1R). Gộp chung là đánh giá oan chiến lược.
    CASE
      WHEN k.kq = 'thang' THEN  ABS(k.chot - k.vao) / NULLIF(ABS(k.vao - k.cat), 0)
      WHEN k.kq = 'thua'  THEN -1.0
      WHEN k.kq = 'het_han' AND k.gia_ra IS NOT NULL
        THEN (k.gia_ra - k.vao) * k.dir / NULLIF(ABS(k.vao - k.cat), 0)
      ELSE NULL
    END                                                                   AS r_truoc_phi,
    (2 * p.taker_bps + p.truot_bps) / 10000 * k.vao
      / NULLIF(ABS(k.vao - k.cat), 0)                                     AS phi_r
  FROM keo k
  LEFT JOIN phi p
    ON p.tu_ngay = (SELECT MAX(tu_ngay) FROM phi WHERE tu_ngay <= DATE(k.ts_srv))
) t;


-- ---------------------------------------------------------------------
--  4. NÂNG CẤP CHO DATABASE ĐÃ TẠO TRƯỚC ĐÓ
--  Bổ sung giá trị 'khong_khop' vào hai cột ENUM. Với database mới tạo ở
--  bước 1 thì hai câu này là no-op. MODIFY COLUMN chạy lại bao nhiêu lần
--  cũng an toàn và không đụng tới dữ liệu sẵn có.
-- ---------------------------------------------------------------------
ALTER TABLE keo
  MODIFY COLUMN kq ENUM('mo','thang','thua','het_han','nhap_nhang','khong_khop','bo_qua')
    NOT NULL DEFAULT 'mo'
    COMMENT 'quy ước CHẠM (bóng nến). khong_khop = lệnh chờ không bao giờ khớp',
  MODIFY COLUMN kq_dong ENUM('mo','thang','thua','het_han','nhap_nhang','khong_khop','bo_qua')
    NULL DEFAULT NULL
    COMMENT 'quy ước ĐÓNG NẾN vượt hẳn — so với kq để đo độ nhạy của kết luận';


-- =====================================================================
--  XONG. Kiểm tra bằng câu dưới — phải ra keo / phi / v_keo và 49 cột.
-- =====================================================================
SELECT TABLE_NAME AS ten, TABLE_TYPE AS loai
FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()
UNION ALL
SELECT 'SO COT BANG keo', CAST(COUNT(*) AS CHAR)
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'keo';


/* =====================================================================
   TRUY VẤN THAM KHẢO — không tự chạy, copy ra tab SQL khi cần.
   Trang bao-cao.php đã hiển thị sẵn tất cả những cái này dưới dạng web.

   -- Bảng tổng: LUÔN đọc "hon_ngau_nhien", đừng bao giờ đọc mỗi ty_le_thang
   SELECT nhom, COUNT(*) n, SUM(thang01) dung,
          ROUND(AVG(thang01)*100,1)                     ty_le_thang,
          ROUND(AVG(p_baseline)*100,1)                  baseline,
          ROUND((AVG(thang01)-AVG(p_baseline))*100,1)   hon_ngau_nhien,
          ROUND(AVG(rr_du_kien),2)                      rr,
          ROUND(SUM(r_sau_phi),2)                       tong_R
   FROM v_keo WHERE thang01 IS NOT NULL GROUP BY nhom;

   -- 4 chốt chặn đang cứu hay đang cắt mất tiền?  (chan = 0 là kèo thật)
   SELECT chan, COUNT(*) n,
          ROUND(AVG(thang01)*100,1) ty_le,
          ROUND((AVG(thang01)-AVG(p_baseline))*100,1) hon_ngau_nhien,
          ROUND(SUM(r_sau_phi),2) tong_R
   FROM v_keo WHERE thang01 IS NOT NULL GROUP BY chan ORDER BY chan;

   -- Sức khoẻ bộ chấm sổ: kèo mở lâu bất thường = cron chết hoặc coin delist
   SELECT coin, inst_id, FROM_UNIXTIME(ts/1000) ghi_luc,
          FROM_UNIXTIME(han_ms/1000) han, cham_luc
   FROM keo WHERE kq = 'mo' AND han_ms < UNIX_TIMESTAMP()*1000
   ORDER BY han_ms ASC;
   ===================================================================== */
