<?php
require 'db_connection.php';
require 'vendor/autoload.php';
require 'UserAuth.php';

$query = '
mutation CreateInventory(
    $input: CreateInventoryInput!
    $condition: ModelInventoryConditionInput
  ) {
    createInventory(input: $input, condition: $condition) {
      id
      name
      code
      colour_hex
      onHand
      onOrder
      productID
      incoming
      available_country
      supplierID
      colour_pms
      createdAt
      updatedAt
      owner
      __typename
    }
  }
';


$pdo = new PDO($dsn, $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->query('SELECT i.Name, i.Code, i.Colour_Hex, i.On_Hand, i.On_Order, i.Product_Code, i.Incoming, i.Avaliable_Country, i.Supplier_Name, i.Colour_Pms, p.Status as Product_Status 
                     FROM Inventory i
                     LEFT JOIN Products p ON i.Product_Code = p.Product_Code
');


while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $jsonFile = 'product_ids.json';
    $jsonData = file_get_contents($jsonFile);
    if ($jsonData === false) {
        echo '无法读取 JSON 文件';
    } else {
        $productData = json_decode($jsonData, true);
        if ($productData === null) {
            echo '解码 JSON 数据失败';
        } else {
            // 要查找的键
            $keyToFind = $row['Product_Code'];
            if (array_key_exists($keyToFind, $productData)) {
                $foundid = $productData[$keyToFind];
            } else {
                // 处理未找到的情况，如果需要的话
            }
        }
    }
    $productStatus = $row['Product_Status'];
    if ($productStatus == 'Insert') {
    $variables = [
    'input' => [
        'name' =>$row['Name'],
        'code' =>$row['Code'],
        'colour_hex' =>$row['Colour_Hex'],
        'onHand' =>$row['On_Hand'],
        'onOrder' =>$row['On_Order'],
        'productID' =>$foundid,
        'incoming' =>$row['Incoming'],
        'available_country' =>$row['Avaliable_Country'],
        'supplierID' =>'2d4a265b-de20-40c4-82f7-421253a4ec94',
        'colour_pms' =>$row['Colour_Pms']
        ]
    ];
    $payload = json_encode(['query' => $query, 'variables' => $variables]);
    }else{
        
        continue;
       }


    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "cURL Error: $error";
        continue;
    } else {
        echo $row['Product_Code'] . "'s inventory is saved successfully\n";
    }

}


?>