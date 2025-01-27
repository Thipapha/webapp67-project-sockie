<?php
// รวมไฟล์การเชื่อมต่อฐานข้อมูล
require __DIR__ . '/../00conn.php';

if (isset($_GET['id'])) {
    $type_id = htmlspecialchars($_GET['id']);

    // สร้าง SQL query เพื่อลบประเภท
    $delete_query = "DELETE FROM types WHERE type_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("s", $type_id);

    if ($stmt->execute()) {
        // ลบสำเร็จ
        header("Location: ../09type_management.php");
        exit();
    } else {
        // ลบไม่สำเร็จ
        header("Location: ../09type_management.php?error=1");
        exit();
    }

    // ปิดการเชื่อมต่อ
    $stmt->close();
    $conn->close();
} else {
    // ไม่มีรหัสประเภทใน URL
    echo "<script>alert('ไม่มีรหัสประเภท'); window.location.href='09type_management.php';</script>";
}
?>
