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

// ดึงข้อมูลประเภทสินค้าจากตาราง types
$query_types = "SELECT type_id, type_name FROM types";
$stmt_types = $conn->prepare($query_types);
$stmt_types->execute();
$result_types = $stmt_types->get_result();

$types = [];
if ($result_types->num_rows > 0) {
    $types = $result_types->fetch_all(MYSQLI_ASSOC);
}

// ดึงข้อมูลระดับความยาวจากตาราง length_level
$query_length_lv = "SELECT lv_id, lv_name FROM length_level";
$stmt_length_lv = $conn->prepare($query_length_lv);
$stmt_length_lv->execute();
$result_length_lv = $stmt_length_lv->get_result();

$length_levels = [];
if ($result_length_lv->num_rows > 0) {
    $length_levels = $result_length_lv->fetch_all(MYSQLI_ASSOC);
}

// เมื่อกดปุ่มบันทึกเพื่อแก้ไขข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $type_name = $_POST['type_name'];
    $color = $_POST['color'];
    $size = $_POST['size'];
    $quantity = $_POST['quantity'];
    $length_lv = $_POST['length_lv'];
    $product_image = $_POST['product_image'];
    
    // ตรวจสอบการอัปโหลดไฟล์ใหม่
    if (!empty($_FILES['product_image']['tmp_name'])) {
        // ถ้ามีการอัปโหลดไฟล์ใหม่
        $product_image = file_get_contents($_FILES['product_image']['tmp_name']);
    } else {
        // ถ้าไม่มีการอัปโหลดไฟล์ใหม่ ให้ใช้รูปภาพเดิม
        $product_image = $product['product_image'];
    }

    // อัปเดตข้อมูลสินค้าลงฐานข้อมูล
    $update_query = "UPDATE products 
    SET product_name = ?,type_name = ?,color = ?, size = ?,quantity = ?,length_lv = ? ,product_image = ? WHERE product_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('ssssssss',$product_name,$type_name,$color,$size,$quantity,$length_lv ,$product_image ,$product_id);

    

    if ($stmt->execute()) {
        echo "แก้ไขข้อมูลสำเร็จ";
        // เปลี่ยนหน้าไปยัง 06inventory_management.php หลังจากแก้ไขสำเร็จ
        header("Location: 05products.php");
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
    <link rel="stylesheet" href="css/edit_product.css">
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
                <a class="nav-link " href="04main.php">หน้าหลัก</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="05products.php">ข้อมูลสินค้า</a>
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
        <div class="edit_productarea">
        <div class="edit_producthead">
            <h1>แก้ไขข้อมูลสินค้า</h1>
            <div class="btn-close-container">
                <button type="button" class="btn-close" aria-label="Close" onclick="window.location.href='05products.php'"></button>
            </div>
        </div>
    <!-- ฟอร์มแก้ไขข้อมูล -->
    
    <div class="edit_productform">
    <form method="POST" enctype="multipart/form-data">

        <div class="form-row">
            <div class="form-group">
                <label for="price">ชื่อสินค้า:</label>
                <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required><br>
            </div>

            <div class="form-group">
            <label for="type_name">ประเภท:</label>
            <select name="type_name" required>
                <?php foreach ($types as $type): ?>
                    <option value="<?php echo htmlspecialchars($type['type_name']); ?>" 
                        <?php echo ($type['type_name'] == $product['type_name']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['type_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br>
        </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="color">สี:</label>
                <input type="text" name="color" value="<?php echo htmlspecialchars($product['color']); ?>" required><br>
            </div>

            <div class="form-group">
                <label for="size">ขนาด:</label>
                <input type="text" name="size" value="<?php echo htmlspecialchars($product['size']); ?>" required><br>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="quantity">จำนวน:</label>
                <input type="text" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required><br>
            </div>

            <div class="form-group">
            <label for="length_lv">ความยาวถุงเท้า:</label>
            <select name="length_lv" required>
                <?php foreach ($length_levels as $length_lv): ?>
                    <option value="<?php echo htmlspecialchars($length_lv['lv_name']); ?>" 
                        <?php echo ($length_lv['lv_name'] == $product['length_lv']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($length_lv['lv_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br>
        </div>
        </div>

            <div class="form-group">
            <div class="form-group image-upload">
                <label for="product_image">อัปโหลดภาพสินค้า:</label>
                <input type="file" name="product_image"><br>
                <div class="image-preview">
                    <?php 
                    echo '<img src="data:image/jpeg;base64,' . base64_encode($product['product_image']).'"/>';
                    ?>
                <div>
            </div>
            </div>


            <div class="button-group">
                <button type="submit" class="btn btn-success">บันทึกการแก้ไข</button>
                <button onclick="window.location.href='05product.php';" class="btn btn-danger">ยกเลิก</button>
            </div>
    </form>
    </div>


</div>
</div>
    <!-- ส่วนท้าย -->
    <footer>
        <p>ระบบจัดการคลังสินค้า © 2024</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
    crossorigin="anonymous"></script> 
</body>
</html>
