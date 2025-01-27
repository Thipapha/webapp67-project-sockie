<?php
$servername = "localhost"; // ชื่อเซิร์ฟเวอร์ฐานข้อมูล
$username = "root";        // ชื่อผู้ใช้ฐานข้อมูล (ค่าเริ่มต้นของ XAMPP)
$password = "";            // รหัสผ่านฐานข้อมูล (ค่าเริ่มต้นของ XAMPP คือว่าง)
$dbname = "sockie";        // ชื่อฐานข้อมูล

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}
?>