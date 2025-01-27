<?php
include '00conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $product_id = $_POST['product_id'];
    $product_name = $conn->real_escape_string($_POST['product_name']);
    $type_name = $conn->real_escape_string($_POST['type_name']);
    $color = $conn->real_escape_string($_POST['color']);
    $size = $conn->real_escape_string($_POST['size']);
    $price = floatval($_POST['price']);
    $length_lv = $conn->real_escape_string($_POST['length_lv']);
    $unit_cost = floatval($_POST['unit_cost']);
    $product_image = $_FILES['product_image']['name'];

    // ถ้ามีการอัพโหลดรูปภาพ
    if (!empty($product_image)) {
        // อัพโหลดรูปภาพไปยังโฟลเดอร์ uploads
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($product_image);
        move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file);
        
        // อัพเดทคำสั่ง SQL
        $sql = "UPDATE products SET product_name = ?, type_name = ?, color = ?, size = ?, price = ?, length_lv = ?, unit_cost = ?, product_image = ? WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdsdss", $product_name, $type_name, $color, $size, $price, $length_lv, $unit_cost, $product_image, $product_id);
    } else {
        // หากไม่ต้องการเปลี่ยนรูปภาพ
        $sql = "UPDATE products SET product_name = ?, type_name = ?, color = ?, size = ?, price = ?, length_lv = ?, unit_cost = ? WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdsds", $product_name, $type_name, $color, $size, $price, $length_lv, $unit_cost, $product_id);
    }

    // ประมวลผลการอัพเดท
    if ($stmt->execute()) {
        echo "ข้อมูลสินค้าถูกอัพเดทเรียบร้อย";
        header("Location: 05products.php"); // เปลี่ยนไปหน้ารายการสินค้า
        exit;
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>

