<?php
require_once '00conn.php';

// รับค่า product_id จาก URL
$product_id = $_GET['id'];

// ตรวจสอบว่าได้ product_id มาหรือไม่
if ($product_id) {
    // ดึงข้อมูลสินค้าจากตาราง products ตาม product_id
    $query = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // ตรวจสอบว่าพบสินค้าหรือไม่
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "ไม่พบสินค้า";
        exit;
    }
} else {
    echo "ไม่มีรหัสสินค้า";
    exit;
}

// เมื่อกดปุ่มบันทึกเพื่อแก้ไขข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $price = $_POST['price'];
    $unit_cost = $_POST['unit_cost'];
    $in_stock = $_POST['in_stock'];

    // อัปเดตข้อมูลสินค้าลงฐานข้อมูล
    $update_query = "UPDATE products SET price = ?, unit_cost = ?, in_stock = ? WHERE product_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('ddis', $price, $unit_cost, $in_stock, $product_id);

    if ($stmt->execute()) {
        echo "แก้ไขข้อมูลสำเร็จ";
        // เปลี่ยนหน้าไปยัง 06inventory_management.php หลังจากแก้ไขสำเร็จ
        header("Location: 06inventory_management.php");
        exit;
    } else {
        echo "เกิดข้อผิดพลาดในการแก้ไขข้อมูล";
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสินค้า</title>
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/edit_inven.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
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
                <a class="nav-link active" href="06inventory_management.php">จัดการสินค้าคงคลัง</a>
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
                <a class="nav-link" href="10reports_analytics.html">รายงานและการวิเคราะห์</a>
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
    <div class="edit_invenarea">
        <h1>แก้ไขข้อมูลสินค้า</h1>
        <button type="button" class="btn-close" aria-label="Close" onclick="window.location.href='06inventory_management.php'"></button>

        <!-- ฟอร์มแก้ไขข้อมูลสินค้า -->

        <div>
        <form method="POST">
        <label for="price">ราคาขาย/ชิ้น:</label>
        <input type="text" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required><br>

        <label for="unit_cost">ราคาต้นทุน:</label>
        <input type="text" name="unit_cost" value="<?php echo htmlspecialchars($product['unit_cost']); ?>" required><br>

        <label for="in_stock">จำนวนในสต็อก:</label>
        <input type="text" name="in_stock" value="<?php echo htmlspecialchars($product['in_stock']); ?>" required><br>

        <div class="button-group">
        <button type="submit" class="btn btn-success">บันทึกการแก้ไข</button>

        <button type="reset" class="btn btn-danger">ล้าง</button>
        </button>
        </div>
        
        <div>

    </form>

    </div>
    </div>
</body>
</html>
