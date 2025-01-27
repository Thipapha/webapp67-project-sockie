<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sockie";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบการส่งข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ตรวจสอบฟิลด์ไม่ว่างเปล่า
    if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['first_name']) && !empty($_POST['last_name']) && !empty($_POST['email'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $confirm_password = $_POST['confirm_password'];

        // ตรวจสอบวรหัสผ่านและยืนยันรหัสผ่านตรงกัน
        if ($password !== $confirm_password) {
            echo "รหัสผ่านไม่ตรงกัน!";
        } else {
            // ป้องกันการโจมตีแบบ SQL Injection
            $username = $conn->real_escape_string($username);
            $password = $conn->real_escape_string($password);
            $first_name = $conn->real_escape_string($first_name);
            $last_name = $conn->real_escape_string($last_name);
            $email = $conn->real_escape_string($email);

            // บันทึกข้อมูล
            $sql = "INSERT INTO user_ad (username, password, first_name, last_name, email) VALUES ('$username', '$password', '$first_name', '$last_name', '$email')";

            if ($conn->query($sql) === TRUE) {
                echo "ลงทะเบียนสำเร็จ!";
                header("Location: 02login.php");
            } else {
                echo "เกิดข้อผิดพลาด: " . $sql . "<br>" . $conn->error;
            }
        }
    } else {
        // ฟิลด์ว่างเปล่า
        echo "กรุณากรอกข้อมูลให้ครบ!";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/css_test/login.css">
</head>

<body>
    <section class="vh-100 d-flex justify-content-center align-items-center" style="background-color: #f0f0f0;">
        <div class="container-fluid h-custom d-flex justify-content-center align-items-center">
            <div class="bg-white p-5 rounded" style="max-width: 1200px; box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-md-9 col-lg-6 col-xl-5">
                    <a href="">
                        <img src="images/Logo Web App.png" class="img-fluid" alt="Sample image"
                            style="cursor: pointer;">
                    </a>
                </div>
                <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                    <form method="POST" action="">
                        <div class="">
                            <p class="text-center fw-bold mx-3 mb-5" style="font-size: 30px;">ลงทะเบียน</p>
                        </div>

                        <!-- Name -->
                        <div data-mdb-input-init class="form-outline mb-4">
                            <div class="row">
                                <!-- First Name -->
                                <div class="col-md-6">
                                    <label class="form-label" for="firstName">ชื่อ</label>
                                    <input type="text" name="first_name" class="form-control form-control-lg" placeholder="ชื่อ" required />
                                </div>

                                <!-- Last Name -->
                                <div class="col-md-6">
                                    <label class="form-label" for="lastName">นามสกุล</label>
                                    <input type="text" name="last_name" class="form-control form-control-lg" placeholder="นามสกุล" required />
                                </div>
                            </div> <br>

                            <!-- Account -->
                            <div data-mdb-input-init class="form-outline mb-4">
                                <label class="form-label" for="account">บัญชีผู้ใช้</label>
                                <input type="text" name="username" class="form-control form-control-lg" placeholder="บัญชีผู้ใช้" required />
                            </div>

                            <!-- Email -->
                            <div data-mdb-input-init class="form-outline mb-4">
                                <label class="form-label" for="email">อีเมล</label>
                                <input type="email" name="email" class="form-control form-control-lg" placeholder="อีเมล" required />
                            </div>

                            <!-- Password input -->
                            <div data-mdb-input-init class="form-outline mb-3">
                                <label class="form-label" for="password">รหัสผ่าน</label>
                                <input type="password" name="password" class="form-control form-control-lg" placeholder="รหัสผ่าน" required />
                            </div>

                            <!-- Password input confirm-->
                            <div data-mdb-input-init class="form-outline mb-3">
                                <label class="form-label" for="confirm_password">ยืนยันรหัสผ่าน</label>
                                <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="ยืนยันรหัสผ่าน" required />
                            </div>

                            <div class="text-center text-lg-start mt-4 pt-2 d-flex flex-column align-items-center">
                                <button type="submit" class="btn btn-primary btn-lg" style="padding-left: 2.5rem; padding-right: 2.5rem; background-color: #FFDF49; color: black; border: none;">ลงทะเบียน</button>
                                <br>
                                <p class="small fw-bold mt-2 pt-1 mb-0">มีบัญชีแล้ว? <a href="02login.php" class="link-danger">เข้าสู่ระบบ</a></p>
                            </div>

                    </form>
                </div>
            </div>
        </div>
        </div>
    </section>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
    crossorigin="anonymous"></script>
</body>

</html>
