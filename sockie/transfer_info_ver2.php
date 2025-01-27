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

// ตรวจสอบว่ามี transfer_in_id หรือไม่
if (isset($_GET['transfer_id'])) {
    $transfer_id = $_GET['transfer_id'];

    // ดึงข้อมูลจาก transfer_in_header
    $header_sql = "SELECT * FROM transfer_in_header WHERE transfer_in_id = ?";
    $stmt_header = $conn->prepare($header_sql);
    $stmt_header->bind_param("i", $transfer_id);
    $stmt_header->execute();
    $result_header = $stmt_header->get_result();

    // ดึงข้อมูลจาก transfer_in_items
    $items_sql = "SELECT * FROM transfer_in_items WHERE transfer_in_id = ?";
    $stmt_items = $conn->prepare($items_sql);
    $stmt_items->bind_param("i", $transfer_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();

    if ($result_header->num_rows > 0) {
        $header = $result_header->fetch_assoc();
    } else {
        echo "ไม่พบข้อมูล";
        exit;
    }
} else {
    echo "ไม่พบหมายเลขการรับสินค้า";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดการรับสินค้า</title>
    <link rel="stylesheet" href="css/transfer_info_ver2.css">
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
                <a class="nav-link" href="04main.php">หน้าหลัก</a>
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
                <a class="nav-link active" href="08transaction_history.php">บันทึกการรับและจ่ายสินค้า</a>
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

    <!-- ส่วนหลัก -->
    <div class="wrapper">
    <div class="transfer_info_area">
    <div class="transfer_info_header">
    <h1>รายละเอียดการรับสินค้า</h1>
            <div class="btn-close-container">
                <button type="button" class="btn-close" aria-label="Close" onclick="window.location.href='08transaction_history.php'"></button>
            </div>
    </div>        
    <br>
    <p><strong>เลขที่:</strong> <?php echo htmlspecialchars($header['transfer_in_id']); ?></p>
    <p><strong>วันที่-เวลา:</strong> <?php echo htmlspecialchars($header['transfer_date']); ?></p>
    <p><strong>ต้นทาง:</strong> <?php echo htmlspecialchars($header['from_location']); ?></p>
    <p><strong>ปลายทาง:</strong> <?php echo htmlspecialchars($header['to_location']); ?></p>
    <p><strong>ผู้รับผิดชอบ:</strong> <?php echo htmlspecialchars($header['received_by']); ?></p>
    <p><strong>ต้นทุนรวม:</strong> <?php echo htmlspecialchars($header['total_cost']); ?></p>
    <br>

    <h2>รายการสินค้า</h2>
    <br>
    <table class="custom-table">
        <thead>
            <tr>
                <th>รหัสสินค้า</th>
                <th>จำนวน</th>
                <th>ราคาต่อหน่วย</th>
                <th>ต้นทุนรวม</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($item = $result_items->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($item['product_id']) . "</td>";
                echo "<td>" . htmlspecialchars($item['quantity']) . "</td>";
                echo "<td>" . htmlspecialchars($item['unit_cost']) . "</td>";
                echo "<td>" . htmlspecialchars($item['total_cost']) . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    </div>
    </div>
</body>
</html>

