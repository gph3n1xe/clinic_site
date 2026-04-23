<?php
require "auth.php";
require "db.php";

if ($_SESSION['role'] !== "doctor") {
    echo "Access denied.";
    exit();
}

$doctor_id = $_SESSION['user_id'];

/* ================================
   FETCH ALL APPOINTMENTS
================================ */
$sql = "SELECT a.id, a.date, a.time, a.status, u.username AS patient_name
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        WHERE a.doctor_id = ?
        ORDER BY a.date ASC, a.time ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$appointments = $stmt->get_result();

/* ================================
   FETCH ONLY PENDING APPOINTMENTS
================================ */
$sql_pending = "SELECT a.id, a.date, a.time, u.username AS fullname, u.id AS patient_id
                FROM appointments a
                JOIN users u ON a.patient_id = u.id
                WHERE a.doctor_id = ? AND a.status = 'pending'";

$stmt2 = $conn->prepare($sql_pending);
$stmt2->bind_param("i", $doctor_id);
$stmt2->execute();
$pending_appointments = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Doctor Dashboard</title>

<style>
body { margin:0; font-family:Arial; background:#f0f4ff; }

.navbar {
    background:#1e3a8a;
    padding:15px;
    color:#fff;
    font-size:22px;
    font-weight:700;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.logout-btn {
    padding:10px 20px;
    background:red;
    color:white;
    border-radius:5px;
    text-decoration:none;
    font-weight:bold;
}

.container { width:90%; margin:25px auto; }

.card {
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    margin-bottom:25px;
}

h2 { color:#1e3a8a; margin-bottom:15px; }

table { width:100%; border-collapse:collapse; margin-top:10px; }
td, th { border:1px solid #cbd5e1; padding:10px; }
th { background:#eef2ff; color:#1e3a8a; }

input, textarea, select {
    width:100%;
    padding:10px;
    border:1px solid #cbd5e1;
    border-radius:8px;
    margin:6px 0;
}

.btn {
    padding:10px 15px;
    background:#1e3a8a;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:600;
    margin-top:10px;
}
.btn:hover { background:#3b82f6; }
</style>

</head>

<body>

<div class="navbar">
    <span>Doctor Dashboard</span>
    <a href="/hospital/login.html?type=doctor" class="logout-btn">Logout</a>
</div>

<div class="container">

    <!-- DOCTOR APPOINTMENTS -->
    <div class="card">
        <h2>Appointments</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
            <?php while($app = $appointments->fetch_assoc()) { ?>
            <tr>
                <td><?= $app['id'] ?></td>
                <td><?= htmlspecialchars($app['patient_name']) ?></td>
                <td><?= $app['date'] ?></td>
                <td><?= $app['time'] ?></td>
                <td><?= ucfirst($app['status']) ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>


    <!-- PRESCRIPTIONS SECTION -->
    <div class="card">
        <h2>Add Prescription for Pending Appointments</h2>

        <form method="POST" action="save_prescription.php">

            <!-- Appointment dropdown -->
            <label>Select Pending Appointment</label>
            <select name="appointment_id" id="appointmentSelect" required>
              <option value="">-- Select Appointment --</option>
              <?php while($row = $pending_appointments->fetch_assoc()) { ?>
                  <option value="<?= $row['id'] ?>"
                          data-patient="<?= htmlspecialchars($row['fullname']) ?>">
                    #<?= $row['id'] ?> (<?= $row['date'] ?> - <?= $row['time'] ?>)
                  </option>
              <?php } ?>
            </select>

            <!-- Patient name auto-filled -->
            <label>Patient Name</label>
            <input type="text" id="patientName" disabled placeholder="Select appointment">

            <!-- Diagnosis -->
            <label>Diagnosis</label>
            <input type="text" name="diagnosis" required>

            <!-- Medicines -->
            <label>Medicines</label>
            <textarea name="medicines" rows="3" required></textarea>

            <button type="submit" class="btn">Submit</button>
        </form>
    </div>

</div>

<script>
document.getElementById("appointmentSelect").addEventListener("change", function() {
    let selected = this.options[this.selectedIndex];
    let patient = selected.getAttribute("data-patient") || "";
    document.getElementById("patientName").value = patient;
});
</script>

</body>
</html>
