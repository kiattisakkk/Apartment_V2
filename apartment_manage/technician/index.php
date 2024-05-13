<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำนวณค่าเช่า</title>
</head>
<body>
    <h2>คำนวณค่าเช่า</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="Room_number">เลขห้อง:</label>
        <input type="text" id="Room_number" name="Room_number" required><br><br>

        <input type="submit" value="คำนวณค่าเช่า">
    </form>

    <?php
    // ตรวจสอบการส่งข้อมูล POST มาจากฟอร์ม
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // รับค่าห้องที่เลือกจากฟอร์ม
        $Room_number = $_POST["Room_number"];

        // คำสั่ง SQL เพื่อดึงข้อมูลราคาเช่าของห้องที่เลือก
        $sql = "SELECT price FROM type WHERE id = (SELECT type_id FROM users WHERE Room_number = ?)";

        // ตรวจสอบคำสั่ง SQL
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // ผูกพารามิเตอร์และประมวลผลคำสั่ง SQL
            mysqli_stmt_bind_param($stmt, "s", $param_Room_number);

            // ตั้งค่าพารามิเตอร์และประมวลผลคำสั่ง SQL
            $param_Room_number = $Room_number;

            // ดำเนินการคำสั่ง SQL
            if (mysqli_stmt_execute($stmt)) {
                // เก็บผลลัพธ์จากคำสั่ง SQL
                mysqli_stmt_store_result($stmt);

                // ตรวจสอบว่ามีข้อมูลที่ถูกคืนหรือไม่
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // เก็บผลลัพธ์จากคำสั่ง SQL
                    mysqli_stmt_bind_result($stmt, $price);

                    // ดึงข้อมูล
                    if (mysqli_stmt_fetch($stmt)) {
                        // คำนวณค่าเช่ารวม (เพื่อตัวอย่างเท่านั้น)
                        $total_rent = $price; // แทนค่า $num_rooms ด้วยจำนวนห้องพักที่คำนวณ
                        // แสดงผลค่าเช่ารวม
                        echo "ค่าเช่ารวมทั้งสิ้น: " . $total_rent . " บาท";
                    }
                } else {
                    // หากไม่พบข้อมูล
                    echo "ไม่พบข้อมูลราคาเช่าสำหรับห้องพักที่เลือก";
                }
            } else {
                // หากไม่สามารถดำเนินการคำสั่ง SQL ได้
                echo "มีบางอย่างผิดพลาด กรุณาลองใหม่ภายหลัง";
            }

            // ปิดคำสั่ง SQL
            mysqli_stmt_close($stmt);
        }
    }
    ?>
</body>
</html>
