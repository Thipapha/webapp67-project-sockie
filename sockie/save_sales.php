<?php
// เปิดการแสดงข้อผิดพลาด (สำหรับการดีบัก)
// แนะนำให้ปิดในสภาพแวดล้อมการผลิต
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '00conn.php';

// รับข้อมูลจาก AJAX
$data = json_decode(file_get_contents("php://input"), true);

// บันทึกข้อมูลสำหรับการดีบัก
file_put_contents('debug_log.txt', print_r($data, true), FILE_APPEND);

// ตรวจสอบการรับข้อมูล
if (!isset($data['items']) || count($data['items']) === 0 || 
    !isset($data['transfer_date']) || !isset($data['send_by'])) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit();
}

// สร้าง transfer_out_id โดยใช้วันที่และเวลาเพื่อความไม่ซ้ำกัน
$transferDate = $data['transfer_date'];
$transferOutId = date('YmdHis') . uniqid(); // ตัวอย่างการสร้าง ID ด้วยวันที่และเวลา

// เริ่มต้น Transaction
$conn->begin_transaction();

try {
    // คำนวณ total_quantity และ total_price
    $totalQuantity = 0;
    $totalPrice = 0;
    
    foreach ($data['items'] as $item) {
        // ตรวจสอบว่ามีข้อมูลที่จำเป็นครบถ้วนหรือไม่
        if (!isset($item['product_id'], $item['quantity'], $item['unit_price'], $item['total_price'])) {
            throw new Exception('ข้อมูลสินค้าไม่ครบถ้วน');
        }
    
        // ตรวจสอบชนิดของข้อมูล
        if (!is_numeric($item['quantity']) || !is_numeric($item['unit_price']) || !is_numeric($item['total_price'])) {
            throw new Exception('ข้อมูลจำนวนหรือราคาไม่ถูกต้อง');
        }
    
        $totalQuantity += intval($item['quantity']);
        $totalPrice += floatval($item['total_price']);
    }
    
    // บันทึกข้อมูลลง transfer_out_header
    $headerQuery = "INSERT INTO transfer_out_header (transfer_out_id, transfer_date, from_location, to_location, send_by, total_quantity, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $headerStmt = $conn->prepare($headerQuery);
    
    if (!$headerStmt) {
        throw new Exception('Error preparing header statement: ' . $conn->error);
    }
    
    $from_location = "pos";
    $to_location = "sockie";
    $headerStmt->bind_param('sssssss', 
        $transferOutId,
        $transferDate,
        $from_location,
        $to_location,
        $data['send_by'],
        $totalQuantity,
        $totalPrice
    );
    
    if (!$headerStmt->execute()) {
        throw new Exception('บันทึกข้อมูล header ล้มเหลว: ' . $headerStmt->error);
    }
    $headerStmt->close();
    
    // บันทึกข้อมูลลง transfer_out_items และลดจำนวนสินค้าใน products
    $itemQuery = "INSERT INTO transfer_out_items (transfer_out_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)";
    $itemStmt = $conn->prepare($itemQuery);
    
    if (!$itemStmt) {
        throw new Exception('Error preparing item statement: ' . $conn->error);
    }
    
    $updateProductQuery = "UPDATE products SET in_stock = in_stock - ? WHERE product_id = ?";
    $updateProductStmt = $conn->prepare($updateProductQuery);
    
    if (!$updateProductStmt) {
        throw new Exception('Error preparing product update statement: ' . $conn->error);
    }
    
    foreach ($data['items'] as $item) {
        // ตรวจสอบว่ามีข้อมูลที่จำเป็นครบถ้วนหรือไม่
        if (!isset($item['product_id'], $item['quantity'], $item['unit_price'], $item['total_price'])) {
            throw new Exception('ข้อมูลสินค้าไม่ครบถ้วน');
        }
    
        $product_id = $item['product_id'];
        $quantity = intval($item['quantity']);
        $unit_price = floatval($item['unit_price']);
        $total_price = floatval($item['total_price']);
    
        // ตรวจสอบว่าสินค้ามีในสต็อกเพียงพอหรือไม่
        $checkStockQuery = "SELECT in_stock FROM products WHERE product_id = ?";
        $checkStockStmt = $conn->prepare($checkStockQuery);
        if (!$checkStockStmt) {
            throw new Exception('Error preparing stock check statement: ' . $conn->error);
        }
        $checkStockStmt->bind_param('s', $product_id);
        $checkStockStmt->execute();
        $checkStockStmt->bind_result($currentStock);
        if ($checkStockStmt->fetch()) {
            if ($currentStock < $quantity) {
                throw new Exception("สินค้ารหัส {$product_id} จำนวนไม่เพียงพอในสต็อก");
            }
        } else {
            throw new Exception("ไม่พบสินค้ารหัส {$product_id} ในฐานข้อมูล");
        }
        $checkStockStmt->close();
    
        // บันทึกข้อมูลลง transfer_out_items
        $itemStmt->bind_param('ssidd', 
            $transferOutId,
            $product_id,
            $quantity,
            $unit_price,
            $total_price
        );
    
        if (!$itemStmt->execute()) {
            throw new Exception('บันทึกข้อมูล item ล้มเหลว: ' . $itemStmt->error);
        }
    
        // ลดจำนวนสินค้าใน products
        $updateProductStmt->bind_param('is', 
            $quantity,
            $product_id
        );
    
        if (!$updateProductStmt->execute()) {
            throw new Exception('ลดจำนวนสินค้าในสต็อกล้มเหลว: ' . $updateProductStmt->error);
        }
    }
    
    $itemStmt->close();
    $updateProductStmt->close();
    
    // Commit Transaction
    $conn->commit();
    $conn->close();
    
    echo json_encode(['success' => true, 'message' => 'บันทึกยอดขายสำเร็จ!']);
} catch (Exception $e) {
    // Rollback Transaction
    $conn->rollback();
    $conn->close();
    
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
