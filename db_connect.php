<?php
// db_connect.php

$servername = "localhost"; 
$username = "u143688490_satsu"; 
$password = "Satsu_221"; 
$dbname = "u143688490_satsu";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>