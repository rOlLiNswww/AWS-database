<?php
require 'PBToDatabase/InsertPBProduct.php';
require 'PBToDatabase/InsertPBInventory.php';
require 'PBToDatabase/InsertPBDecoration.php';
require_once 'db_connection.php';
require_once 'function.php';

$data = file_get_contents('inputPB.json');
$jsonDataArray = json_decode($data, true);


if (is_array($jsonDataArray)) {
    foreach ($jsonDataArray as $jsonData) {
        $dbLastModified = productCodeExists($jsonData['Product_Code']);
        $date = new DateTime($jsonData['Last_Modified']);
        $timezone = new DateTimeZone('Australia/Melbourne');  
        $date->setTimezone($timezone);
        $jsonLastUpdated = $date->format('Y-m-d H:i:s'); 
       
        if (!productCodeExists($jsonData['Product_Code'])){
            insertPBProduct($jsonData,"Insert");
            insertPBInventory($jsonData,"Insert");
            insertPBDecoration($jsonData,"Insert");
            continue;
        }

        if( $dbLastModified != $jsonLastUpdated){
            insertPBProduct($jsonData,"Updated");
            insertPBInventory($jsonData,"Updated");
            insertPBDecoration($jsonData,"Updated");
            continue;
        }
    }
}


?>