<?php
require 'db_connection.php';
require 'vendor/autoload.php';
require 'UserAuth.php';

$Insertquery = '
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

$Updatequery='
mutation UpdateProduct(
  $input: UpdateProductInput!
  $condition: ModelProductConditionInput
) {
  updateProduct(input: $input, condition: $condition) {
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
$stmt = $pdo->query('SELECT Product_Code,Product_Details,Supplier_Name,Status,AwsProductID FROM products');

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

//提取json中的数据
$productDetails = json_decode($row['Product_Details'], true);
$product_name = isset($productDetails['product_name']) ? $productDetails['product_name'] : null;
$product_is_discontinued = isset($productDetails['product_is_discontinued']) ? $productDetails['product_is_discontinued'] : false;
$full_description = isset($productDetails['full_description']) ? $productDetails['full_description'] : null;
$available_branding = isset($productDetails['available_branding']) ? $productDetails['available_branding'] : null;
$lowest_leadtime = isset($productDetails['lowest_leadtime']) ? $productDetails['lowest_leadtime'] : null;
$keywords = isset($productDetails['keywords']) ? $productDetails['keywords'] : null;
$Feature = isset($productDetails['Feature']) ? $productDetails['Feature'] : null;
$avaliable_leadtime = isset($productDetails['avaliable_leadtime']) ? $productDetails['avaliable_leadtime'] : null;
$short_description = isset($productDetails['short_description']) ? $productDetails['short_description'] : "null";
$keywords = isset($productDetails['keywords']) ? $productDetails['keywords'] : "null";
$availbale_colour = isset($productDetails['availbale_colour']) ? $productDetails['availbale_colour'] : null;
$related_product_code = isset($productDetails['related_product_code']) ? $productDetails['related_product_code'] : "null";
$product_url = isset($productDetails['product_url']) ? $productDetails['product_url'] : "null";
$availableCountry = isset($productDetails['availableCountry']) ? $productDetails['availableCountry'] : "null";
$promo = isset($productDetails['Promo']) ? $productDetails['Promo'] : null;
$available_stock = isset($productDetails['available_stock']) ? $productDetails['available_stock'] : 0;
$lowest_priceAU = isset($productDetails['lowest_price']['lowest_priceAU']) ? $productDetails['lowest_price']['lowest_priceAU'] : 0;
$lowest_priceNZ = isset($productDetails['lowest_price']['lowest_priceNZ']) ? $productDetails['lowest_price']['lowest_priceNZ'] : 0;
$avaliable_moq = isset($productDetails['avaliable_moq']) ? $productDetails['avaliable_moq'] : 0;

$packaging = isset($productDetails['packaging']) ? json_encode($productDetails['packaging']) : "null";
$supplier_categories = isset($productDetails['supplier_categories']) ? json_encode($productDetails['supplier_categories']) : "null";
$colour_pms = isset($productDetails['colour_pms']) ? json_encode($productDetails['colour_pms']) : "null";
$specification = isset($productDetails['specification']) ? json_encode($productDetails['specification']) : "null";
$images = isset($productDetails['images']) ? json_encode($productDetails['images']) : "null";
$shipping_cost = isset($productDetails['shipping_cost']) ? json_encode($productDetails['shipping_cost']) : "null";
$additional_info = isset($productDetails['additional_info']) ? json_encode($productDetails['additional_info']) : "null";
$files = isset($productDetails['files']) ? json_encode($productDetails['files']) : "null";
$pricing = isset($productDetails['pricing']) ? json_encode($productDetails['pricing']) : "null";

$variables = [
  'input' => [
      'supplierID' => '2d4a265b-de20-40c4-82f7-421253a4ec94',
      'categorychildID' => '85b549dd-ba7f-4b5c-b0e2-d5c184c7bf9e',
      'name' => $product_name,
      'code' => $row['Product_Code'],
      'is_discontinued' =>$product_is_discontinued,
      'full_description' =>$full_description,
      'available_branding' =>$available_branding,
      'lowest_leadtime' =>$lowest_leadtime,
      'keywords' =>$keywords,
      'feature_tags' =>$Feature,
      'available_leadtime' =>$avaliable_leadtime,
      'related_product' => $related_product_code,
      'packaging' => $packaging,
      'supplier_categories' => $supplier_categories,
      'short_description' => $short_description,
      'keywords' => $keywords,
      'available_colour' => $availbale_colour,
      'colour_pms' => $colour_pms, 
      'specification' => $specification,
      'images' => $images,
      'shipping_cost' => $shipping_cost,
      'additional_info' => $additional_info,
      'files' => $files,
      'product_url' => $product_url,
      'pricing' => $pricing,
      'available_country' => $availableCountry,
      'promotion_tag' => $promo,
      'available_stock' => $available_stock,
      'lowprice_au' => $lowest_priceAU,
      'lowprice_nz' => $lowest_priceNZ,
      'available_moq' => $avaliable_moq
    ]
];

if ($row['Status'] == 'Insert') {
  $payload = json_encode(['query' => $Insertquery, 'variables' => $variables]);
} elseif ($row['Status'] == 'Updated') {
  $variables2 = [
    'input' => [
      'id' =>  $row['AwsProductID'],
      'name' => $product_name,
      'code' => $row['Product_Code'],
      'is_discontinued' =>$product_is_discontinued,
      'full_description' =>$full_description,
      'available_branding' =>$available_branding,
      'lowest_leadtime' =>$lowest_leadtime,
      'keywords' =>$keywords,
      'feature_tags' =>$Feature,
      'available_leadtime' =>$avaliable_leadtime,
      'related_product' => $related_product_code,
      'packaging' => $packaging,
      'supplier_categories' => $supplier_categories,
      'short_description' => $short_description,
      'keywords' => $keywords,
      'available_colour' => $availbale_colour,
      'colour_pms' => $colour_pms, 
      'specification' => $specification,
      'images' => $images,
      'shipping_cost' => $shipping_cost,
      'additional_info' => $additional_info,
      'files' => $files,
      'product_url' => $product_url,
      'pricing' => $pricing,
      'available_country' => $availableCountry,
      'promotion_tag' => $promo,
      'available_stock' => $available_stock,
      'lowprice_au' => $lowest_priceAU,
      'lowprice_nz' => $lowest_priceNZ,
      'available_moq' => $avaliable_moq
      ]
  ];
 $payload = json_encode(['query' => $Updatequery, 'variables' => $variables2]);
}elseif($row['Status'] == 'Normal'){

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
}

$parsedResponse = json_decode($response, true);

// 如果状态是'Insert'，检查响应中的ID
if (($row['Status'] == 'Insert') && isset($parsedResponse['data']['createProduct']['id'])) {
    $productId = $parsedResponse['data']['createProduct']['id'];
    $productCode = $row['Product_Code'];
  
    // 更新数据库中的AwsProductID列
    $updateStmt = $pdo->prepare("UPDATE products SET AwsProductID = :productId WHERE Product_Code = :productCode");
    $updateStmt->execute([
        ':productId' => $productId,
        ':productCode' => $productCode
    ]);
  
    echo $row['Product_Code'] . " saved successfully with ID: " . $productId . "\n";
} 

}

?>