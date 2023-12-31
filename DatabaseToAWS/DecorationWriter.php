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

$query2 = '
mutation UpdateDecoration(
    $input: UpdateDecorationInput!
    $condition: ModelDecorationConditionInput
  ) {
    updateDecoration(input: $input, condition: $condition) {
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
$stmt = $pdo->query('SELECT d.AwsDecorationID, d.Product_Code, d.Decoration_ID, d.Imprint_Type, d.Imprint_Area, d.Avaliable_Country, d.Services, d.Supplier_Name, d.Max_Colour, d.Decoration_Name, p.Status as Product_Status ,p.AwsProductID as ProductID
                     FROM Decoration d
                     LEFT JOIN Products p ON d.Product_Code = p.Product_Code');

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $productStatus = $row['Product_Status'];
   
    if ($productStatus == 'Insert') {
        $variables = [
            'input' => [
                'imprint_type' =>$row['Imprint_Type'],
                'imprint_area' =>$row['Imprint_Area'],
                'productID' => $row['ProductID'],
                'available_country' => json_decode($row['Avaliable_Country'], true),
                'services' => $row['Services'],
                'supplierID' => '2d4a265b-de20-40c4-82f7-421253a4ec94',
                'decoration_name'=>$row['Decoration_Name'],
                'max_colour'=>$row['Max_Colour']
                ]
            ];
            $payload = json_encode(['query' => $query, 'variables' => $variables]);
        
    } elseif ($productStatus == 'Updated'){

      
      $variables2 = [
        'input' => [
            'id' => $row['AwsDecorationID'],
            'imprint_type' =>$row['Imprint_Type'],
            'imprint_area' =>$row['Imprint_Area'],
            'productID' => $row['ProductID'],
            'available_country' => json_decode($row['Avaliable_Country'], true),
            'services' => $row['Services'],
            'supplierID' => '2d4a265b-de20-40c4-82f7-421253a4ec94',
            'decoration_name'=>$row['Decoration_Name'],
            'max_colour'=>$row['Max_Colour']
            ]
        ];
        $payload = json_encode(['query' => $query2, 'variables' => $variables2]);
    
    
    }
    else{continue;}

   
 
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
      if (isset($decodedResponse['data']['createDecoration']['id'])) {
          $decorationId = $decodedResponse['data']['createDecoration']['id'];

          // Save the decorationId into Decoration table
          $updateStmt = $pdo->prepare("UPDATE Decoration SET AwsDecorationID = :decorationId WHERE Decoration_ID = :decorationID");
          $updateStmt->bindParam(':decorationId', $decorationId);
          $updateStmt->bindParam(':decorationID', $row['Decoration_ID']);
          $updateStmt->execute();

          echo $row['Product_Code'] . "'s decoration is saved successfully with ID: $decorationId\n";
      } else {
          echo "Failed to save decoration for " . $row['Product_Code'] . ".\n";
      }
  }
}

?>