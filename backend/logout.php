<?php
session_start();
session_unset();
session_destroy();

// لو الدكتور → رجّعه على login.php
if (isset($_GET['type']) && $_GET['type'] === 'doctor') {
    header("Location: /hospital/login.html");
    exit();
}

// لو مريض → رجّعه على الصفحة الرئيسية
header("Location: /hospital/index.php");
exit();
?>
