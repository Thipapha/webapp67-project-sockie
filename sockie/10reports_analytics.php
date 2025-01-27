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

// ดึงข้อมูลยอดขายรายเดือนสำหรับตารางและกราฟ
$sales_sql = "
    SELECT 
        MONTH(transfer_date) AS month,
        YEAR(transfer_date) AS year,
        SUM(total_price) AS total_sales
    FROM 
        transfer_out_header
    GROUP BY 
        YEAR(transfer_date), 
        MONTH(transfer_date)
    ORDER BY 
        year, month
";

$sales_result = $conn->query($sales_sql);

// เตรียมข้อมูลสำหรับกราฟเทรนด์การขาย
$sales_labels = [];
$sales_data = [];
$sales_table_rows = "";

$thai_month = [
    1 => 'มกราคม',
    2 => 'กุมภาพันธ์',
    3 => 'มีนาคม',
    4 => 'เมษายน',
    5 => 'พฤษภาคม',
    6 => 'มิถุนายน',
    7 => 'กรกฎาคม',
    8 => 'สิงหาคม',
    9 => 'กันยายน',
    10 => 'ตุลาคม',
    11 => 'พฤศจิกายน',
    12 => 'ธันวาคม'
];

if ($sales_result->num_rows > 0) {
    while ($row = $sales_result->fetch_assoc()) {
        $month = $row['month'];
        $year = $row['year'];
        $total_sales = $row['total_sales'];
        
        $month_name = isset($thai_month[$month]) ? $thai_month[$month] : 'ไม่ทราบเดือน';
        $sales_labels[] = $month_name . " " . $year;
        $sales_data[] = $total_sales;
        
        // เพิ่มแถวข้อมูลในตารางยอดขาย
        $sales_table_rows .= "<tr>";
        $sales_table_rows .= "<td>" . htmlspecialchars($month_name) . " " . htmlspecialchars($year) . "</td>";
        $sales_table_rows .= "<td>" . number_format($total_sales, 2) . " ฿</td>";
        $sales_table_rows .= "</tr>";
    }
} else {
    $sales_table_rows = "<tr><td colspan='2'>ไม่พบข้อมูล</td></tr>";
}

// ดึงข้อมูลสินค้าคงคลัง
$inventory_sql = "
    SELECT 
        t.type_name, 
        SUM(oi.quantity) AS total_quantity_sold, 
        SUM(oi.total_price) AS total_sales_value
    FROM 
        transfer_out_items oi
    JOIN 
        products p ON oi.product_id = p.product_id
    JOIN 
        types t ON p.type_name = t.type_name
    GROUP BY 
        t.type_name;
";

$inventory_result = $conn->query($inventory_sql);

// เตรียมข้อมูลสำหรับตารางสินค้าคงคลัง
$inventory_table_rows = "";

if ($inventory_result->num_rows > 0) {
    while ($row = $inventory_result->fetch_assoc()) {
        $type_name = htmlspecialchars($row['type_name']);
        $total_quantity_sold = htmlspecialchars($row['total_quantity_sold']);
        $total_sales_value = number_format($row['total_sales_value'], 2);
        
        $inventory_table_rows .= "<tr>";
        $inventory_table_rows .= "<td>" . $type_name . "</td>";
        $inventory_table_rows .= "<td>" . $total_quantity_sold . " รายการ</td>";
        $inventory_table_rows .= "<td>" . $total_sales_value . " ฿</td>";
        $inventory_table_rows .= "</tr>";
    }
} else {
    $inventory_table_rows = "<tr><td colspan='3'>ไม่พบข้อมูล</td></tr>";
}

// ดึงข้อมูลสินค้าที่ขายดีที่สุด
$best_sql = "
    SELECT 
        p.product_name,
        SUM(t.quantity) AS total_quantity
    FROM 
        transfer_out_items t
    JOIN 
        products p ON t.product_id = p.product_id
    GROUP BY 
        t.product_id
    ORDER BY 
        total_quantity DESC
    LIMIT 5
";

$best_result = $conn->query($best_sql);

$best_products = [];
$best_labels = [];
$best_data = [];

if ($best_result->num_rows > 0) {
    while ($row = $best_result->fetch_assoc()) {
        $best_labels[] = $row['product_name'];
        $best_data[] = $row['total_quantity'];
    }
}

