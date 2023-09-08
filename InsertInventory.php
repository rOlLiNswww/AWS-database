<?php
require 'db_connection.php';

foreach ($jsonData['inventory'] as $item) {
   
    $itemNumber = $item['itemNumber'];
    $itemName = $item['itemName'];
    $colour = $item['colour'];
    $onHand = $item['onHand'];
    $onOrder = $item['onOrder'];
    $incomingStock = $item['incomingStock'];
    $productCode = $jsonData['product_code'];
    $supplierName = $jsonData['supplier_code'];




    // 将处理过的数据插入到数据库
    $sql = 'INSERT INTO Inventory (Code, Name, Colour_Pms, On_Hand, Incoming, Product_Code,Supplier_Name,On_Order) VALUES (?,?,?,?,?,?,?,?)';
    $stmt = $pdo->prepare($sql);
    $values = [$itemNumber, $itemName, $colour, $onHand, $incomingStock, $productCode, $supplierName,$onOrder];


    if ($stmt->execute($values)) {
    } else {
        echo "Error inserting data.";
    }
}

?>

