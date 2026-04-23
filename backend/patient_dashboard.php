<?php
require "auth.php";
require "db.php";

if ($_SESSION['role'] !== "patient") {
    echo "Access denied.";
    exit;
}

$patient_id = $_SESSION['user_id'];

// Get patient profile
$sql_profile = "SELECT id, username FROM users WHERE id=?";
$stmt = $conn->prepare($sql_profile);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$profile_result = $stmt->get_result();
$profile = $profile_result->fetch_assoc();

// Get all doctors for dropdown
$sql_doctors = "SELECT id, username FROM users WHERE role='doctor'";
$doctors = $conn->query($sql_doctors);

// =========================
// FIXED APPOINTMENT QUERY
// =========================
$sql_appointments = "
    SELECT 
        a.id,
        a.date,
        a.time,
        u.username AS doctor_name,
        CASE
    WHEN p.id IS NOT NULL THEN 'diagnosed'
    ELSE a.decision_status
END AS status

    FROM appointments a
    JOIN users u ON u.id = a.doctor_id
    LEFT JOIN prescriptions p ON p.appointment_id = a.id
    WHERE a.patient_id = ?
    ORDER BY a.date ASC, a.time ASC
";

$stmt = $conn->prepare($sql_appointments);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$appointments = $stmt->get_result();

// Patient Prescriptions
$sql_prescriptions = "
    SELECT 
        p.id,
        u.username AS doctor_name,
        p.diagnosis,
        p.medicines,
        p.date
    FROM prescriptions p
    JOIN users u ON u.id = p.doctor_id
    WHERE p.patient_id = ?
    ORDER BY p.date DESC
";
$stmt = $conn->prepare($sql_prescriptions);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$prescriptions = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="patient_dashboard.css">
<title>Patient Dashboard</title>
<style>
  
body { margin:0; font-family:Arial; background:#f0f4ff; }

.navbar { background:#1e3a8a; padding:15px; color:white; text-align:center; font-size:22px; font-weight:700; }

.container { width:90%; margin:25px auto; }
.card { background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); margin-bottom:25px; }

h2 { color:#1e3a8a; margin-bottom:15px; }
table { width:100%; border-collapse:collapse; }
td, th { border:1px solid #cbd5e1; padding:10px; }
th { background:#eef2ff; color:#1e3a8a; }
input, select { width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; margin:7px 0; }
.btn { background:#1e3a8a; color:#fff; padding:10px 15px; border:none; border-radius:6px; cursor:pointer; font-weight:600; margin-top:10px; }
.btn:hover { background:#3b82f6; }
</style>
</head>

<body>
<div class="navbar">
  Patient Dashboard
<a href="backend/logout.php?type=patient">
<a href="../backend/logout.php?type=patient">
    <button style="padding:10px 20px; background:red; color:white; border:none; border-radius:5px; margin: 0px 50px 0px 1100px;">
        Logout
    </button>
</a>
</div>
<div class="container">

  <!-- PROFILE -->
  <div class="card">
    <h2>My Profile</h2>
    <p><strong>Patient ID:</strong> <?= $profile['id'] ?></p>
    <p><strong>Name:</strong> <?= htmlspecialchars($profile['username']) ?></p>
  </div>

  <!-- BOOK APPOINTMENT -->
  <div class="card">
    <h2>Book Appointment</h2>
    <form method="POST" action="book.php">
      <label>Choose Doctor</label>
      <select name="doctor" required>
        <option value="">Select a doctor</option>
        <?php while($doc = $doctors->fetch_assoc()) { ?>
          <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['username']) ?> (ID:<?= $doc['id'] ?>)</option>
        <?php } ?>
      </select>

      <label>Date</label>
      <input type="date" name="date" required>

      <label>Time</label>
      <input type="time" name="time" required>

      <button class="btn" type="submit">Book Appointment</button>
    </form>
  </div>

  <!-- MY APPOINTMENTS -->
  <div class="card">
    <h2>My Appointments</h2>
    <table>
      <tr>
        <th>Doctor</th>
        <th>Date</th>
        <th>Time</th>
        <th>Status</th>
      </tr>
      <?php while($app = $appointments->fetch_assoc()) { ?>
      <tr>
        <td><?= htmlspecialchars($app['doctor_name']) ?></td>
        <td><?= $app['date'] ?></td>
        <td><?= $app['time'] ?></td>
        <td><?= ucfirst($app['status']) ?></td>
      </tr>
      <?php } ?>
    </table>
  </div>

  <!-- MY PRESCRIPTIONS -->
  <div class="card">
    <h2>My Prescriptions</h2>
    <table>
      <tr>
        <th>Doctor</th>
        <th>Diagnosis</th>
        <th>Medicines</th>
        <th>Date</th>
      </tr>
      <?php while($pres = $prescriptions->fetch_assoc()) { ?>
      <tr>
        <td><?= htmlspecialchars($pres['doctor_name']) ?></td>
        <td><?= htmlspecialchars($pres['diagnosis']) ?></td>
        <td><?= htmlspecialchars($pres['medicines']) ?></td>
        <td><?= $pres['date'] ?></td>
      </tr>
      <?php } ?>
    </table>
  </div>

</div>
</body>
</html>
