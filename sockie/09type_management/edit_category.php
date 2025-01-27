<?php
// เชื่อมต่อฐานข้อมูล
require __DIR__ . '/../00conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_id = $_POST['type_id'];
    $type_name = $_POST['type_name'];

    // อัปเดตข้อมูลประเภทสินค้าในฐานข้อมูล
    $sql = "UPDATE types SET type_name = ? WHERE type_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $type_name, $type_id);

    if ($stmt->execute()) {
        // ส่งสัญญาณว่าอัปเดตสำเร็จ
        echo "<script>
                alert('อัปเดตสำเร็จ');
                window.location.href = '../09type_management.php'; // เปลี่ยนเส้นทางไปยังหน้าหลัก
              </script>";
    } else {
        echo "<script>
                alert('เกิดข้อผิดพลาด: " . $stmt->error . "');
              </script>"; // แสดงข้อผิดพลาดถ้าอัปเดตไม่สำเร็จ
    }

    $stmt->close();
}
$conn->close();
?>

