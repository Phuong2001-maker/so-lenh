<?php
/* =====================================================================
   CẤU HÌNH — điền 5 giá trị dưới rồi tải cả thư mục php/ lên hosting.
   File này bị 3 file kia require, KHÔNG gọi trực tiếp từ trình duyệt.
   ===================================================================== */

// --- Kết nối MySQL (lấy trong 1Panel > Quản lý MySQL) ---
const DB_HOST = 'localhost';
const DB_NAME = '';          // <-- ĐIỀN tên database (phải khớp DB đã chạy tao-database.sql)
const DB_USER = '';          // <-- ĐIỀN tên tài khoản CSDL
const DB_PASS = '';          // <-- ĐIỀN mật khẩu

/* Token bí mật: tự nghĩ một chuỗi ngẫu nhiên dài (>= 32 ký tự) rồi dán vào đây
   VÀ dán y hệt vào hằng số API_TOKEN trong index.html.
   Không có nó thì endpoint ghi nằm trần trên internet, bot quét web sẽ đổ rác vào bảng. */
const API_TOKEN = '';        // <-- ĐIỀN, ví dụ: 'k7Qp2mXv9wRt4NcB8sLd3FhJ6gYzA1oE'

/* Tên miền được phép gọi ghi.php từ trình duyệt.
   Mở app bằng file:// thì Origin là chuỗi 'null' — giữ nguyên dòng dưới.
   Khi đưa app lên hosting thì thêm tên miền thật vào mảng. */
const ORIGIN_CHO_PHEP = ['null', 'http://localhost', 'http://127.0.0.1'];

// ---------------------------------------------------------------------
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::ATTR_EMULATE_PREPARES   => false]
        );
    }
    return $pdo;
}

function traLoi(int $ma, array $data): void {
    http_response_code($ma);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/* So sánh token theo kiểu chống dò thời gian */
function kiemTraToken(): void {
    $t = $_SERVER['HTTP_X_TOKEN'] ?? '';
    if (API_TOKEN === '' || !hash_equals(API_TOKEN, $t)) {
        traLoi(401, ['ok' => false, 'loi' => 'token sai']);
    }
}

function batCors(): void {
    $o = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($o, ORIGIN_CHO_PHEP, true)) {
        header('Access-Control-Allow-Origin: ' . $o);
        header('Vary: Origin');
    }
    header('Access-Control-Allow-Headers: Content-Type, X-Token');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }
}
