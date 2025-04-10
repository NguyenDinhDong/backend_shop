<?php
require_once 'db_config.php';  // Kết nối tới cơ sở dữ liệu

// Kiểm tra kết nối cơ sở dữ liệu
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra xem có 'order_id' và 'new_status' trong query string không
if (isset($_GET['order_id']) && isset($_GET['new_status'])) {
    $orderId = $_GET['order_id'];
    $newStatus = $_GET['new_status'];

    // In ra các giá trị nhận được để kiểm tra
    error_log("Received order_id: " . $orderId . " and new_status: " . $newStatus);

    // Kiểm tra xem trạng thái mới có hợp lệ không (nằm trong ENUM)
    $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
    if (!in_array($newStatus, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
        exit;
    }

    // Kiểm tra xem đơn hàng có tồn tại trong cơ sở dữ liệu
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("SQL Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi chuẩn bị câu truy vấn']);
        exit;
    }

    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Kiểm tra nếu đơn hàng tồn tại
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();

        // Kiểm tra nếu trạng thái hiện tại của đơn hàng đã là trạng thái mới
        if ($order['status'] === $newStatus) {
            echo json_encode(['success' => true, 'message' => 'Trạng thái đơn hàng đã giống với trạng thái hiện tại']);
            exit;
        }

        // Cập nhật trạng thái đơn hàng
        $updateSql = "UPDATE orders SET status = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        if ($updateStmt === false) {
            error_log("SQL Prepare failed: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Lỗi khi chuẩn bị câu truy vấn cập nhật']);
            exit;
        }

        $updateStmt->bind_param("si", $newStatus, $orderId);
        if ($updateStmt->execute()) {
            // Thành công - trả về thông báo thành công
            echo json_encode(['success' => true, 'message' => 'Trạng thái đơn hàng đã được cập nhật']);
        } else {
            // Nếu xảy ra lỗi trong quá trình cập nhật
            error_log("Error executing update query: " . $updateStmt->error);
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái']);
        }
    } else {
        // Nếu đơn hàng không tồn tại trong cơ sở dữ liệu
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại']);
    }

    $conn->close();
} else {
    // Trường hợp không có 'order_id' hoặc 'new_status' trong request
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
}
?>
