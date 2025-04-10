<?php
include "db_config.php";
header("Content-Type: application/json");

// 📌 Đọc dữ liệu từ JSON Body
$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data["user_id"] ?? null;
$total_price = $data["total_price"] ?? null;
$items = $data["items"] ?? [];

// 🔍 Kiểm tra dữ liệu đầu vào
if (!$user_id || !$total_price || empty($items)) {
    echo json_encode(["success" => false, "error" => "Dữ liệu không hợp lệ"]);
    exit();
}

// 1️⃣ Thêm đơn hàng vào bảng `orders`
$sql = "INSERT INTO orders (user_id, total_price, status, created_at) VALUES (?, ?, 'pending', NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("id", $user_id, $total_price);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "error" => "Lỗi khi đặt hàng"]);
    exit();
}

// 2️⃣ Lấy `order_id` vừa tạo
$order_id = $conn->insert_id;

// 3️⃣ Thêm từng món ăn vào `order_items`
$sql_item = "INSERT INTO order_items (order_id, food_id, quantity, subtotal) VALUES (?, ?, ?, ?)";
$stmt_item = $conn->prepare($sql_item);

foreach ($items as $item) {
    $food_id = $item["food_id"] ?? null;
    $quantity = $item["quantity"] ?? null;
    $subtotal = $item["subtotal"] ?? null;

    if (!$food_id || !$quantity || !$subtotal) {
        echo json_encode(["success" => false, "error" => "Dữ liệu món ăn không hợp lệ"]);
        exit();
    }

    $stmt_item->bind_param("iiid", $order_id, $food_id, $quantity, $subtotal);
    
    if (!$stmt_item->execute()) {
        echo json_encode(["success" => false, "error" => "Lỗi khi thêm món ăn vào đơn hàng"]);
        exit();
    }
}

// ✅ Thành công
echo json_encode(["success" => true, "message" => "Đặt hàng thành công", "order_id" => $order_id]);

// 🔚 Đóng kết nối
$stmt->close();
$stmt_item->close();
$conn->close();
?>
