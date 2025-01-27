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

// รับค่าการค้นหาจากผู้ใช้ (ถ้ามี)
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

// SQL Query สำหรับการค้นหาพร้อมฟิลเตอร์ตาม search_query
$sql = "
    SELECT 
        transfer_in_id AS transfer_id, 
        transfer_date AS date,
        from_location AS source,
        to_location AS destination,
        total_quantity AS total_quantity,
        received_by AS responsible,
        'นำเข้า' AS transaction_type
    FROM transfer_in_header
    WHERE transfer_in_id LIKE '%$search_query%' OR from_location LIKE '%$search_query%' OR to_location LIKE '%$search_query%'
    UNION ALL
    SELECT 
        transfer_out_id AS transfer_id, 
        transfer_date AS date,
        from_location AS source,
        to_location AS destination,
        total_quantity AS total_quantity,
        send_by AS responsible,
        'ขายออก' AS transaction_type
    FROM transfer_out_header
    WHERE transfer_out_id LIKE '%$search_query%' OR from_location LIKE '%$search_query%' OR to_location LIKE '%$search_query%'
    ORDER BY date DESC
";

$result = $conn->query($sql);
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกการรับและจ่ายสินค้า - ระบบจัดการคลังสินค้า</title>
    <link rel="stylesheet" href="css/08transaction_history.css">
    <link rel="stylesheet" href="css/layout.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body>
    <!-- ส่วนหัว -->
    <header>
        <h1>ระบบจัดการคลังสินค้า - ถุงเท้า</h1>
        <span class="logo-container">
            <img src="images/Logo Web App.png" alt="Logo" class="logo-img">
        </span>
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
    <div class="wrapper">
    <div class="transaction_area">
        <h2>บันทึกการรับและจ่ายสินค้า</h2>

        <!-- ตัวเลือกการกรองและค้นหา -->
        <div class="search-container my-3">
        <div class="d-flex justify-content-between align-items-center">
    
        <!-- ฟอร์มค้นหา -->
        <div class="input-group rounded search-bar">
            <form method="GET" class="form-inline">
                <div class="input-group rounded">
                    <input  type="search" name="search" class="form-control rounded" placeholder="ค้นหาด้วยเลขที่" aria-label="Search" value="<?php echo htmlspecialchars($search_query); ?>" />
                    <button type="submit" class="input-group-text border-0" id="search-addon">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
        <div class="d-flex lign-items-end gap-2"> 
                <a href="add_transfer_in_ver2.php" class="btn btn-success">รับสินค้าเข้า</a>

            </div>
        </div>
        </div>


        <!-- ตารางบันทึกการรับจ่าย -->
        <table class="custom-table">
            <thead>
                <tr>
                    <th>เลขที่</th>
                    <th>ประเภทกิจกรรม</th>
                    <th>วันที่-เวลา</th>
                    <th>ต้นทาง</th>
                    <th>ปลายทาง</th>
                    <th>ต้นทุนรวม</th>
                    <th>ผู้รับผิดชอบ</th>
                    <th>ดูรายละเอียด</th>
                    <th>แก้ไข</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['transfer_id']) . "</td>"; // ใช้ transfer_id ที่ดึงมาจาก SQL
                        echo "<td>" . htmlspecialchars($row['transaction_type']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['source']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['destination']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['total_quantity']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['responsible']) . "</td>";
                            
                        // ปุ่มดูรายละเอียด
                        echo "<td><a href='transfer_info_ver2.php?transfer_id=" . htmlspecialchars($row['transfer_id']) . "' class='btn btn-info'>ดูรายละเอียด</a></td>";
                            
                        // ปุ่มแก้ไข
                        echo "<td><a href='edit_transfer.php?transfer_id=" . htmlspecialchars($row['transfer_id']) . "' class='btn btn-warning'>แก้ไข</a></td>";
                            
                        // เงื่อนไขในการแสดงปุ่มลบเฉพาะกิจกรรมประเภท 'นำเข้า'
                        if ($row['transaction_type'] === 'นำเข้า') {
                            echo "<td>
                            <form action='delete_transfer.php' method='POST' onsubmit=\"return confirm('คุณแน่ใจที่จะลบรายการนี้?');\">
                            <input type='hidden' name='transfer_id' value='" . htmlspecialchars($row['transfer_id']) . "'>
                            <button type='submit' class='btn btn-danger'>ลบ</button>
                            </form>
                            </td>";
                        } else {
                            // กรณีที่ไม่ใช่ 'นำเข้า' ให้ช่องนี้เป็นค่าว่าง
                             echo "<td></td>";
                        }

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>ไม่มีข้อมูล</td></tr>";
                }
                ?>
            </tbody>

        </table>
    </div>
    </div>

    <!-- ส่วนท้าย -->
    <footer>
        <p>ระบบจัดการคลังสินค้า © 2024</p>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>
</html>