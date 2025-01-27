<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "sockie"; 

// เปิดการรายงานข้อผิดพลาดของ MySQLi เพื่อการดีบักที่ดีขึ้น
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตั้งค่าการเข้ารหัสเป็น UTF-8 เพื่อป้องกันปัญหาเรื่องอักขระ
$conn->set_charset("utf8");

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบว่ามีการส่งฟอร์มหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์มและทำการ sanitize
    $transfer_in_id = trim($_POST['transfer_in_id']);
    $transfer_date = $_POST['transfer_date'];
    $from_location = trim($_POST['from_location']);
    $to_location = trim($_POST['to_location']);
    $received_by = trim($_POST['received_by']);
    
    // Initialize total_cost และ total_quantity
    $total_cost = 0;
    $total_quantity = 0;

    // Array เพื่อเก็บรายละเอียดสินค้าที่จะเพิ่ม
    $items = [];

    // ตรวจสอบสินค้าที่เพิ่มเข้ามา
    if (isset($_POST['product_id']) && is_array($_POST['product_id'])) {
        foreach ($_POST['product_id'] as $index => $product_id) {
            $product_id = trim($_POST['product_id'][$index]);
            $quantity = intval($_POST['quantity'][$index]);
            $unit_cost = floatval($_POST['unit_cost'][$index]);

            if ($product_id === "" || $quantity <= 0) {
                continue; // ข้ามสินค้าที่ไม่ถูกต้อง
            }

            // คำนวณต้นทุนรวมสำหรับสินค้านี้
            $item_total_cost = $unit_cost * $quantity;

            // รวมค่าใช้จ่ายและจำนวน
            $total_cost += $item_total_cost;
            $total_quantity += $quantity;

            // เก็บรายละเอียดสินค้า
            $items[] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'unit_cost' => $unit_cost,
                'total_cost' => $item_total_cost
            ];
        }
    }

    // ตรวจสอบว่ามีสินค้าถูกต้องเพียงพอที่จะบันทึก
    if (empty($items)) {
        echo "ไม่มีข้อมูลสินค้าเพื่อบันทึก.";
        exit;
    }

    // เริ่ม Transaction เพื่อให้มั่นใจว่าการบันทึกทั้งหมดจะสำเร็จหรือไม่สำเร็จเลย
    $conn->begin_transaction();

    try {
        // บันทึกข้อมูลใน transfer_in_header เพียงครั้งเดียว
        $header_sql = "INSERT INTO transfer_in_header (transfer_in_id, transfer_date, from_location, to_location, received_by, total_cost, total_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_header = $conn->prepare($header_sql);
        if (!$stmt_header) {
            throw new Exception("Preparation failed for header: " . $conn->error);
        }
        // ตรวจสอบชนิดข้อมูลให้ถูกต้อง
        // transfer_in_id: string (s)
        // transfer_date: string (s)
        // from_location: string (s)
        // to_location: string (s)
        // received_by: string (s)
        // total_cost: double (d)
        // total_quantity: integer (i)
        $stmt_header->bind_param("issssdi", $transfer_in_id, $transfer_date, $from_location, $to_location, $received_by, $total_cost, $total_quantity);
        $stmt_header->execute();
        $stmt_header->close();

        // เตรียม statement สำหรับ transfer_in_items
        $items_sql = "INSERT INTO transfer_in_items (transfer_in_id, product_id, quantity, unit_cost, total_cost) VALUES (?, ?, ?, ?, ?)";
        $stmt_item = $conn->prepare($items_sql);
        if (!$stmt_item) {
            throw new Exception("Preparation failed for items: " . $conn->error);
        }

        // เตรียม statement สำหรับ updating in_stock
        $update_stock_sql = "UPDATE products SET in_stock = in_stock + ? WHERE product_id = ?";
        $stmt_update_stock = $conn->prepare($update_stock_sql);
        if (!$stmt_update_stock) {
            throw new Exception("Preparation failed for updating stock: " . $conn->error);
        }

        foreach ($items as $item) {
            // ตรวจสอบว่า product_id มีอยู่ใน products หรือไม่
            $check_sql = "SELECT 1 FROM products WHERE product_id = ?";
            $stmt_check = $conn->prepare($check_sql);
            if (!$stmt_check) {
                throw new Exception("Preparation failed for check: " . $conn->error);
            }
            $stmt_check->bind_param("s", $item['product_id']);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows == 0) {
                throw new Exception("ไม่มี product_id นี้ในตาราง products: " . htmlspecialchars($item['product_id']));
            }
            $stmt_check->close();

            // Debugging: แสดงข้อมูลที่กำลังจะถูกเพิ่ม
            // echo "Inserting transfer_in_id: " . htmlspecialchars($transfer_in_id) . "<br>";
            // echo "Inserting product_id: " . htmlspecialchars($item['product_id']) . ", Quantity: " . htmlspecialchars($item['quantity']) . ", Unit Cost: " . htmlspecialchars($item['unit_cost']) . ", Total Cost: " . htmlspecialchars($item['total_cost']) . "<br>";

            // Insert into transfer_in_items
            $stmt_item->bind_param("isidd", $transfer_in_id, $item['product_id'], $item['quantity'], $item['unit_cost'], $item['total_cost']);
            $stmt_item->execute();

            // Update in_stock in products
            $stmt_update_stock->bind_param("is", $item['quantity'], $item['product_id']);
            $stmt_update_stock->execute();
        }

        // ปิด prepared statements
        $stmt_item->close();
        $stmt_update_stock->close();

        // ยืนยันการทำงานทั้งหมด
        $conn->commit();

        // ปิดการเชื่อมต่อ
        $conn->close();

        // รีเซ็ตฟอร์มและแสดงข้อความสำเร็จ
        header("Location: add_transfer_in.php?success=1");
        exit;
    } catch (Exception $e) {
        // Rollback ในกรณีมีปัญหา
        $conn->rollback();
        echo "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage();
    }
}

