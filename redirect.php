<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

switch ($_SESSION['role']) {
    case 'Admin':
        header("Location: admin_dashboard.php");
        break;
    case 'Hospital':
        header("Location: hospital_dashboard.php");
        break;
    case 'Donor':
        header("Location: donor_dashboard.php");
        break;
    default:
        header("Location: login.php");
        break;
}
exit();
?>
