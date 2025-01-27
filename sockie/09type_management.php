<?php
// เชื่อมต่อฐานข้อมูล
include '00conn.php';

// ดึงข้อมูลประเภทสินค้าจากฐานข้อมูล
$sql = "SELECT * FROM types";
$result = $conn->query($sql);

// ค้นหาประเภทสินค้าจากฐานข้อมูล
$search_query = isset($_GET['search']) ? $_GET['search'] : ''; // กำหนดตัวแปร เพื่อช่องค้นหา

$sql = "SELECT * FROM types WHERE type_id LIKE ? OR type_name LIKE ?";
$stmt = $conn->prepare($sql);
$search_term = "%" . $search_query . "%";
$stmt->bind_param("ss", $search_term, $search_term);
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบการดึงข้อมูล
if (!$result) {
    die("การดึงข้อมูลล้มเหลว: " . $conn->error);
}

$search_query = isset($_GET['search']) ? $_GET['search'] : ''; /*กำหนดตัวแปร เพื่อช่องค้นหา ซิมติง*/
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการประเภทสินค้า - ระบบจัดการคลังสินค้า</title>
    <!--<link rel="stylesheet" href="styles.css">-->
    <link rel="stylesheet" href="css/09type_management.css">
    <link rel="stylesheet" href="css/layout.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
        <div class="type_area">
            <h2>จัดการประเภทสินค้า</h2>
            <br>

            <!-- ตัวเลือกการกรองและค้นหา -->
            <div class="search-container my-3">
                <div class="form-container">

                    <!-- ฟอร์มเพิ่มประเภทสินค้า -->
                    <div class="add-category-form">
                        <h3>เพิ่มประเภทสินค้า</h3>
                        <form id="categoryForm" method="POST" action="09type_management/add_category.php">
                            <div class="form-group">
                                <label for="type_id">รหัสประเภท:</label>
                                <input type="text" name="type_id" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="type_name">ชื่อประเภทสินค้า:</label>
                                <input type="text" name="type_name" class="form-control" required>
                            </div>
                            <br>
                            <button type="submit" class="btn btn-success">เพิ่ม</button>
                        </form>
                    </div>
                    <br>
                    <!-- ฟอร์มค้นหา -->
                    <div id="search-bar" class="input-group rounded search-bar">
                        <form id="searchForm" method="GET" class="form-inline">
                            <div class="input-group rounded">
                                <input type="search" name="search" class="form-control rounded" placeholder="ค้นหาด้วยรหัสประเภท" aria-label="Search" value="<?php echo htmlspecialchars($search_query); ?>" />
                                <button type="submit" class="input-group-text border-0" id="search-addon">
                                    <i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
            <br>

            <!-- ฟอร์มแก้ไขประเภทสินค้า -->
            <div class="edit-category-form" style="display: none; margin-top: 10px;">
                <h3>แก้ไขประเภทสินค้า</h3>
                <form id="editCategoryForm" method="POST" action="09type_management/edit_category.php">
                    <input type="hidden" name="type_id" id="editTypeId" required>
                    <div class="form-group">
                        <label for="edit_type_name">ชื่อประเภทสินค้า:</label>
                        <input type="text" name="type_name" id="editTypeName" class="form-control" required>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-warning">อัปเดต</button>
                    <button type="button" class="btn btn-secondary" id="cancelEdit">ยกเลิก</button>
                </form>
            </div>
            <br>

            <!-- ตารางประเภทสินค้า -->
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>รหัสประเภท</th>
                        <th>ชื่อประเภทสินค้า</th>
                        <th>แก้ไข</th>
                        <th>ลบ</th>
                    </tr>
                </thead>
                <tbody id="categoryList">
                    <?php
                    // แสดงข้อมูลประเภทที่ดึงจากฐานข้อมูล
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['type_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['type_name']) . "</td>";
                            echo "<td>
                                    <button class='btn btn-warning' data-id='" . $row['type_id'] . "' data-name='" . htmlspecialchars($row['type_name']) . "'>แก้ไข</button>
                                  </td>";
                            echo "<td>
                                    <button class='btn btn-danger' data-id='" . $row['type_id'] . "'>ลบ</button>
                                </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>ไม่มีข้อมูลประเภท</td></tr>"; // เปลี่ยนเป็น colspan='4'
                    }
                    ?>
                </tbody>
            </table>


        </div>
    </div>



    <!-- ส่วนท้าย -->
    <footer>
        <p>ระบบจัดการคลังสินค้า © 2024</p>
    </footer>

    <script>
        const categoryForm = document.getElementById('categoryForm');
        const categoryList = document.getElementById('categoryList');
        const searchForm = document.getElementById('searchForm');
        const editForm = document.querySelector('.edit-category-form');
        const addForm = document.querySelector('.add-category-form');
        const editTypeId = document.getElementById('editTypeId');
        const editTypeName = document.getElementById('editTypeName');
        const cancelEdit = document.getElementById('cancelEdit');

        // ฟังก์ชันสำหรับส่งข้อมูลฟอร์มไปยังเซิร์ฟเวอร์
        function submitForm(url, formData, successMessage) {
            fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data === 'success') {
                        alert(successMessage);
                        window.location.href = '09type_management.php';
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + data);
                    }
                })
                .catch(error => console.error('เกิดข้อผิดพลาด:', error));
        }

        // ฟังก์ชันสำหรับอัปเดตตารางประเภทสินค้า
        function updateCategoryList() {
            fetch('09type_management.php')
                .then(response => response.text())
                .then(data => {
                    const newContent = new DOMParser()
                        .parseFromString(data, 'text/html')
                        .getElementById('categoryList').innerHTML;
                    categoryList.innerHTML = newContent;
                });
        }

        // ฟังก์ชันจัดการลบประเภทสินค้า
        categoryList.addEventListener('click', event => {
            if (event.target.classList.contains('btn-danger')) {
                const categoryId = event.target.getAttribute('data-id');
                if (confirm('คุณแน่ใจว่าต้องการลบประเภทนี้?')) {
                    window.location.href = `09type_management/delete_category.php?id=${categoryId}`;
                }
            }
        });

        // ฟังก์ชันแสดงฟอร์มแก้ไข
        function showEditForm(categoryId, categoryName) {
            editTypeId.value = categoryId;
            editTypeName.value = categoryName;
            categoryForm.action = '09type_management/edit_category.php';
            categoryForm.querySelector('button').textContent = 'แก้ไขประเภท';
            toggleForms(true);
        }

        // ฟังก์ชันสลับการแสดงฟอร์ม
        function toggleForms(isEditing) {
            editForm.style.display = isEditing ? 'block' : 'none';
            addForm.style.display = isEditing ? 'none' : 'block';
            editForm.style.marginTop = '10px';

            const searchBar = document.getElementById('search-bar');
            if (searchBar) {
                searchBar.style.display = isEditing ? 'none' : 'block';

                if (!isEditing) {
                    searchBar.style.width = '300px'; // รีเซ็ตความกว้างเมื่อแสดงบาร์ค้นหา
                }
            }

            if (isEditing) {
                document.querySelector('h2').scrollIntoView({
                    behavior: 'smooth'
                });
            }
        }

        // ฟังก์ชันจัดการแก้ไขประเภท
        categoryList.addEventListener('click', event => {
            if (event.target.classList.contains('btn-warning')) {
                const categoryId = event.target.getAttribute('data-id');
                const categoryName = event.target.getAttribute('data-name');
                showEditForm(categoryId, categoryName);
                hideSearchBar();
            }
        });

        // ฟังก์ชันซ่อนบาร์ค้นหา
        function hideSearchBar() {
            const searchBar = document.getElementById('search-bar');
            if (searchBar) searchBar.style.display = 'none';
        }

        // ฟังก์ชันยกเลิกการแก้ไข
        cancelEdit.addEventListener('click', () => {
            toggleForms(false);
            resetForm();
        });

        // ฟังก์ชันรีเซ็ตฟอร์ม
        function resetForm() {
            categoryForm.reset();
            categoryForm.action = '09type_management/add_category.php';
            categoryForm.querySelector('button').textContent = 'เพิ่มประเภท';
        }

        // ฟังก์ชันจัดการการค้นหา
        searchForm.addEventListener('input', () => {
            if (!searchForm.querySelector('input[name="search"]').value.trim()) {
                window.location.href = '09type_management.php';
            }
        });

        // Event Listener สำหรับส่งฟอร์มเพิ่มประเภทสินค้า
        categoryForm.addEventListener('submit', event => {
            event.preventDefault();
            submitForm('09type_management/add_category.php', new FormData(categoryForm), 'เพิ่มประเภทสินค้าเรียบร้อยแล้ว');
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>