<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sockie";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบว่ามีการส่งค่า product_id มาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    // รับค่า product_id จากฟอร์ม
    $product_id = $_POST['product_id']; // ไม่ต้องแปลงเป็น integer

    // ตรวจสอบว่า product_id ไม่เป็นค่าว่าง
    if (!empty($product_id)) {
        // SQL สำหรับลบข้อมูลเฉพาะที่มี product_id ตรงกัน
        $sql = "DELETE FROM products WHERE product_id = ?";

        // เตรียมคำสั่ง SQL
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $product_id); // ผูกค่า product_id (ใช้ "s" แทน "i" สำหรับ string)

        if ($stmt->execute()) {
            // ถ้าลบสำเร็จ ให้กลับไปที่หน้าข้อมูลสินค้า พร้อมแสดงข้อความยืนยัน
            header("Location: 05products.php?message=ลบสินค้าสำเร็จ");
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Invalid product ID.";
    }
} else {
    echo "No product ID provided.";
}

$conn->close();
?>
