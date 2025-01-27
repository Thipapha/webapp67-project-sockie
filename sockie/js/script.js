// // ข้อมูลสินค้า (จาก PHP)
// const products = <?php echo json_encode($products); ?>;

// // แสดงวันที่และเวลา
// function updateDateTime() {
//     const now = new Date();
//     document.getElementById("currentDateTime").innerText = now.toLocaleString("th-TH");
// }

// // ค้นหาสินค้า
// function searchProduct() {
//     const searchInput = document.getElementById("searchInput").value.toLowerCase();
//     const productList = document.getElementById("productList");
//     productList.innerHTML = ""; // ล้างรายการสินค้าเก่า

//     products.forEach(product => {
//         if (product.product_name.toLowerCase().includes(searchInput)) { // ใช้ product.product_name แทน
//             const li = document.createElement("li");
//             li.className = "list-group-item d-flex justify-content-between align-items-center";
//             li.innerHTML = `${product.product_name} - ${product.price} ฿ <button class="btn btn-primary btn-sm" onclick="addToCart('${product.product_id}')">เพิ่ม</button>`;
//             productList.appendChild(li);
//         }
//     });
// }

// // ตะกร้าสินค้า
// let cart = [];

// // เพิ่มสินค้าลงในตะกร้า
// function addToCart(productId) {
//     const product = products.find(p => p.product_id === productId);
//     cart.push(product);
//     updateCart();
// }

// // อัปเดตตะกร้า
// function updateCart() {
//     const cartList = document.getElementById("cartList");
//     cartList.innerHTML = ""; // ล้างรายการสินค้าในตะกร้า
//     let total = 0;

//     cart.forEach(item => {
//         const li = document.createElement("li");
//         li.className = "list-group-item d-flex justify-content-between align-items-center";
//         li.innerHTML = `${item.product_name} - ${item.price} ฿ <button class="btn btn-danger btn-sm" onclick="removeFromCart('${item.product_id}')">ลบ</button>`;
//         cartList.appendChild(li);
//         total += item.price;
//     });

//     document.getElementById("totalPrice").innerText = total.toFixed(2) + " ฿";
// }

// // ลบสินค้าจากตะกร้า
// function removeFromCart(productId) {
//     cart = cart.filter(item => item.product_id !== productId);
//     updateCart();
// }

// // ชำระเงิน
// function checkout() {
//     if (cart.length === 0) {
//         alert("กรุณาเพิ่มสินค้าก่อนชำระเงิน");
//         return;
//     }

//     const salesList = document.getElementById("salesList");
//     cart.forEach(item => {
//         const totalPrice = item.price; // หรือคูณด้วยจำนวนถ้าต้องการ
//         const row = document.createElement("tr");
//         row.innerHTML = `<td>${salesList.children.length + 1}</td><td>${item.product_id}</td><td>1</td><td>${item.price}</td><td>${totalPrice}</td>`;
//         salesList.appendChild(row);
//     });

//     const total = cart.reduce((sum, item) => sum + item.price, 0);
//     alert(`ชำระเงินสำเร็จ! ยอดรวม: ${total.toFixed(2)} ฿`);
//     cart = []; // ล้างตะกร้าสินค้า
//     updateCart();
// }

// // อัปเดตเวลาเมื่อโหลดหน้า
// updateDateTime();
// setInterval(updateDateTime, 1000);
