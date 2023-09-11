<?php
require 'db_connection.php';
require 'vendor/autoload.php';

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
//在IAM创建一个外部调用的key
$client = new CognitoIdentityProviderClient([
    'version' => 'latest',
    'region'  => 'us-east-1', // 你的 AWS 区域
    'credentials' => [
        'key'    => 'AKIA3U7WIJBEFWP3L5MQ',
        'secret' => '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',
    ],
]);
//登陆用户
$result = $client->initiateAuth([
    'AuthFlow' => 'USER_PASSWORD_AUTH',
    'ClientId' => '74pc69s5vnoa0vh5s6u0co2q33',
    'AuthParameters' => [
        'USERNAME' => 'bihonom135@lukaat.com',
        'PASSWORD' => 'wdc20010109',
    ],
]);

//验证用户
$idToken = $result['AuthenticationResult']['IdToken'];



$apiUrl = "https://6brrcx5ltbaq7dqk22zwdq64sq.appsync-api.us-east-1.amazonaws.com/graphql";
$headers = [
    "Content-Type: application/json",
    "Authorization: $idToken"
];

$query = '
mutation CreateProduct(
    $input: CreateProductInput!
    $condition: ModelProductConditionInput
  ) {
    createProduct(input: $input, condition: $condition) {
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
      Inventories {
        nextToken
        __typename
      }
      Decorations {
        nextToken
        __typename
      }
      product_url
      available_country
      pricing
      available_moq
      promotion_tag
      categorychildID
      AddOns {
        nextToken
        __typename
      }
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
  }
';

$pdo = new PDO($dsn, $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->query('SELECT Product_Code,Product_Details,Supplier_Name FROM products WHERE Product_Code = "BOH001"'); // 修改表名和条件
$row = $stmt->fetch();
//提取json中的数据
$productDetails = json_decode($row['Product_Details'], true);
$product_name = isset($productDetails['product_name']) ? $productDetails['product_name'] : null;
$product_is_discontinued = isset($productDetails['product_is_discontinued']) ? $productDetails['product_is_discontinued'] : null;
$full_description = isset($productDetails['full_description']) ? $productDetails['full_description'] : null;
$available_branding = isset($productDetails['available_branding']) ? $productDetails['available_branding'] : null;
$brandingOptions = explode(',', $available_branding);
$brandingOptions = array_map('trim', $brandingOptions);
$lowest_leadtime = isset($productDetails['lowest_leadtime']) ? $productDetails['lowest_leadtime'] : null;
$keywords = isset($productDetails['keywords']) ? $productDetails['keywords'] : null;
$Feature = isset($productDetails['Feature']) ? $productDetails['Feature'] : null;
$avaliable_leadtime = isset($productDetails['avaliable_leadtime']) ? $productDetails['avaliable_leadtime'] : null;

$related_product_code = isset($productDetails['related_product_code']) ? $productDetails['related_product_code'] : "null";
if ($related_product_code === null) {
    $related_product_code = "null";
}

$packaging = isset($productDetails['packaging']) ? json_encode($productDetails['packaging']) : null;

$supplier_categories = isset($productDetails['supplier_categories']) ? json_encode($productDetails['supplier_categories']) : null;
$variables = [
    'input' => [
        'supplierID' => '2d4a265b-de20-40c4-82f7-421253a4ec94',
        'categorychildID' => '85b549dd-ba7f-4b5c-b0e2-d5c184c7bf9e',
        'name' => $product_name,
        'code' => $row['Product_Code'],
        'is_discontinued' =>$product_is_discontinued,
        'full_description' =>$full_description,
        'available_branding' =>$brandingOptions,
        'lowest_leadtime' =>$lowest_leadtime,
        'keywords' =>$keywords,
        'feature_tags' =>$Feature,
        'available_leadtime' =>$avaliable_leadtime,
        'related_product' => $related_product_code,
        'packaging' => $packaging,
        'supplier_categories' => $supplier_categories
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
  exit;
}

//把id和code放到json
$parsedResponse = json_decode($response, true);
if (isset($parsedResponse['data']['createProduct']['id'])) {
  $productId = $parsedResponse['data']['createProduct']['id'];
  $productCode = $row['Product_Code'];
  
  // Check if file exists. If not, create a new empty array
  $filename = "product_ids.json";
  if (file_exists($filename)) {
      $existingData = json_decode(file_get_contents($filename), true);
  } else {
      $existingData = [];
  }
  
  // Add/Update Product_Code and ID
  $existingData[$productCode] = $productId;
  
  // Save back to file
  file_put_contents($filename, json_encode($existingData, JSON_PRETTY_PRINT));
  
  echo "Product ID saved successfully!";
} else {
  echo "Error in creating product or retrieving ID.";
  print_r($parsedResponse);
}
?>