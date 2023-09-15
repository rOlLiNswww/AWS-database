<?php
require 'DatabaseToAWS/ProductWriter.php';
require 'DatabaseToAWS/DecorationWriter.php';
require 'DatabaseToAWS/InventoryWriter.php';
require 'DatabaseToAWS/AddOnWriter.php';

$pdo = new PDO($dsn, $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "UPDATE Products SET status = 'Normal'";
$stmt = $pdo->prepare($query);
$stmt->execute();
?>