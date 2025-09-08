<?php // config.php
function getConnection() {
    $servername = "localhost";
    $username = "root"; 
    $password = "";
<<<<<<< HEAD
    $dbname = "hrs";
=======
    $dbname = "maggie_hr";
>>>>>>> 86d68ff94e965ff4593e34aa4e2cc57edde6d5d3

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); 
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>