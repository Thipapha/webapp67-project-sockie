<?php
// รวมไฟล์การเชื่อมต่อฐานข้อมูล
require __DIR__ . '/../00conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม
    $type_id = htmlspecialchars($_POST['type_id']);
    $type_name = htmlspecialchars($_POST['type_name']);

    // สร้าง SQL query เพื่อตรวจสอบว่ามีประเภทนี้ในฐานข้อมูลหรือไม่
    $check_query = "SELECT * FROM types WHERE type_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $type_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // หากประเภทนี้มีอยู่แล้ว ให้แจ้งเตือน
        echo "<script>alert('รหัสประเภทนี้มีอยู่แล้วในฐานข้อมูล กรุณาใช้รหัสอื่น'); window.location.href='09type_management.php';</script>";
    } else {
        // ถ้ายังไม่มี ให้เพิ่มประเภทสินค้า
        $insert_query = "INSERT INTO types (type_id, type_name) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ss", $type_id, $type_name);

        if ($stmt->execute()) {
            // เพิ่มสำเร็จ
            echo "success"; // ส่งกลับ success
        } else {
            echo 'เกิดข้อผิดพลาดในการเพิ่ม: ' . $stmt->error; // เพิ่มการแจ้งข้อผิดพลาด
        }
    } // ปิดเงื่อนไขที่ตรวจสอบว่าไม่มีประเภทนี้ในฐานข้อมูล

    // ปิดการเชื่อมต่อ
    $stmt->close();
    $conn->close();
}
?>
