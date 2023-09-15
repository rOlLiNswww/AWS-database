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
        // Check if product_code already exists
        if (!productCodeExists($jsonData['Product_Code'])) {
            insertPBProduct($jsonData);
            insertPBInventory($jsonData);
            insertPBDecoration($jsonData);
        }
    }
}


if (is_array($jsonDataArray)) {
    foreach ($jsonDataArray as $jsonData) {
        $dbLastModified = productCodeExists($jsonData['Product_Code']);
        $date = new DateTime($jsonData['Last_Modified']);
        $timezone = new DateTimeZone('Australia/Melbourne');  
        $date->setTimezone($timezone);
        $jsonLastUpdated = $date->format('Y-m-d H:i:s'); 
       
        // Check if product_code already exists
        if (!productCodeExists($jsonData['Product_Code']) || $dbLastModified != $jsonLastUpdated){
            if (productCodeExists($jsonData['Product_Code'])) {
                deleteProductByCode($jsonData['Product_Code']);
            }
            insertPBProduct($jsonData);
            insertPBInventory($jsonData);
            insertPBDecoration($jsonData);
        }
    }
}


//check delete product
$inputProductCodes = [];
foreach ($jsonDataArray as $jsonData) {
    $inputProductCodes[] = $jsonData['Product_Code'];
}

$dbProductCodes = getAllProductCodesFromDatabase('PB');
$codesToDelete = array_diff($dbProductCodes, $inputProductCodes);

foreach ($codesToDelete as $code) {
    echo  $code . " has been delete!\n";
    deleteProductByCode($code);
}

?>