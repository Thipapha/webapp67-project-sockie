<?php
require_once '00conn.php';

// ดึง product_id ทั้งหมดจากฐานข้อมูล
$query = "SELECT product_id FROM products";
$stmt = $conn->prepare($query);
$stmt->execute();
$product_ids = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// รับค่า product_id ที่ถูกเลือกจากการค้นหา
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT * FROM products";
if (!empty($search)) {
    // กรองสินค้าที่มีรหัสตรงกับการเลือก
    $query .= " WHERE product_id = ?";
}

$stmt = $conn->prepare($query);

if (!empty($search)) {
    $stmt->bind_param("s", $search);
}

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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสินค้าคงคลัง - ระบบจัดการคลังสินค้าocument</title>
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/06.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

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
        <div class="stock_area">
            <h2>จัดการสินค้าคงคลัง</h2>

            <div class="search-container my-3">
                <form method="GET" class="form-inline">
                    <div class="input-group rounded search-bar">
                        <select name="search" class="form-control rounded">
                            <option value="">เลือกสินค้าตามรหัส</option>
                            <?php foreach ($product_ids as $product): ?>
                                <option value="<?php echo htmlspecialchars($product['product_id']); ?>" <?php echo (isset($_GET['search']) && $_GET['search'] === $product['product_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($product['product_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="input-group-text border-0" id="search-addon">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>



            <!-- ตารางสถานะสต็อก -->
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>รหัสสินค้า</th>
                        <th>ชื่อสินค้า</th>
                        <th>ราคาขาย/ชิ้น</th>
                        <th>ราคาต้นทุน</th>
                        <th>จำนวนในสต็อก</th>
                        <th>สถานะ</th>
                        <th>แก้ไข</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (isset($products)) : ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['product_id']); ?></td>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['price']); ?></td>
                                <td><?php echo htmlspecialchars($product['unit_cost']); ?></td>
                                <td><?php echo htmlspecialchars($product['in_stock']); ?></td>
                                <!-- แสดงสถานะสินค้า -->
                                <td>
                                    <?php
                                    $in_stock = intval($product['in_stock']);
                                    if ($in_stock == 0) {
                                        echo '<span class="badge bg-danger">หมดสต็อก</span>';
                                    } elseif ($in_stock > 0 && $in_stock < 30) {
                                        echo '<span class="badge bg-warning text-dark">ใกล้หมดสต็อก</span>';
                                    } else {
                                        echo '<span class="badge bg-success">มีในสต็อก</span>';
                                    }
                                    ?>
                                </td>

                                <!-- แก้ไขข้อมูล -->

                                <td>
                                    <a href="edit_inventory.php?id=<?php echo urlencode($product['product_id']); ?>" class="btn btn-warning">แก้ไข</a>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8">ไม่มีข้อมูล</td>
                        </tr>
                    <?php endif; ?>

                </tbody>

            </table>

        </div>
    </div>

    <footer>
        <p>ระบบจัดการคลังสินค้า © 2024</p>
    </footer>

    <!-- JavaScript สำหรับการกรองและค้นหา (ตัวเลือกเพิ่มเติม) -->
    <script>
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const tableRows = document.querySelectorAll('table tbody tr');

        // ฟังก์ชันการกรองตาราง
        function filterTable() {
            const searchValue = searchInput.value.toLowerCase();
            const categoryValue = categoryFilter.value;

            tableRows.forEach(row => {
                const productId = row.cells[0].textContent.toLowerCase();
                const productName = row.cells[1].textContent.toLowerCase();
                const category = row.cells[2].textContent.toLowerCase();

                const matchesSearch = productId.includes(searchValue) || productName.includes(searchValue);
                const matchesCategory = categoryValue === "" || category === categoryValue.toLowerCase();

                if (matchesSearch && matchesCategory) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // ดักจับเหตุการณ์การพิมพ์ในช่องค้นหา
        searchInput.addEventListener('input', filterTable);

        // ดักจับเหตุการณ์การเลือกประเภท
        categoryFilter.addEventListener('change', filterTable);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>