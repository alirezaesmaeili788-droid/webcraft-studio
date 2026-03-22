<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "studyspot";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Verbindung fehlgeschlagen: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
