<?php
require_once 'config.php';
// ฟังก์ชันคำนวณค่าน้ำและค่าไฟ
function calculateWaterCost($start, $end, $rate) {
    $usage = $end - $start;
    return $usage * $rate;
}

function calculateElectricCost($start, $end, $rate) {
    $usage = $end - $start;
    return $usage * $rate;
}

// ดึงข้อมูลผู้ใช้และประเภทห้อง
$userId = 8; // ตัวอย่างเลข id ของผู้ใช้
$sql = "SELECT u.Room_number, u.First_name, u.Last_name, t.type_name, t.price 
        FROM users u
        LEFT JOIN type t ON u.type_id = t.id
        WHERE u.id = $userId";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $roomNumber = $row['Room_number'];
    $firstName = $row['First_name'];
    $lastName = $row['Last_name'];
    $roomType = $row['type_name'];
    $roomPrice = $row['price'];
} else {
    echo "No user found.";
}

// ดึงข้อมูลมิเตอร์น้ำและไฟฟ้าล่าสุด
$waterSql = "SELECT meter_water, start_meter as water_start, end_meter as water_end, date_record 
             FROM water 
             WHERE user_id = $userId 
             ORDER BY date_record DESC 
             LIMIT 1";
$waterResult = $conn->query($waterSql);

$electricSql = "SELECT meter_electric, start_meter as electric_start, end_meter as electric_end, date_record
                FROM electric
                WHERE user_id = $userId
                ORDER BY date_record DESC
                LIMIT 1";
$electricResult = $conn->query($electricSql);

if ($waterResult->num_rows > 0) {
    $waterRow = $waterResult->fetch_assoc();
    $waterMeter = $waterRow['meter_water'];
    $waterStart = $waterRow['water_start'];
    $waterEnd = $waterRow['water_end'];
} else {
    $waterMeter = 0;
    $waterStart = 0;
    $waterEnd = 0;
}

if ($electricResult->num_rows > 0) {
    $electricRow = $electricResult->fetch_assoc();
    $electricMeter = $electricRow['meter_electric'];
    $electricStart = $electricRow['electric_start'];
    $electricEnd = $electricRow['electric_end'];
} else {
    $electricMeter = 0;
    $electricStart = 0;
    $electricEnd = 0;
}

// ประมวลผลฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $month = date("n");
    $year = date("Y");
    $waterNow = $_POST['bill_water_now'];
    $electricNow = $_POST['bill_elec_now'];
    $waterRate = 5;
    $electricRate = 12;

    $waterCost = calculateWaterCost($waterEnd, $waterNow, $waterRate);
    $electricCost = calculateElectricCost($electricEnd, $electricNow, $electricRate);
    $totalCost = $roomPrice + $waterCost + $electricCost;

    // บันทึกข้อมูลบิลลงในตาราง bill
    $billSql = "INSERT INTO bill (user_id, month, year, electric_cost, water_cost, room_cost, total_cost)
                VALUES ($userId, $month, $year, $electricCost, $waterCost, $roomPrice, $totalCost)";
    if ($conn->query($billSql) === TRUE) {
        echo "Bill saved successfully";
    } else {
        echo "Error: " . $billSql . "<br>" . $conn->error;
    }

    // บันทึกข้อมูลมิเตอร์น้ำและไฟฟ้าลงในตาราง water และ electric
    $waterSql = "INSERT INTO water (user_id, meter_water, start_meter, end_meter, date_record)
                 VALUES ($userId, $waterNow, $waterEnd, $waterNow, CURDATE())";
    if ($conn->query($waterSql) === TRUE) {
        echo "Water meter saved successfully";
    } else {
        echo "Error: " . $waterSql . "<br>" . $conn->error;
    }

    $electricSql = "INSERT INTO electric (user_id, meter_electric, start_meter, end_meter, date_record)
                    VALUES ($userId, $electricNow, $electricEnd, $electricNow, CURDATE())";
    if ($conn->query($electricSql) === TRUE) {
        echo "Electric meter saved successfully";
    } else {
        echo "Error: " . $electricSql . "<br>" . $conn->error;
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>ฟอร์มคิดค่าเช่า ระบบหอพัก</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <br>
                <h3>ฟอร์มคิดค่าเช่า </h3>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-sm-2">
                            <label class="col-form-label">เลขห้อง</label>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" name="room_number" class="form-control" value="<?php echo $roomNumber; ?>" readonly>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-sm-2">
                            <label class="col-form-label">ชื่อ-นามสกุล</label>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" value="<?php echo $firstName . ' ' . $lastName; ?>" readonly>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-sm-2">
                            <label class="col-form-label">ประเภทห้อง</label>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" value="<?php echo $roomType; ?>" readonly>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-sm-2">
                            <label class="col-form-label">ค่าเช่า</label>
                        </div>
                        <div class="col-sm-2">
                            <input type="number" name="bill_room_price" class="form-control" value="<?php echo $roomPrice; ?>" readonly>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-sm-2">
                            <label class="col-form-label">ค่าน้ำ</label>
                        </div>
                        <div class="col-sm-2">
                            เลขมิเตอร์ครั้งนี้
                            <input type="number" name="bill_water_now" value="<?php echo $waterMeter; ?>" required class="form-control" min="<?php echo $waterEnd; ?>">
                        </div>
                        <div class="col-sm-2">
                            ครั้งก่อน
                            <input type="number" name="bill_water_before" value="<?php echo $waterStart; ?>" required readonly class="form-control" min="0">
                        </div>
                        <div class="col-sm-2">
                            หน่วยที่ใช้
                            <input type="text" name="bill_water_meter" id="water_meter" required readonly class="form-control" min="0">
                        </div>
                        <div class="col-sm-2">
                            ราคา/หน่วย
                            <input type="number" name="bill_water_rate" value="5" required readonly class="form-control" min="0">
                        </div>
                        <div class="col-sm-2">
                            รวม(บาท)
                            <input type="number" name="bill_water_total" id="water_total" required readonly class="form-control" min="0">
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-sm-2">
                            <label class="col-form-label">ค่าไฟ</label>
                        </div>
                        <div class="col-sm-2">
                            เลขมิเตอร์ครั้งนี้
                            <input type="number" name="bill_elec_now" value="<?php echo $electricMeter; ?>" required class="form-control" min="<?php echo $electricEnd; ?>">
                        </div>
                        <div class="col-sm-2">
                            ครั้งก่อน
                            <input type="number" name="bill_elec_before" value="<?php echo $electricStart; ?>" required readonly class="form-control" min="0">
                        </div>
                        <div class="col-sm-2">
                            หน่วยที่ใช้
                            <input type="number" name="bill_elec_meter" id="electric_meter" required readonly class="form-control" min="0">
                        </div>
                        <div class="col-sm-2">
                            ราคา/หน่วย
                            <input type="number" name="bill_elec_rate" value="12" required readonly class="form-control" min="0">
                        </div>
                        <div class="col-sm-2">
                            รวม(บาท)
                            <input type="number" name="bill_elec_total" id="electric_total" required readonly class="form-control" min="0">
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-sm-2">
                            <label class="col-form-label">รวมทั้งสิ้น</label>
                        </div>
                        <div class="col-sm-3">
                            <input type="number" name="pay_total" id="total_cost" required readonly class="form-control" placeholder="รวมทั้งสิ้น" min="0">
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-sm-2">
                            <label class="col-form-label"></label>
                        </div>
                        <div class="col-sm-4">
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p class="text-center">ฟอร์มคิดค่าเช่า ระบบหอพัก </p>
    </footer>

    <script>
        // ฟังก์ชันคำนวณค่าน้ำและค่าไฟ
        function calculateWaterCost() {
            let waterBefore = parseInt(document.querySelector('input[name="bill_water_before"]').value);
            let waterNow = parseInt(document.querySelector('input[name="bill_water_now"]').value);
            let waterRate = parseFloat(document.querySelector('input[name="bill_water_rate"]').value);

            if (waterNow < waterBefore) {
                alert('เลขมิเตอร์น้ำครั้งนี้ต้องมากกว่าครั้งก่อน');
                return;
            }

            let waterUsage = waterNow - waterBefore;
            let waterCost = waterUsage * waterRate;

            document.getElementById('water_meter').value = waterUsage;
            document.getElementById('water_total').value = waterCost.toFixed(2);
            calculateTotalCost();
        }

        function calculateElectricCost() {
            let electricBefore = parseInt(document.querySelector('input[name="bill_elec_before"]').value);
            let electricNow = parseInt(document.querySelector('input[name="bill_elec_now"]').value);
            let electricRate = parseFloat(document.querySelector('input[name="bill_elec_rate"]').value);

            if (electricNow < electricBefore) {
                alert('เลขมิเตอร์ไฟฟ้าครั้งนี้ต้องมากกว่าครั้งก่อน');
                return;
            }

            let electricUsage = electricNow - electricBefore;
            let electricCost = electricUsage * electricRate;

            document.getElementById('electric_meter').value = electricUsage;
            document.getElementById('electric_total').value = electricCost.toFixed(2);
            calculateTotalCost();
        }

        function calculateTotalCost() {
            let roomPrice = parseFloat(document.querySelector('input[name="bill_room_price"]').value);
            let waterCost = parseFloat(document.getElementById('water_total').value);
            let electricCost = parseFloat(document.getElementById('electric_total').value);

            let totalCost = roomPrice + waterCost + electricCost;

            document.getElementById('total_cost').value = totalCost.toFixed(2);
        }
    </script>
</body>
</html>
