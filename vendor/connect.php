<?php
$server = "localhost";
$name = "root";
$password = '';
$dbname = "CafeFinder";

$connect = new mysqli($server, $name, $password, $dbname);
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}
?>
