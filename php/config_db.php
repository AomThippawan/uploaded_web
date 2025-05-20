<?php
// $servername = "localhost";
// $username = "root";
// $password = "K@nt@ng11407"; 
// $dbname = "kantang_db";      
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "kantang_db";     
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
?>