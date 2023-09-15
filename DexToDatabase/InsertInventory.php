<?php
require 'db_connection.php';

function insertInventory($jsonData,$Status) {

global $pdo;
$inventoryData = isset($jsonData['inventory'][0]) ? $jsonData['inventory'] : array($jsonData['inventory']);

foreach ($inventoryData as $item) {
    // 检查是否满足跳过条件
    if (
        isset($item['itemNumber']) && $item['itemNumber'] === "" &&
        isset($item['itemName']) && $item['itemName'] === "" &&
        $item['colour'] === null &&
        isset($item['onHand']) && $item['onHand'] === "" &&
        $item['onOrder'] === null &&
        isset($item['incomingStock']) && $item['incomingStock'] === ""
    ) {
        continue;
    }

    // 以下是原始的插入逻辑
    $itemNumber = isset($item['itemNumber']) ? $item['itemNumber'] : null;
    $itemName = isset($item['itemName']) ? $item['itemName'] : null;
    $colour = isset($item['colour']) ? $item['colour'] : null;
    $onHand = isset($item['onHand']) ? intval($item['onHand']) : null;
    $onOrder = isset($item['onOrder']) ? intval($item['onOrder']) : null;
    $incomingStock = isset($item['incomingStock']) ? intval($item['incomingStock']) : null;
    $productCode = $jsonData['product_code'];
    $supplierName = $jsonData['supplier_code'];

    
        if ($Status == "Insert") {
            $sql = 'INSERT INTO Inventory (Code, Name, Colour_Pms, On_Hand, Incoming, Product_Code, Supplier_Name, On_Order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
            $stmt = $pdo->prepare($sql);
            $values = [$itemNumber, $itemName, $colour, $onHand, $incomingStock, $productCode, $supplierName, $onOrder];
        } elseif ($Status == "Updated") {
            // 我们需要知道基于哪个字段或哪组字段进行更新，这里我默认使用Product_Code和Code (itemNumber)
            $sql = 'UPDATE Inventory SET Name = ?, Colour_Pms = ?, On_Hand = ?, Incoming = ?, Supplier_Name = ?, On_Order = ? WHERE Code = ? AND Product_Code = ?';
            $stmt = $pdo->prepare($sql);
            $values = [$itemName, $colour, $onHand, $incomingStock, $supplierName, $onOrder, $itemNumber, $productCode];
        } else {
            // 可能是未知的状态，所以我们终止循环
            continue;
        }

    if ($stmt->execute($values)) {
    } else {
        echo "Error inserting data.";
    }
}

}
?>

