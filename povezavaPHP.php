<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'eucilnica';

$conn = new mysqli($host, $user, $password, $database);

// Preveri povezavo
if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
}

// Dodatno: nastavi UTF-8
$conn->set_charset("utf8");
?>