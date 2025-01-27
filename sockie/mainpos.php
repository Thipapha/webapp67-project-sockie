<?php
require_once '00conn.php';

// ดึงข้อมูลสินค้า
$query = "SELECT product_id, product_name, price FROM products";
$result = $conn->query($query);

$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'product_id' => $row['product_id'],
            'product_name' => $row['product_name'],
            'price' => $row['price']
        ];
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบ POS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<script>
    // ข้อมูลสินค้า (จาก PHP)
    const products = <?php echo json_encode($products); ?>;

    // แสดงวันที่และเวลา
    function updateDateTime() {
        const now = new Date();
        document.getElementById("currentDateTime").innerText = now.toLocaleString("th-TH");
    }

    // ค้นหาสินค้า
    function searchProduct() {
        const searchInput = document.getElementById("searchInput").value.toLowerCase();
        const productList = document.getElementById("productList");
        productList.innerHTML = ""; // ล้างรายการสินค้าเก่า

        products.forEach(product => {
            if (product.product_name.toLowerCase().includes(searchInput)) {
                const li = document.createElement("li");
                li.className = "list-group-item d-flex justify-content-between align-items-center";
                li.innerHTML = `${product.product_name} - ${product.price} ฿ <button class="btn btn-primary btn-sm" onclick="addToCart('${product.product_id}')">เพิ่ม</button>`;
                productList.appendChild(li);
            }
        });
    }

    let cart = {}; // ใช้เป็นออบเจ็กต์เพื่อเก็บข้อมูลสินค้าและจำนวน

    // เพิ่มสินค้าลงในตะกร้า
    function addToCart(productId) {
        const product = products.find(p => p.product_id === productId);
        if (cart[productId]) {
            cart[productId].quantity++; // เพิ่มจำนวนถ้ามีสินค้าอยู่แล้ว
            cart[productId].total_price = (cart[productId].unit_price * cart[productId].quantity).toFixed(2);
        } else {
            cart[productId] = { 
                product_id: product.product_id,
                product_name: product.product_name,
                unit_price: parseFloat(product.price),
                quantity: 1,
                total_price: parseFloat(product.price).toFixed(2)
            }; // เพิ่มสินค้าใหม่
        }
        console.log("Cart after adding:", cart); // ตรวจสอบข้อมูลในตะกร้าหลังจากเพิ่ม
        updateCart();
    }

    // อัปเดตตะกร้า
    function updateCart() {
        const cartList = document.getElementById("cartList");
        cartList.innerHTML = ""; // ล้างรายการสินค้าในตะกร้า
        let total = 0;

        Object.values(cart).forEach(item => {
            const li = document.createElement("li");
            li.className = "list-group-item d-flex justify-content-between align-items-center";
            li.innerHTML = `${item.product_name} - ${item.unit_price} ฿ x ${item.quantity} = ${item.total_price} ฿ <button class="btn btn-danger btn-sm" onclick="removeFromCart('${item.product_id}')">ลบ</button>`;
            cartList.appendChild(li);
            total += parseFloat(item.total_price);
        });

        document.getElementById("totalPrice").innerText = total.toFixed(2) + " ฿"; // แสดงยอดรวม
    }

    // ลบสินค้าจากตะกร้า
    function removeFromCart(productId) {
        if (cart[productId]) {
            delete cart[productId]; // ลบสินค้าจากตะกร้า
            updateCart(); // อัปเดตตะกร้าสินค้า
        }
    }

    // ชำระเงิน
    function checkout() {
        if (Object.keys(cart).length === 0) {
            alert("กรุณาเพิ่มสินค้าก่อนชำระเงิน");
            return;
        }

        const salesList = document.getElementById("salesList");
        Object.values(cart).forEach(item => {
            const totalPrice = item.unit_price * item.quantity; // คำนวณราคาสุทธิ
            const row = document.createElement("tr");
            row.innerHTML = `<td>${salesList.children.length + 1}</td><td>${item.product_id}</td><td>${item.quantity}</td><td>${item.unit_price}</td><td>${totalPrice.toFixed(2)}</td>`;
            salesList.appendChild(row);
        });

        const total = Object.values(cart).reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
        alert(`ชำระเงินสำเร็จ! ยอดรวม: ${total.toFixed(2)} ฿`);
        cart = {}; // ล้างตะกร้าสินค้า
        updateCart();
    }

    // บันทึกยอดขาย
    function saveDailySales() {
        const salesList = document.getElementById("salesList");
        const rows = salesList.getElementsByTagName("tr");
        
        if (rows.length === 0) {
            alert("กรุณาเพิ่มสินค้าก่อนบันทึกยอดขาย");
            return;
        }

        const sendBy = prompt("กรุณากรอกชื่อผู้ส่ง:");
        if (!sendBy || sendBy.trim() === "") {
            alert("กรุณากรอกชื่อผู้ส่ง");
            return;
        }

        let items = [];

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName("td");
            const product_id = cells[1].innerText;
            const quantity = parseInt(cells[2].innerText);
            const unit_price = parseFloat(cells[3].innerText);
            const total_price = parseFloat(cells[4].innerText);

            items.push({
                product_id: product_id,
                quantity: quantity,
                unit_price: unit_price,
                total_price: total_price
            });
        }

        const data = {
            transfer_date: new Date().toISOString().slice(0, 10),
            from_location: "pos",
            to_location: "sockie",
            send_by: sendBy.trim(),
            items: items
        };

        console.log("Data to send:", data); // Debugging

        fetch('save_sales.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert(result.message);
                salesList.innerHTML = ""; // เคลียร์รายการขาย
                cart = {}; // ล้างตะกร้าสินค้า
                updateCart();
            } else {
                alert(`เกิดข้อผิดพลาด: ${result.message}`);
            }
        })
        .catch(error => {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            console.error('Error:', error);
        });
    }

    // อัปเดตเวลาเมื่อโหลดหน้า
    updateDateTime();
    setInterval(updateDateTime, 1000); // อัปเดตทุกวินาที
