<?php
require 'db_connection.php';

function insertPBInventory($jsonData,$Status) {

global $pdo;
$inventoryData = isset($jsonData['Inventory'][0]) ? $jsonData['Inventory'] : array($jsonData['Inventory']);

foreach ($inventoryData as $item) {
    // 检查是否满足跳过条件
    if (
        isset($item['InventoryDetails']['itemNumber']) && $item['InventoryDetails']['itemNumber'] === "" &&
        isset($item['InventoryDetails']['itemName']) && $item['InventoryDetails']['itemName'] === "" &&
        $item['InventoryDetails']['colour'] === null &&
        isset($item['InventoryDetails']['onHand']) && $item['InventoryDetails']['onHand'] === "" &&
        $item['InventoryDetails']['onOrder'] === null &&
        isset($item['InventoryDetails']['eta']) && $item['InventoryDetails']['eta'] === ""
    ) {
        continue;
    }

    // 以下是原始的插入逻辑
    $itemNumber = isset($item['InventoryDetails']['itemNumber']) ? $item['InventoryDetails']['itemNumber'] : null;
    $itemName = isset($item['InventoryDetails']['itemName']) ? $item['InventoryDetails']['itemName'] : null;
    $colour = isset($item['InventoryDetails']['colour']) ? $item['InventoryDetails']['colour'] : null;
    $onHand = isset($item['InventoryDetails']['onHand']) ? intval($item['InventoryDetails']['onHand']) : null;
    $onOrder = isset($item['InventoryDetails']['onOrder']) ? intval($item['InventoryDetails']['onOrder']) : null;
    $incomingStock = isset($item['InventoryDetails']['eta']) ? intval($item['InventoryDetails']['eta']) : null;
    $productCode = $jsonData['Product_Code'];
    $supplierName = "PB";

    if ($Status == "Insert") {
        $sql = 'INSERT INTO Inventory (Code, Name, Colour_Pms, On_Hand, Incoming, Product_Code, Supplier_Name, On_Order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $values = [$itemNumber, $itemName, $colour, $onHand, $incomingStock, $productCode, $supplierName, $onOrder];
    } elseif ($Status == "Updated") {
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

