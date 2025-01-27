<?php
require_once '00conn.php';

// ดึงข้อมูลประเภทสินค้าจากตาราง types
$query_types = "SELECT type_id, type_name FROM types";
$stmt_types = $conn->prepare($query_types);
$stmt_types->execute();
$result_types = $stmt_types->get_result();

$typess = [];
if ($result_types->num_rows > 0) {
    $typess = $result_types->fetch_all(MYSQLI_ASSOC);
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


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $type_name = $_POST['type_name'];
    $color = $_POST['color'];
    $size = $_POST['size'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $length_lv = $_POST['length_lv'];
    $in_stock = $_POST['in_stock'];
    $unit_cost = $_POST['unit_cost'];

    // ตรวจสอบการอัปโหลดไฟล์ใหม่
    if (!empty($_FILES['product_image']['tmp_name'])) {
        // ถ้ามีการอัปโหลดไฟล์ใหม่
        $product_image = file_get_contents($_FILES['product_image']['tmp_name']);
    } else {
        // ถ้าไม่มีการอัปโหลดไฟล์ใหม่ ให้ใช้รูปภาพเดิม
        $product_image = $product['product_image'];
    }
   

    // เตรียมคำสั่ง SQL สำหรับการเพิ่มข้อมูลใหม่
    $query = "INSERT INTO products (product_id, product_name, type_name, color, size, quantity, price, length_lv, product_image, in_stock, unit_cost) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssssissii', $product_id, $product_name, $type_name, $color, $size, $quantity, $price, $length_lv, $product_image, $in_stock, $unit_cost);

    // ตรวจสอบว่าการบันทึกข้อมูลสำเร็จหรือไม่
    if ($stmt->execute()) {
        echo "บันทึกข้อมูลสำเร็จ";
    } else {
        echo "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $conn->error;
    }

    $stmt->close();
}


$stmt_types->close();
$stmt_length_lv->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสินค้า - ระบบจัดการคลังสินค้า</title>
    <link rel="stylesheet" href="css/07add_product.css">
    <link rel="stylesheet" href="css/layout.css">
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
        <div class="add-edit-product">
            <h2>เพิ่มสินค้า</h2>
            
            
            <!-- ฟอร์ม -->
            <form method="post" id="productForm" enctype="multipart/form-data" >
                <div class="form-group">
                    <label for="product_id">รหัสสินค้า:</label>
                    <input type="text" id="product_id" name="product_id" required>
                </div>

                <div class="form-group">
                    <label for="product_name">ชื่อสินค้า:</label>
                    <input type="text" id="product_name" name="product_name" required>
                </div>


                <div class="form-group">
                    <label for="type">ประเภท:</label>
                    <select id="type" name="type_name" required>
                        <option value="">เลือกประเภท</option>
                        <?php foreach ($typess as $type): ?>
                            <option value="<?php echo htmlspecialchars($type['type_name']); ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                
                <div class="form-group">
                    <label for="color">สี:</label>
                    <input type="text" id="color" name="color" required>
                </div>
                
                <div class="form-group">
                    <label for="size">ขนาด:</label>
                    <input type="text" id="size" name="size" required>
                </div>

                <div class="form-group">
                    <label for="quantity">จำนวน:</label>
                    <input type="text" id="quantity" name="quantity" required>
                </div>
                
                <div class="form-group">
                    <label for="price">ราคาขาย:</label>
                    <input type="number" id="price" name="price" min="0" required>
                </div>

                <div class="form-group">
                    <label for="length_level">ระดับความยาว:</label>
                    <select id="length_level" name="length_lv" required>
                        <option value="">เลือกระดับความยาว</option>
                        <?php foreach ($length_levels as $level): ?>
                            <option value="<?php echo htmlspecialchars($level['lv_name']); ?>"><?php echo htmlspecialchars($level['lv_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                <label for="product_image">อัปโหลดภาพสินค้า:</label>
                <input type="file" name="product_image" required><br>
                    
                </div>

                <div class="form-group">
                    <label for="in_stock">คงเหลือในสต็อก:</label>
                    <input type="number" id="in_stock" name="in_stock" required>
                </div>

                <div class="form-group">
                    <label for="unit_cost">ต้นทุนต่อหน่วย:</label>
                    <input type="number" id="unit_cost" name="unit_cost" step="0.01" min="0" required>
                </div>
                
                <!-- ปุ่มกด -->
                <div class="form-actions">
                <button type="submit" class="btn btn-success">บันทึก</button>
                <a><button type="reset" class="btn btn-danger">ยกเลิก</button></a>
                </div>
            </form>
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