</script>

<div class="container">
    <header class="text-center my-4">
        <h1>ระบบ POS sockie </h1>
        <h4 id="currentDateTime"></h4>
        
        <a href="04main.php">ไปหน้าระบบจัดการคลังสินค้า  </a> 
    </header>

    <div class="row">
        <div class="col-md-6">
            <h5>ค้นหาสินค้า</h5>
            <input type="text" id="searchInput" class="form-control" placeholder="ค้นหาสินค้า..." oninput="searchProduct()">
            <ul id="productList" class="list-group my-3">
                <!-- รายการสินค้าจะถูกเพิ่มที่นี่ -->
            </ul>
        </div>

        <div class="col-md-6">
            <h5>ตะกร้าสินค้า</h5>
            <ul id="cartList" class="list-group my-3">
                <!-- สินค้าในตะกร้าจะถูกเพิ่มที่นี่ -->
            </ul>
            <div class="d-flex justify-content-between">
                <h5>ยอดรวม:</h5>
                <h5 id="totalPrice">0.00 ฿</h5>
            </div>
            <button class="btn btn-success my-2" onclick="checkout()">ชำระเงิน</button>
            
        </div>
    </div>
    <div class="my-4">
        <h5>รายการขายล่าสุด</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>รหัสสินค้า</th>
                    <th>จำนวน</th>
                    <th>ราคา</th>
                    <th>ยอดรวม</th>
                </tr>
            </thead>
            <tbody id="salesList">
                <!-- ข้อมูลการขายจะถูกเพิ่มที่นี่ -->
            </tbody>
        </table>
        <button class="btn btn-warning my-2" onclick="saveDailySales()">บันทึกยอดขายรายวัน</button>
    </div>
    <footer class="text-center my-4">
        <p>ระบบ POS © 2024</p>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
