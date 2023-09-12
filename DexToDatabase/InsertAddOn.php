<?php
require 'db_connection.php';

function insertAddon($jsonData) {

global $pdo;
if (isset($jsonData['addons'])) {
foreach ($jsonData['addons'] as $item) {
   
   
    $itemName = $item['Name'];
    $cost_au = $item['cost_au'];
    $cost_nz = $item['cost_nz'];
  
    $cost = json_encode([
        "cost_au" => $cost_au,
        "cost_nz" => $cost_nz
    ]);
    
    $decorations = []; 
    foreach ($jsonData['decorations'] as $decorationItem) {

        $decorationData = [
            "name" => $itemName,
            "size" => $decorationItem['Size'],
            "cost_au" => $decorationItem['cost_au'],
            "cost_nz" => $decorationItem['cost_nz'],
            "instruction" => null, 
        ];
    
        $decorations[] = $decorationData;
    }
    $decorationsJson = json_encode($decorations); 

    $productCode = $jsonData['product_code'];
    $supplierName = $jsonData['supplier_code'];
    

    $sql = 'INSERT INTO AddOn (Name,Cost,Product_Code,Supplier_Name,Decoration) VALUES (?,?,?,?,?)';
    $stmt = $pdo->prepare($sql);
    $values = [$itemName,$cost,$productCode,$supplierName,$decorationsJson];


    if ($stmt->execute($values)) {
    } else {
        echo "Error inserting data.";
    }
}}
}
?>

