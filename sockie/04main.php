<?php
// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "sockie");

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query นับจำนวนรายการสินค้าทั้งหมดในตาราง products
$sql = "SELECT COUNT(DISTINCT product_id) AS total_products FROM products";
$result = $conn->query($sql);
$total_products = $result->num_rows > 0 ? $result->fetch_assoc()['total_products'] : 0;

// Query นับจำนวนสินค้าที่หมดสต็อก
$sql = "SELECT COUNT(*) AS out_of_stock FROM products WHERE in_stock = 0";
$result = $conn->query($sql);
$out_of_stock = $result->num_rows > 0 ? $result->fetch_assoc()['out_of_stock'] : 0;

// Query ดึงชื่อสินค้าที่มี quantity มากที่สุด
$sql = "SELECT product_name FROM products ORDER BY in_stock DESC LIMIT 1"; // เปลี่ยน quantity เป็น in_stock
$result = $conn->query($sql);
$best_selling_product = $result->num_rows > 0 ? $result->fetch_assoc()['product_name'] : "ไม่มีข้อมูล";

// SQL Query สำหรับดึงข้อมูลกิจกรรมล่าสุดจากทั้งสองตาราง
$sql = "
    SELECT 
        transfer_date AS date,
        from_location AS source,
        to_location AS destination,
        total_quantity AS total_quantity,
        received_by AS responsible,
        'นำเข้า' AS transaction_type -- เพิ่มคอลัมน์สำหรับแสดงประเภทกิจกรรม
    FROM transfer_in_header
    UNION ALL
    SELECT 
        transfer_date AS date,
        from_location AS source,
        to_location AS destination,
        total_quantity AS total_quantity,
        send_by AS responsible,
        'ขายออก' AS transaction_type -- เพิ่มคอลัมน์สำหรับแสดงประเภทกิจกรรม
    FROM transfer_out_header
    ORDER BY date DESC
    LIMIT 5
";

// ดำเนินการ Query
$result = $conn->query($sql);

// ตรวจสอบว่ามีผลลัพธ์หรือไม่
if (!$result) {
    die("Query failed: " . $conn->error);
}

// ปิดการเชื่อมต่อ
$conn->close();
?>
<Style>
.right-text {
 text-align: right;
}
</Style>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก - ระบบจัดการคลังสินค้า</title>
    <link rel="stylesheet" href="css/04main.css">
    <link rel="stylesheet" href="css/layout.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- ส่วนหัว -->
    <header >
        <h1>ระบบจัดการคลังสินค้า - ถุงเท้า</h1>
        <span class="logo-container">
            <img src="images/Logo Web App.png" alt="Logo" class="logo-img">
        </span>
        <div class="right-text">
        <a href="mainpos.php"  style="text-align: right;">ไปหน้า pos  </a></div>           
    </header>

    <!------ mamu bar-------->
    <nav class="custom-navbar d-flex align-items-center" style="height: 70px;">
        <!-- เมนูอยู่ชิดซ้าย -->
    <div class="flex-grow-1 d-flex justify-content-start">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link active" href="04main.php">หน้าหลัก</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="05products.php">ข้อมูลสินค้า</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="06inventory_management.php">จัดการสินค้าคงคลัง</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="07add_product.php">เพิ่มสินค้า</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="08transaction_history.php">บันทึกการรับและจ่ายสินค้า</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="09type_management.php">จัดการหมวดหมู่</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="10reports_analytics.php">รายงานและการวิเคราะห์</a>
            </li>
        </ul>
    </div>

    <!-- ปุ่มออกจากระบบชิดขวา -->
    <div class="logout-container ms-3">
        <a href="01index.html" class="btn btn-outline-warning btn-custom">
            <i class="fa fa-sign-out-alt"></i> ออกจากระบบ
        </a>
    </div>
    </nav>

    <script>
        // กำหนดชื่อไฟล์ HTML สำหรับหน้า
        const currentPage = window.location.pathname.split("/").pop();

        // ค้นหาทุกลิงก์ในเมนูบาร์
        const navLinks = document.querySelectorAll('.custom-navbar .nav-link');

        navLinks.forEach(link => {
            // เปรียบเทียบ href ของลิงก์กับชื่อไฟล์ปัจจุบัน
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active'); // เพิ่มคลาส active ให้ลิงก์ที่ตรงกัน
            } else {
                link.classList.remove('active'); // ลบ active ออกจากลิงก์อื่นๆ
            }
        });
    </script> 

    <!-- ส่วนหลัก -->
    <div class="main_area">
        <div class="dashboard-cards">
            <div class="card">
                <h3>จำนวนสินค้าคงคลัง</h3>
                <p><?php echo $total_products; ?> รายการ</p>
            </div>
            <div class="card">
                <h3>สินค้าหมดสต็อก</h3>
                <p><?php echo $out_of_stock; ?> รายการ</p>
            </div>
            <div class="card">
                <h3>สินค้าที่ขายดีที่สุด</h3>
                <p><?php echo htmlspecialchars($best_selling_product); ?></p>
            </div>
        </div>

        <div class="recent-activities">
            <h2>กิจกรรมล่าสุด</h2>
            <table>
                <thead>
                    <tr>
                        <th>วันที่-เวลา</th>
                        <th>ประเภทกิจกรรม</th>
                        <th>ต้นทาง</th>
                        <th>ปลายทาง</th>
                        <th>จำนวนรวม</th>
                        <th>ผู้รับผิดชอบ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // แสดงข้อมูลในตาราง
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['transaction_type']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['source']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['destination']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['total_quantity']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['responsible']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>ไม่มีข้อมูลกิจกรรมล่าสุด</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ส่วนล่าง -->
    <footer>
        <p>ระบบจัดการคลังสินค้า © 2024</p>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
