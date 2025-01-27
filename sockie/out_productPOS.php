<?php
// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "sockie");

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึงข้อมูลสินค้าทั้งหมด
$sql = "SELECT product_id, product_name, price FROM products WHERE in_stock > 0";
$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// ตรวจสอบการส่งฟอร์มขายสินค้า
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // เก็บข้อมูลในตัวแปร
    $transfer_out_id = uniqid(); // หรือกำหนด ID ด้วยวิธีการที่เหมาะสม
    $total_amount = 0;

    // บันทึกข้อมูลการขายใน transfer_out_items
    foreach ($_POST['items'] as $item) {
        if ($item['quantity'] > 0) { // ตรวจสอบว่ามีการกรอกจำนวนที่ถูกต้อง
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $unit_price = $item['unit_price'];
            $total_price = $quantity * $unit_price;
            $total_amount += $total_price;

            // บันทึกลง transfer_out_items
            $sql = "INSERT INTO transfer_out_items (transfer_out_id, product_id, quantity, unit_price, total_price) VALUES ('$transfer_out_id', '$product_id', $quantity, $unit_price, $total_price)";
            $conn->query($sql);
        }
    }

    // บันทึกข้อมูลใน transfer_out_header
    $sql = "INSERT INTO transfer_out_header (transfer_out_id, transfer_date, total_quantity, total_cost, received_by) VALUES ('$transfer_out_id', NOW(), (SELECT COUNT(*) FROM transfer_out_items WHERE transfer_out_id='$transfer_out_id'), $total_amount, 'ผู้ขาย')";
    $conn->query($sql);

    echo "บันทึกการขายเสร็จเรียบร้อย!";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบขายสินค้า</title>
</head>
<body>
    <h1>ขายสินค้า</h1>
    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>เลือก</th>
                    <th>ชื่อสินค้า</th>
                    <th>ราคา</th>
                    <th>จำนวน</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <input type="checkbox" name="items[<?php echo $product['product_id']; ?>][selected]" value="1">
                    </td>
                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($product['price']); ?></td>
                    <td>
                        <input type="number" name="items[<?php echo $product['product_id']; ?>][quantity]" min="0" required>
                        <input type="hidden" name="items[<?php echo $product['product_id']; ?>][product_id]" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                        <input type="hidden" name="items[<?php echo $product['product_id']; ?>][unit_price]" value="<?php echo htmlspecialchars($product['price']); ?>">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit">บันทึกการขาย</button>
    </form>
</body>
</html>
