<?php
require 'db_connection.php';


$inventoryData = isset($jsonData['inventory'][0]) ? $jsonData['inventory'] : array($jsonData['inventory']);

foreach ($inventoryData as $item) {
    $itemNumber = isset($item['itemNumber']) ? $item['itemNumber'] : null;
    $itemName = isset($item['itemName']) ? $item['itemName'] : null;
    $colour = isset($item['colour']) ? $item['colour'] : null;
    $onHand = isset($item['onHand']) ? intval($item['onHand']) : null; // 转换为整数
    $onOrder = isset($item['onOrder']) ? intval($item['onOrder']) : null; // 转换为整数
    $incomingStock = isset($item['incomingStock']) ? intval($item['incomingStock']) : null; // 转换为整数
    $productCode = $jsonData['product_code'];
    $supplierName = $jsonData['supplier_code'];

    $sql = 'INSERT INTO Inventory (Code, Name, Colour_Pms, On_Hand, Incoming, Product_Code, Supplier_Name, On_Order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    $values = [$itemNumber, $itemName, $colour, $onHand, $incomingStock, $productCode, $supplierName, $onOrder];

    if ($stmt->execute($values)) {
        echo "Data inserted successfully!";
    } else {
        echo "Error inserting data.";
    }
}

?>

