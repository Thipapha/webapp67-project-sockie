<?php
// เชื่อมต่อฐานข้อมูล
$servername = "localhost"; // หรือชื่อเซิร์ฟเวอร์ของคุณ
$username = "username"; // ชื่อผู้ใช้ฐานข้อมูล
$password = "password"; // รหัสผ่านฐานข้อมูล
$dbname = "sockie"; // ชื่อฐานข้อมูล

$conn = new mysqli($servername, $username, $password, $dbname);

// เช็คการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึงข้อมูลจากตาราง products
$sql = "SELECT product_id, product_name, price FROM products";
$result = $conn->query($sql);

$products = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = [
            'product_id' => $row['product_id'], // เปลี่ยนจาก 'id' เป็น 'product_id'
            'product_name' => $row['product_name'], // เปลี่ยนจาก 'name' เป็น 'product_name'
            'price' => $row['price']
        ];
    }
}

$conn->close();

// ส่งข้อมูลเป็น JSON
header('Content-Type: application/json');
echo json_encode($products);
?>
