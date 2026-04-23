<?php
require "auth.php";
require "db.php";
if ($_SESSION['role'] !== "patient") {
    echo "Access denied.";
    exit;
}
$sql = "SELECT id, username FROM users WHERE role='doctor'";
$doctors = $conn->query($sql);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $patient_id = $_SESSION['user_id'];
    $doctor_id = $_POST['doctor'];
    $date = $_POST['date'];
    $time = $_POST['time'];
// Check if the slot is already booked for this doctor
$sql_check = "SELECT id FROM appointments WHERE doctor_id=? AND date=? AND time=?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("iss", $doctor_id, $date, $time);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    echo "<script>alert('This time slot is already booked!'); window.location='patient_dashboard.php';</script>";
    exit;
}

    $insert = "INSERT INTO appointments (patient_id, doctor_id, date, time, status) 
               VALUES (?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($insert);
    $stmt->bind_param("iiss", $patient_id, $doctor_id, $date, $time);

    if ($stmt->execute()) {
        echo "<script>alert('Appointment booked successfully!'); window.location='patient_dashboard.php';</script>";
    } else {
        echo "Error booking appointment.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment</title>
</head>
<body>
    <h2>Book an Appointment</h2>

    <form method="POST" action="">
        <label>Select Doctor:</label>
        <select name="doctor" required>
            <option value="">Choose a doctor</option>
            <?php while ($row = $doctors->fetch_assoc()) { ?>
                <option value="<?php echo $row['id']; ?>">
                    <?php echo $row['username']; ?>
                </option>
            <?php } ?>
        </select>
        <br><br>

        <label>Date:</label>
        <input type="date" name="date" required>
        <br><br>

        <label>Time:</label>
        <input type="time" name="time" required>
        <br><br>

        <button type="submit">Book</button>
    </form>

    <br>
    <a href="patient_dashboard.php">Back to Dashboard</a>
</body>
</html>
