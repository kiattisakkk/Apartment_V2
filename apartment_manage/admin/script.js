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
    let waterTotal = parseFloat(document.querySelector('input[name="bill_water_total"]').value);
    let electricTotal = parseFloat(document.querySelector('input[name="bill_elec_total"]').value);

    let totalCost = roomPrice + waterTotal + electricTotal;
    document.getElementById('total_cost').value = totalCost.toFixed(2);
}

// เรียกใช้ฟังก์ชันคำนวณเมื่อมีการเปลี่ยนแปลงค่าในช่องป้อนข้อมูล
document.querySelector('input[name="bill_water_now"]').addEventListener('input', calculateWaterCost);
document.querySelector('input[name="bill_elec_now"]').addEventListener('input', calculateElectricCost);