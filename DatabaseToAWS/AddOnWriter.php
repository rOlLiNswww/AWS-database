<?php
require 'db_connection.php';
require 'vendor/autoload.php';
require 'UserAuth.php';

$query = '
mutation CreateAddOn(
    $input: CreateAddOnInput!
    $condition: ModelAddOnConditionInput
  ) {
    createAddOn(input: $input, condition: $condition) {
      id
      name
      avaliable_country
      cost
      productID
      supplierID
      decoration
      createdAt
      updatedAt
      owner
      __typename
    }
  }
';

$pdo = new PDO($dsn, $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->query('SELECT a.Name, a.Avaliable_Country, a.Cost, a.Product_Code, a.Supplier_Name, a.Decoration, p.Status as Product_Status 
                     FROM AddOn a
                     LEFT JOIN Products p ON a.Product_Code = p.Product_Code
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
        'productID' =>$foundid,
        'avaliable_country' =>$row['Avaliable_Country'],
        'supplierID' =>'2d4a265b-de20-40c4-82f7-421253a4ec94',
        'cost'=>$row['Cost'],
        'decoration'=>$row['Decoration']
        ]
    ];
    $payload = json_encode(['query' => $query, 'variables' => $variables]);
    }
    else{
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
        $decodedResponse = json_decode($response, true);

            if(isset($decodedResponse['data']['createAddOn']['id'])) {
                $addOnID = $decodedResponse['data']['createAddOn']['id'];

                // 在数据库中更新 AwsAddOnID
                $updateSql = 'UPDATE AddOn SET AwsAddOnID = ? WHERE Product_Code = ?';
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([$addOnID, $row['Product_Code']]);
                
                echo $row['Product_Code'] . "'s AddOn with ID " . $addOnID . " is saved successfully\n";
    }
    else {
        echo "Failed to retrieve AddOn ID from the response\n";
    }
}
}
?>