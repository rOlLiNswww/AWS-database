<?php
require 'db_connection.php';
require 'vendor/autoload.php';
require 'UserAuth.php';

$query = '
mutation CreateDecoration(
  $input: CreateDecorationInput!
  $condition: ModelDecorationConditionInput
) {
  createDecoration(input: $input, condition: $condition) {
    id
    imprint_type
    imprint_area
    productID
    available_country
    services
    supplierID
    decoration_name
    max_colour
    createdAt
    updatedAt
    owner
    __typename
  }
}
';

$pdo = new PDO($dsn, $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->query('SELECT Product_Code,Decoration_ID,Imprint_Type,Imprint_Area,Avaliable_Country,Services,Supplier_Name,Max_Colour,Decoration_Name FROM Decoration');

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
    $variables = [
    'input' => [
        'imprint_type' =>$row['Imprint_Type'],
        'imprint_area' =>$row['Imprint_Area'],
        'productID' => $foundid,
        'available_country' => json_decode($row['Avaliable_Country'], true),
        'services' => $row['Services'],
        'supplierID' => '2d4a265b-de20-40c4-82f7-421253a4ec94',
        'decoration_name'=>$row['Decoration_Name'],
        'max_colour'=>$row['Max_Colour']
        ]
    ];
    $payload = json_encode(['query' => $query, 'variables' => $variables]);

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
        echo $row['Product_Code'] . "'s decoration is saved successfully\n";
    }

}


?>