// ดึงข้อมูลสินค้าที่ขายแย่ที่สุด
$worst_sql = "
    SELECT 
        p.product_name,
        SUM(t.quantity) AS total_quantity
    FROM 
        transfer_out_items t
    JOIN 
        products p ON t.product_id = p.product_id
    GROUP BY 
        t.product_id
    ORDER BY 
        total_quantity ASC
    LIMIT 5
";

$worst_result = $conn->query($worst_sql);

$worst_products = [];
$worst_labels = [];
$worst_data = [];

if ($worst_result->num_rows > 0) {
    while ($row = $worst_result->fetch_assoc()) {
        $worst_labels[] = $row['product_name'];
        $worst_data[] = $row['total_quantity'];
    }
}

// เตรียมข้อมูลสำหรับกราฟสินค้าที่ขายดีที่สุดและแย่ที่สุด
$combined_labels = array_merge($best_labels, $worst_labels);
$combined_data = array_merge($best_data, $worst_data);
$combined_colors = array_merge(
    array_fill(0, count($best_labels), 'rgba(75, 192, 192, 0.6)'), 
    array_fill(0, count($worst_labels), 'rgba(255, 99, 132, 0.6)')
);
$combined_border_colors = array_merge(
    array_fill(0, count($best_labels), 'rgba(75, 192, 192, 1)'), 
    array_fill(0, count($worst_labels), 'rgba(255, 99, 132, 1)')
);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานและการวิเคราะห์ - ระบบจัดการคลังสินค้า</title>
    <link rel="stylesheet" href="css/10reports_analytics.css">
    <link rel="stylesheet" href="css/layout.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- เชื่อมต่อกับ Chart.js ผ่าน CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a class="nav-link " href="04main.php">หน้าหลัก</a>
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
                <a class="nav-link active" href="10reports_analytics.php">รายงานและการวิเคราะห์</a>
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
     
    <div class="wrapper">
    <div class="report_area">
        <div class="header-wrapper d-flex justify-content-between align-items-center">
            <h2>รายงานยอดขายรายเดือน</h2>
            <div class="button-wrapper">
                <button onclick="printReport()" class="btn btn-primary print-button">พิมพ์รายงาน</button>
        </div>
    </div>
    <br><br>
        <canvas id="salesChart"></canvas>

        <table class="table table-striped mt-4">
            <thead>
                <tr>
                    <th>เดือน</th>
                    <th>ยอดขายรวม</th>
                </tr>
            </thead>
            <tbody>
                <?= $sales_table_rows; ?>
            </tbody>
        </table>

        <h2 class="mt-4">สินค้าคงคลัง</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ประเภทสินค้า</th>
                    <th>จำนวนที่ขายได้</th>
                    <th>มูลค่ารวมการขาย</th>
                </tr>
            </thead>
            <tbody>
                <?= $inventory_table_rows; ?>
            </tbody>
        </table>

        <h2 class="mt-4">สินค้าที่ขายดีที่สุด</h2>
        <canvas id="bestProductsChart"></canvas>

        <h2 class="mt-4">สินค้าที่ขายแย่ที่สุด</h2>
        <canvas id="worstProductsChart"></canvas>
    </div>
</div>

    <script>
            // ฟังก์ชันสำหรับพิมพ์รายงาน
            function printReport() {
            window.print(); // เรียกใช้งานฟังก์ชันพิมพ์ของเบราว์เซอร์
        }

        // กราฟยอดขายรายเดือน
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($sales_labels); ?>,
                datasets: [{
                    label: 'ยอดขายรวม (฿)',
                    data: <?= json_encode($sales_data); ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 1,
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // กราฟสินค้าที่ขายดีที่สุด
        const bestProductsCtx = document.getElementById('bestProductsChart').getContext('2d');
        const bestProductsChart = new Chart(bestProductsCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($best_labels); ?>,
                datasets: [{
                    label: 'จำนวนที่ขายได้',
                    data: <?= json_encode($best_data); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // กราฟสินค้าที่ขายแย่ที่สุด
        const worstProductsCtx = document.getElementById('worstProductsChart').getContext('2d');
        const worstProductsChart = new Chart(worstProductsCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($worst_labels); ?>,
                datasets: [{
                    label: 'จำนวนที่ขายได้',
                    data: <?= json_encode($worst_data); ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

     <!-- ส่วนล่าง -->
     <footer>
        <p>ระบบจัดการคลังสินค้า © 2024</p>
    </footer>
</body>
</html>

<?php
// ปิดการเชื่อมต่อ
$conn->close();
?>