// รับข้อมูลสินค้าจากฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");
$products_sql = "SELECT product_id, unit_cost FROM products";
$products_result = $conn->query($products_sql);
$products = [];
if ($products_result->num_rows > 0) {
    while ($row = $products_result->fetch_assoc()) {
        $products[] = $row;
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รับสินค้าเข้าสู่ระบบ</title>
    <link rel="stylesheet" href="css/add_transfer_in_ver2.css">
    <link rel="stylesheet" href="css/layout.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
    <br>

    <div class="wrapper">

        <div class="add_tranfer_in_area">
        <div class="addtransfer_info_header">
        <h1>รับสินค้าเข้าสู่ระบบ</h1>
            <div class="btn-close-container">
                <button type="button" class="btn-close" aria-label="Close" onclick="window.location.href='08transaction_history.php'"></button>
            </div>
        </div>  

        <form method="post">
            <div class="mb-3">
                <label for="transfer_in_id" class="form-label">เลขที่</label>
                <input type="text" class="form-control" id="transfer_in_id" name="transfer_in_id" required>
            </div>
            <div class="mb-3">
                <label for="transfer_date" class="form-label">วันที่-เวลา</label>
                <input type="datetime-local" class="form-control" id="transfer_date" name="transfer_date" required>
            </div>
            <div class="mb-3">
                <label for="from_location" class="form-label">ต้นทาง</label>
                <input type="text" class="form-control" id="from_location" name="from_location" required>
            </div>
            <div class="mb-3">
                <label for="to_location" class="form-label">ปลายทาง</label>
                <input type="text" class="form-control" id="to_location" name="to_location" required>
            </div>
            <div class="mb-3">
                <label for="received_by" class="form-label">ผู้รับ</label>
                <input type="text" class="form-control" id="received_by" name="received_by" required>
            </div>

            <h3>รายละเอียดสินค้า</h3>
            <table class="table" id="productTable">
                <thead>
                    <tr>
                        <th>รหัสสินค้า</th>
                        <th>จำนวน</th>
                        <th>ต้นทุนต่อหน่วย</th>
                        <th>ต้นทุนรวม</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="product_id[]" class="form-control" onchange="updateUnitCost(this, 0)" required>
                                <option value="">รหัสสินค้า</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['product_id']; ?>" data-cost="<?= $product['unit_cost']; ?>">
                                        <?= $product['product_id']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="quantity[]" class="form-control" data-index="0" oninput="calculateTotalCost(0)" required></td>
                        <td><input type="number" id="unit_cost_0" name="unit_cost[]" step="0.01" class="form-control" readonly></td>
                        <td><input type="number" id="total_cost_0" name="total_cost[]" step="0.01" class="form-control" readonly></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-primary" onclick="addRow()">เพิ่มแถว</button>
            <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
        </form>
        </div>
    </div>

    <script>
        function updateUnitCost(selectElement, index) {
            const unitCost = selectElement.options[selectElement.selectedIndex].getAttribute('data-cost');
            document.querySelector(`#unit_cost_${index}`).value = unitCost;
            calculateTotalCost(index);
        }

        function calculateTotalCost(index) {
            const quantity = document.querySelector(`[name="quantity[]"][data-index="${index}"]`).value;
            const unitCost = document.querySelector(`#unit_cost_${index}`).value;

            if (quantity && unitCost) {
                const totalCost = quantity * unitCost;
                document.querySelector(`#total_cost_${index}`).value = totalCost.toFixed(2);
            } else {
                document.querySelector(`#total_cost_${index}`).value = 0;
            }
        }

        function addRow() {
            var table = document.getElementById('productTable');
            var rowCount = table.rows.length;
            var row = table.insertRow(rowCount);

            var cell1 = row.insertCell(0);
            var cell2 = row.insertCell(1);
            var cell3 = row.insertCell(2);
            var cell4 = row.insertCell(3);

            cell1.innerHTML = `
            <select name="product_id[]" class="form-control" onchange="updateUnitCost(this, ${rowCount})" required>
                <option value="">รหัสสินค้า</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= $product['product_id']; ?>" data-cost="<?= $product['unit_cost']; ?>">
                        <?= $product['product_id']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            `;

            cell2.innerHTML = `<input type="number" name="quantity[]" class="form-control" data-index="${rowCount}" oninput="calculateTotalCost(${rowCount})" required>`;
            cell3.innerHTML = `<input type="number" id="unit_cost_${rowCount}" name="unit_cost[]" step="0.01" class="form-control" readonly>`;
            cell4.innerHTML = `<input type="number" id="total_cost_${rowCount}" name="total_cost[]" step="0.01" class="form-control" readonly>`;
        }
    </script>
</body>
</html>
