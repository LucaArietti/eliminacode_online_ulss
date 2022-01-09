<?php

require 'common.php';


// parametri per connettersi al db
$servername = get_servername();
$username = get_username();
$password = get_password();
$dbname = get_dbname();



// recupero il numero di tabellone attuale

$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$stmt=$conn->prepare("SELECT tabellone FROM coda_main WHERE codice=UPPER(?)");
$stmt->bind_param('s', $_GET['codice']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$tabellone = $row['tabellone'];

echo $tabellone;
