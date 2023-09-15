<?php
require 'DexToDatabase/InsertProduct.php';
require 'DexToDatabase/InsertDecoration.php';
require 'DexToDatabase/InsertInventory.php';
require 'DexToDatabase/InsertAddOn.php';
require_once 'db_connection.php';
require_once 'function.php';

$data = file_get_contents('input.json');
$jsonDataArray = json_decode($data, true);

if (is_array($jsonDataArray)) {
    foreach ($jsonDataArray as $jsonData) {
        $dbLastModified = productCodeExists($jsonData['product_code']);
        $jsonLastUpdated = str_replace('T', ' ', $jsonData['lastUpdated']);
       
        // Check if product_code already exists
        if (!productCodeExists($jsonData['product_code'])){
            insertProduct($jsonData,"Insert");
            insertInventory($jsonData,"Insert");
            insertDecoration($jsonData,"Insert");
            insertAddon($jsonData,"Insert");
            continue;
        }

        if( $dbLastModified != $jsonLastUpdated){
        
            insertProduct($jsonData,"Updated");
            insertInventory($jsonData,"Updated");
            insertDecoration($jsonData,"Updated");
            insertAddon($jsonData,"Updated");
            continue;
        }
    }
}

// //check delete product
// $inputProductCodes = [];
// foreach ($jsonDataArray as $jsonData) {
//     $inputProductCodes[] = $jsonData['product_code'];
// }

// $dbProductCodes = getAllProductCodesFromDatabase($jsonData['supplier_code']);
// $codesToDelete = array_diff($dbProductCodes, $inputProductCodes);


// foreach ($codesToDelete as $code) {
//     echo  $code . " has been delete!\n";
//     deleteProductByCode($code);
// }


?>