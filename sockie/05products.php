<?php
require_once '00conn.php';

$query = "SELECT * FROM products ";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // เก็บข้อมูลในตัวแปร $products เพื่อใช้แสดงในตาราง
    $products = $result->fetch_all(MYSQLI_ASSOC);
} else {
    echo "No products found.";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลสินค้า - ระบบจัดการคลังสินค้า</title>
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/05products.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- ส่วนหัว -->
    <header>
        <h1>ระบบจัดการคลังสินค้า - ถุงเท้า</h1>
        <span class="logo-container">
            <img src="images/Logo Web App.png" alt="Logo" class="logo-img">
        </span>
    </header>
    <!-- ส่วนหัว -->

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
    <div class="product_area">
        <div class="product_info">
            <h2>รายการสินค้า</h2>
            <br>

            <table class=" custom-table">
                <thead>
                    <tr>
                        <th>รหัสสินค้า</th>
                        <th>ชื่อสินค้า</th>
                        <th>ประเภท</th>
                        <th>สี</th>
                        <th>ขนาด</th>
                        <th>จำนวน</th>
                        <th>ความยาวถุงเท้า</th>
                        <th>ภาพสินค้า</th>
                        <th>แก้ไข</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($products)) : ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['product_id']); ?></td>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['type_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['color']); ?></td>
                                <td><?php echo htmlspecialchars($product['size']); ?></td>
                                <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($product['length_lv']); ?></td>
                                <?php
                                // ตรวจสอบว่า 'product_image' มีข้อมูลหรือไม่
                                if (!empty($product['product_image'])) {
                                    $imageData = base64_encode($product['product_image']);
                                    echo "<td><img src='data:image/jpeg;base64," . $imageData . "' alt='" . htmlspecialchars($product['product_name']) . "' class='product-image' style='width:100px;'></td>";
                                } else {
                                    echo "<td>ไม่มีรูปภาพ</td>";
                                }
                                ?>
                                <!-- แก้ไขข้อมูล -->
                                <td>
                                    <a href="edit_product.php?id=<?php echo urlencode($product['product_id']); ?>" class="btn btn-warning">แก้ไข</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="10">ไม่มีข้อมูล</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>ระบบจัดการคลังสินค้า © 2024</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>