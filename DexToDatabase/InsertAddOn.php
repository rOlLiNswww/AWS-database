<?php
require 'db_connection.php';

function insertAddon($jsonData, $Status) {

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
    

    if ($Status == "Insert") {
        $sql = 'INSERT INTO AddOn (Name, Cost, Product_Code, Supplier_Name, Decoration) VALUES (?,?,?,?,?)';
        $stmt = $pdo->prepare($sql);
        $values = [$itemName, $cost, $productCode, $supplierName, $decorationsJson];
    } elseif ($Status == "Updated") {
        $sql = 'UPDATE AddOn SET Cost = ?, Decoration = ? WHERE Name = ? AND Product_Code = ?';
        $stmt = $pdo->prepare($sql);
        $values = [$cost, $decorationsJson, $itemName, $productCode];
    } else {
        // Unknown status
        continue;
    }


    if ($stmt->execute($values)) {
    } else {
        echo "Error inserting data.";
    }
}}
}
?>

