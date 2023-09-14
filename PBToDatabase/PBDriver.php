<?php
require 'PBToDatabase/InsertPBProduct.php';
require 'PBToDatabase/InsertPBInventory.php';
require 'PBToDatabase/InsertPBDecoration.php';

$data = file_get_contents('inputPB.json');
$jsonDataArray = json_decode($data, true);

if (is_array($jsonDataArray)) {
    foreach ($jsonDataArray as $jsonData) {
        insertPBProduct($jsonData);
        insertPBInventory($jsonData);
        insertPBDecoration($jsonData);
    }
}

?>