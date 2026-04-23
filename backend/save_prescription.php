<?php
session_start();
require "db.php";

if ($_SESSION['role'] !== "doctor") {
    echo "Access denied.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $doctor_id      = $_SESSION['user_id'];
    $appointment_id = $_POST['appointment_id'];
    $diagnosis      = $_POST['diagnosis'];
    $medicines      = $_POST['medicines'];

    /* =====================================================
       1) تأكيد إن الميعاد فعلاً تابع للدكتور و Pending
    ===================================================== */
    $check = $conn->prepare("
        SELECT patient_id, date
        FROM appointments
        WHERE id=? AND doctor_id=? AND status='pending'
    ");
    $check->bind_param("ii", $appointment_id, $doctor_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        echo "Invalid appointment selected.";
        exit;
    }

    // الحصول على patient_id و تاريخ الميعاد تلقائياً
    $row = $result->fetch_assoc();
    $patient_id      = $row['patient_id'];
    $appointment_date = $row['date'];  // ← التاريخ الحقيقي للميعاد

    /* =====================================================
       2) إضافة الروشتة باستخدام نفس تاريخ الميعاد
    ===================================================== */
    $insert = $conn->prepare("
        INSERT INTO prescriptions 
        (appointment_id, patient_id, doctor_id, diagnosis, medicines, date)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $insert->bind_param("iiisss",
        $appointment_id,
        $patient_id,
        $doctor_id,
        $diagnosis,
        $medicines,
        $appointment_date
    );
    $insert->execute();

    /* =====================================================
       3) تحديث حالة الميعاد إلى diagnosed
    ===================================================== */
    $update = $conn->prepare("
        UPDATE appointments
        SET status='diagnosed'
        WHERE id=? AND doctor_id=?
    ");
    $update->bind_param("ii", $appointment_id, $doctor_id);
    $update->execute();

    header("Location: doctor_dashboard.php");
    exit;
}
?>
