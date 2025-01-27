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

// ตรวจสอบว่ามีการส่งค่ามาหรือไม่
if (isset($_POST['transfer_id']) && isset($_POST['transfer_type'])) {
    $transfer_id = $_POST['transfer_id'];
    $transfer_type = $_POST['transfer_type']; // 'in' หรือ 'out'

    // เริ่ม Transaction
    $conn->begin_transaction();

    try {
        if ($transfer_type === 'in') {
            // 1. ลบจาก transfer_in_items
            $sql_items = "DELETE FROM transfer_in_items WHERE transfer_in_id = ?";
            $stmt_items = $conn->prepare($sql_items);
            if (!$stmt_items) {
                throw new Exception("Error preparing transfer_in_items delete statement: " . $conn->error);
            }
            $stmt_items->bind_param("s", $transfer_id);
            if (!$stmt_items->execute()) {
                throw new Exception("Error executing transfer_in_items delete: " . $stmt_items->error);
            }
            $stmt_items->close();

            // 2. ลบจาก transfer_in_header
            $sql_header = "DELETE FROM transfer_in_header WHERE transfer_in_id = ?";
            $stmt_header = $conn->prepare($sql_header);
            if (!$stmt_header) {
                throw new Exception("Error preparing transfer_in_header delete statement: " . $conn->error);
            }
            $stmt_header->bind_param("s", $transfer_id);
            if (!$stmt_header->execute()) {
                throw new Exception("Error executing transfer_in_header delete: " . $stmt_header->error);
            }
            $stmt_header->close();

        } elseif ($transfer_type === 'out') {
            // 1. ดึงข้อมูล transfer_out_items เพื่อปรับปรุง in_stock ใน products
            $sql_get_items = "SELECT product_id, quantity FROM transfer_out_items WHERE transfer_out_id = ?";
            $stmt_get_items = $conn->prepare($sql_get_items);
            if (!$stmt_get_items) {
                throw new Exception("Error preparing transfer_out_items select statement: " . $conn->error);
            }
            $stmt_get_items->bind_param("s", $transfer_id);
            if (!$stmt_get_items->execute()) {
                throw new Exception("Error executing transfer_out_items select: " . $stmt_get_items->error);
            }
            $result_items = $stmt_get_items->get_result();

            $items = [];
            while ($row = $result_items->fetch_assoc()) {
                $items[] = [
                    'product_id' => $row['product_id'],
                    'quantity' => intval($row['quantity'])
                ];
            }
            $stmt_get_items->close();

            // 2. เพิ่มจำนวนสินค้าใน in_stock ใน products
            $sql_update_stock = "UPDATE products SET in_stock = in_stock + ? WHERE product_id = ?";
            $stmt_update_stock = $conn->prepare($sql_update_stock);
            if (!$stmt_update_stock) {
                throw new Exception("Error preparing products update statement: " . $conn->error);
            }

            foreach ($items as $item) {
                $stmt_update_stock->bind_param("is", 
                    $item['quantity'],
                    $item['product_id']
                );
                if (!$stmt_update_stock->execute()) {
                    throw new Exception("Error updating product stock: " . $stmt_update_stock->error);
                }
            }
            $stmt_update_stock->close();

            // 3. ลบจาก transfer_out_items
            $sql_items = "DELETE FROM transfer_out_items WHERE transfer_out_id = ?";
            $stmt_items = $conn->prepare($sql_items);
            if (!$stmt_items) {
                throw new Exception("Error preparing transfer_out_items delete statement: " . $conn->error);
            }
            $stmt_items->bind_param("s", $transfer_id);
            if (!$stmt_items->execute()) {
                throw new Exception("Error executing transfer_out_items delete: " . $stmt_items->error);
            }
            $stmt_items->close();

            // 4. ลบจาก transfer_out_header
            $sql_header = "DELETE FROM transfer_out_header WHERE transfer_out_id = ?";
            $stmt_header = $conn->prepare($sql_header);
            if (!$stmt_header) {
                throw new Exception("Error preparing transfer_out_header delete statement: " . $conn->error);
            }
            $stmt_header->bind_param("s", $transfer_id);
            if (!$stmt_header->execute()) {
                throw new Exception("Error executing transfer_out_header delete: " . $stmt_header->error);
            }
            $stmt_header->close();
        } else {
            throw new Exception("Invalid transfer type specified.");
        }

        // Commit Transaction
        $conn->commit();

        // ปิดการเชื่อมต่อ
        $conn->close();

        // รีไดเร็กต์กลับไปที่หน้าหลัก พร้อมข้อความสำเร็จ
        header("Location: 08transaction_history.php?status=success&message=ลบข้อมูลสำเร็จ");
        exit();

    } catch (Exception $e) {
        // Rollback Transaction
        $conn->rollback();

        // ปิดการเชื่อมต่อ
        $conn->close();

        // รีไดเร็กต์กลับไปที่หน้าหลัก พร้อมข้อความผิดพลาด
        header("Location: 08transaction_history.php?status=error&message=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // ถ้าไม่มีการส่งค่าที่จำเป็น
    header("Location: 08transaction_history.php?status=error&message=ไม่มีข้อมูลที่จำเป็น");
    exit();
}
?>
