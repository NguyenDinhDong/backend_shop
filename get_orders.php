<?php
header("Content-Type: application/json");
require "db_config.php"; // Kết nối database

// Lấy danh sách đơn hàng
$sql = "SELECT * FROM orders ORDER BY id DESC";
$result = $conn->query($sql);

$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Kiểm tra nếu không có tổng tiền, tính tổng lại
        if (!isset($row["total"])) {
            $total = 0;
            $order_id = $row["id"];
            $items_sql = "
                SELECT oi.subtotal
                FROM order_items oi
                WHERE oi.order_id = ?
            ";
            $stmt = $conn->prepare($items_sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $items_result = $stmt->get_result();

            while ($item = $items_result->fetch_assoc()) {
                $total += $item["subtotal"];
            }
            $row["total"] = $total; // Gán lại tổng tiền cho đơn hàng
        }

        // Lấy danh sách món ăn trong order_items
        $order_id = $row["id"];
        $items_sql = "
            SELECT oi.food_id, f.name AS food_name, f.price, oi.quantity, oi.subtotal
            FROM order_items oi
            JOIN foods f ON oi.food_id = f.id
            WHERE oi.order_id = ?
        ";
        $stmt = $conn->prepare($items_sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $items_result = $stmt->get_result();

        $items = [];
        while ($item = $items_result->fetch_assoc()) {
            $items[] = $item;
        }

        // Thêm ngày đặt vào đơn hàng (kiểm tra nếu ngày không có)
        if (!isset($row['date'])) {
            $row['date'] = date('Y-m-d H:i:s'); // Thêm ngày giờ hiện tại nếu không có
        }

        $row["items"] = $items; // Gán danh sách món ăn vào đơn hàng
        $orders[] = $row;
    }
}

echo json_encode(["success" => true, "orders" => $orders]);
$conn->close();
?>
