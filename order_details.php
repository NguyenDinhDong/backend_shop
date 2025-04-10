<?php
include "db_config.php";
header("Content-Type: application/json");

// Kiểm tra phương thức GET
if (!isset($_SERVER["REQUEST_METHOD"]) || $_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode(["success" => false, "message" => "Yêu cầu không hợp lệ"]);
    exit();
}


// Lấy `order_id` từ request
$order_id = $_GET["order_id"] ?? null;
if (!$order_id) {
    echo json_encode(["success" => false, "message" => "Thiếu order_id"]);
    exit();
}

// 🔍 1️⃣ Kiểm tra đơn hàng có tồn tại không
$sql_order = "SELECT * FROM orders WHERE id = ?";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("i", $order_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();
$order = $result_order->fetch_assoc();

if (!$order) {
    echo json_encode(["success" => false, "message" => "Không tìm thấy đơn hàng"]);
    exit();
}

// 🔍 2️⃣ Lấy danh sách món ăn trong đơn hàng
$sql_items = "
    SELECT oi.food_id, f.name AS food_name, f.price, oi.quantity, oi.subtotal
    FROM order_items oi
    JOIN foods f ON oi.food_id = f.id
    WHERE oi.order_id = ?
";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$items = [];

while ($row = $result_items->fetch_assoc()) {
    $items[] = $row;
}

// ✅ Trả về JSON
$response = [
    "success" => true,
    "order" => [
        "id" => $order["id"],
        "user_id" => $order["user_id"],
        "total_price" => $order["total_price"],
        "status" => $order["status"],
        "created_at" => $order["created_at"],
        "items" => $items
    ]
];

echo json_encode($response);
?>
