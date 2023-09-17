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

$query2='mutation UpdateInventory(
    $input: UpdateInventoryInput!
    $condition: ModelInventoryConditionInput
  ) {
    updateInventory(input: $input, condition: $condition) {
      id
      name
      code
      colour_hex
      onHand
      onOrder
      productID
      Product {
        id
        code
        name
        related_product
        is_discontinued
        supplier_categories
        short_description
        full_description
        feature_tags
        keywords
        available_colour
        available_branding
        colour_pms
        specification
        packaging
        shipping_cost
        images
        available_leadtime
        lowest_leadtime
        additional_info
        files
        product_url
        available_country
        pricing
        available_moq
        promotion_tag
        categorychildID
        supplierID
        available_stock
        lowprice_au
        lowprice_nz
        lowprice_us
        lowprice_uk
        lowprice_eu
        createdAt
        updatedAt
        owner
        __typename
      }
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
$stmt = $pdo->query('SELECT i.AwsInventoryID, i.Name, i.Code, i.Colour_Hex, i.On_Hand, i.On_Order, i.Product_Code, i.Incoming, i.Avaliable_Country, i.Supplier_Name, i.Colour_Pms, p.Status as Product_Status, p.AwsProductID as ProductID 
                     FROM Inventory i
                     LEFT JOIN Products p ON i.Product_Code = p.Product_Code
');


while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $productStatus = $row['Product_Status'];
    if ($productStatus == 'Insert') {
    $variables = [
    'input' => [
        'name' =>$row['Name'],
        'code' =>$row['Code'],
        'colour_hex' =>$row['Colour_Hex'],
        'onHand' =>$row['On_Hand'],
        'onOrder' =>$row['On_Order'],
        'productID' =>$row['ProductID'],
        'incoming' =>$row['Incoming'],
        'available_country' =>$row['Avaliable_Country'],
        'supplierID' =>'2d4a265b-de20-40c4-82f7-421253a4ec94',
        'colour_pms' =>$row['Colour_Pms']
        ]
    ];
    $payload = json_encode(['query' => $query, 'variables' => $variables]);
    }elseif ($productStatus == 'Updated') {

        $variables2 = [
            'input' => [
                'id' => $row['AwsInventoryID'],
                'name' =>$row['Name'],
                'code' =>$row['Code'],
                'colour_hex' =>$row['Colour_Hex'],
                'onHand' =>$row['On_Hand'],
                'onOrder' =>$row['On_Order'],
                'productID' =>$row['ProductID'],
                'incoming' =>$row['Incoming'],
                'available_country' =>$row['Avaliable_Country'],
                'supplierID' =>'2d4a265b-de20-40c4-82f7-421253a4ec94',
                'colour_pms' =>$row['Colour_Pms']
                ]
            ];
            $payload = json_encode(['query' => $query2, 'variables' => $variables2]);
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
        if (isset($decodedResponse['data']['createInventory']['id'])) {
            $inventoryId = $decodedResponse['data']['createInventory']['id'];

            // Save the inventoryId into Inventory table
            $updateStmt = $pdo->prepare("UPDATE Inventory SET AwsInventoryID = :inventoryId WHERE Code = :code");
            $updateStmt->bindParam(':inventoryId', $inventoryId);
            $updateStmt->bindParam(':code', $row['Code']);
            $updateStmt->execute();

            echo $row['Product_Code'] . "'s inventory is saved successfully with ID: $inventoryId\n";
        } else {
            echo "Failed to save inventory for " . $row['Product_Code'] . ".\n";
        }
    }
}


?>