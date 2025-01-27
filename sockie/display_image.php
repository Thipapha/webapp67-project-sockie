<?php
require_once '00conn.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // ดึงข้อมูลภาพจากฐานข้อมูล
    $result = $conn->query("SELECT product_image FROM products WHERE product_id = '$product_id'");

    if ($result) {
        $row = $result->fetch_assoc();
        if ($row) {
            header("Content-type: image/jpeg"); // หรือ image/png, image/gif ตามชนิดภาพ
            echo $row['product_image']; // แสดงภาพ
        } else {
            echo "ไม่พบภาพ";
        }
    } else {
        echo "เกิดข้อผิดพลาดในการดึงข้อมูล: " . $conn->error;
    }
} else {
    echo "ไม่มีรหัสสินค้าที่ระบุ";
}

$conn->close(); // ปิดการเชื่อมต่อฐานข้อมูล
?>
