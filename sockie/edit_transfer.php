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

// ตรวจสอบว่าได้รับ transfer_id จาก URL
if (isset($_GET['transfer_id'])) {
    $transfer_id = $_GET['transfer_id'];

    // Query เพื่อดึงข้อมูลจากสองตาราง
    $sql = "SELECT transfer_in_id AS transfer_id, transfer_date, from_location, to_location, received_by AS person_responsible
            FROM transfer_in_header WHERE transfer_in_id = ?
            UNION 
            SELECT transfer_out_id AS transfer_id, transfer_date, from_location, to_location, send_by AS person_responsible
            FROM transfer_out_header WHERE transfer_out_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $transfer_id, $transfer_id);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // ดึงข้อมูลจากคอลัมน์
        $transfer_date = $row['transfer_date'];
        $from_location = $row['from_location'];
        $to_location = $row['to_location'];
        $person_responsible = $row['person_responsible'];
    } else {
        echo "ไม่พบข้อมูล";
        exit;
    }
} else {
    echo "ไม่มี transfer_id";
    exit;
}

$success = false; // สถานะสำหรับการแจ้งเตือน

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับข้อมูลจากฟอร์ม
    $transfer_date = $_POST['transfer_date'];
    $from_location = $_POST['from_location'];
    $to_location = $_POST['to_location'];
    $person_responsible = $_POST['person_responsible'];

    // อัปเดตข้อมูลใน transfer_in_header
    $sql_in = "UPDATE transfer_in_header SET transfer_date = ?, from_location = ?, to_location = ?, received_by = ? WHERE transfer_in_id = ?";
    $stmt_in = $conn->prepare($sql_in);
    $stmt_in->bind_param("sssss", $transfer_date, $from_location, $to_location, $person_responsible, $transfer_id);

    // อัปเดตข้อมูลใน transfer_out_header
    $sql_out = "UPDATE transfer_out_header SET transfer_date = ?, from_location = ?, to_location = ?, send_by = ? WHERE transfer_out_id = ?";
    $stmt_out = $conn->prepare($sql_out);
    $stmt_out->bind_param("sssss", $transfer_date, $from_location, $to_location, $person_responsible, $transfer_id);

    // ตรวจสอบการอัปเดต
    if ($stmt_in->execute() || $stmt_out->execute()) {
        $success = true;
        // รีไดเรกต์ไปที่หน้า transaction history
        header("Location: 08transaction_history.php");
        exit();
    } else {
        echo "เกิดข้อผิดพลาด: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/edit_transfer.css">
    <link rel="stylesheet" href="css/layout.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>แก้ไขการรับ/จ่ายสินค้า</title>
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
    <main class="edit_tranfer_area">

        <h2>ฟอร์มแก้ไขการรับ/จ่ายสินค้า</h2>

        <!-- ฟอร์ม -->
        <form method="POST">
            <div class="form-group">
                <label for="transfer_date">วันที่-เวลา:</label>
                <input type="datetime-local" name="transfer_date"
                    value="<?php echo htmlspecialchars($transfer_date); ?>" required>
            </div>

            <div class="form-group">
                <label for="from_location">ต้นทาง:</label>
                <input type="text" name="from_location"
                    value="<?php echo htmlspecialchars($from_location); ?>" required>
            </div>

            <div class="form-group">
                <label for="to_location">ปลายทาง:</label>
                <input type="text" name="to_location"
                    value="<?php echo htmlspecialchars($to_location); ?>" required>
            </div>

            <div class="form-group">
                <label for="person_responsible">ผู้รับผิดชอบ:</label>
                <input type="text" name="person_responsible"
                    value="<?php echo htmlspecialchars($person_responsible); ?>" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <button type="reset" class="btn btn-danger">ล้าง</button>
                <button type="button" class="btn btn-secondary"
                    onclick="window.location.href='08transaction_history.php'">ยกเลิก</button>
            </div>
        </form>
    </main>

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