<?php
require 'db_config.php'; // Đảm bảo file này kết nối MySQL đúng
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo json_encode(["success" => false, "message" => "Thiếu hoặc sai user_id"]);
    exit;
}

$user_id = intval($_GET['user_id']);

// Truy vấn lấy giỏ hàng
$query = "SELECT c.food_id, f.name, f.price, f.image_url, c.quantity 
          FROM cart c 
          JOIN foods f ON c.food_id = f.id 
          WHERE c.user_id = ?";
          
$stmt = $conn->prepare($query);
if (!$stmt) {
    die(json_encode(["success" => false, "message" => "Lỗi truy vấn: " . $conn->error]));
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];

while ($row = $result->fetch_assoc()) {
    $cart_items[] = [
        'food_id' => $row['food_id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'image_url' => $row['image_url'] ?: 'https://yourserver.com/default.jpg',
        'quantity' => $row['quantity']
    ];
}

// Trả kết quả JSON
echo json_encode([
    "success" => !empty($cart_items),
    "cart" => $cart_items,
    "message" => empty($cart_items) ? "Giỏ hàng trống" : "Tải giỏ hàng thành công"
], JSON_UNESCAPED_UNICODE);
?>
