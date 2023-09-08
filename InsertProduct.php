<?php
require 'db_connection.php';
// 从input.json读取数据
$data = file_get_contents('input.json');
$jsonData = json_decode($data, true);

//读取数据保存为json
$outputData = array();

$outputData['product_code'] = $jsonData['product_code'];
$outputData['product_name'] = $jsonData['product_name'];
$outputData['related_product_code'] = $jsonData['related_product_code'];
$outputData['product_is_discontinued'] = $jsonData['product_is_discontinued'];

if (isset($jsonData['categories'])) {
    $outputData['supplier_categories'] = $jsonData['categories'];
}

$outputData['short_description'] = $jsonData['short_description'];

$outputData['full_description'] = $jsonData['full_description'];

if (isset($jsonData['tag'])) {
    $promoTags = [];
    if (strpos($jsonData['tag'], 'new') !== false) {
        $promoTags[] = 'new';
    }
    if (strpos($jsonData['tag'], 'sale') !== false) {
        $promoTags[] = 'sale';
    }
    if (strpos($jsonData['tag'], 'trending') !== false) {
        $promoTags[] = 'trending';
    }
    if (count($promoTags) > 0) {
        $outputData['Promo'] = implode(', ', $promoTags);
    }
}

if (isset($jsonData['tag'])) {
    $featureTags = [];
    if (strpos($jsonData['tag'], 'eco') !== false) {
        $featureTags[] = 'eco';
    }
    if (strpos($jsonData['tag'], 'full-colour') !== false) {
        $featureTags[] = 'full-colour';
    }
    if (count($featureTags) > 0) {
        $outputData['Feature'] = implode(',', $featureTags);
    }
}

$outputData['keywords'] = $jsonData['keywords'];

$outputData['availbale_colour'] = $jsonData['availbale_colour'];

$outputData['available_branding'] = $jsonData['available_branding'];

if (isset($jsonData['availbale_colour'])) {
    $outputData['colour_pms'] = $jsonData['availbale_colour'];
}

$specification = array();
for ($i = 1; $i <= 4; $i++) {
    $nameKey = "specification_name{$i}";
    $valueKey = "specification_value{$i}";
    if (isset($jsonData[$nameKey])) {
        $specification[$nameKey] = $jsonData[$nameKey];
        $specification[$valueKey] = isset($jsonData[$valueKey]) ? $jsonData[$valueKey] : null;
    }
}
$outputData['specification'] = $specification;

$packaging = array(
    "packaging_type" => isset($jsonData["packaging_type"]) ? $jsonData["packaging_type"] : "",
    "carton_length" => isset($jsonData["carton_length"]) ? $jsonData["carton_length"] : "",
    "carton_width" => isset($jsonData["carton_width"]) ? $jsonData["carton_width"] : "",
    "carton_weight" => isset($jsonData["carton_weight"]) ? $jsonData["carton_weight"] : "",
    "carton_qty" => isset($jsonData["carton_qty"]) ? $jsonData["carton_qty"] : ""
);
$outputData['packaging'] = $packaging;

$shippingCost = array(
    "shipping_au" => isset($jsonData["shipping_per_location_au"]) ? $jsonData["shipping_per_location_au"] : 0,
    "shipping_nz" => isset($jsonData["shipping_per_location_nz"]) ? $jsonData["shipping_per_location_nz"] : 0
);
$outputData['shipping_cost'] = $shippingCost;

// Create images array with tag value added
$imagesWithTags = array_map(function($image) {
    return [
        "name" => $image["name"],
        "tag" => null,
        "colour" => $image["colour"],
        "url" => $image["url"]
    ];
}, $jsonData["images"]);

// Replace images array in output JSON
$outputData['images'] = $imagesWithTags;

$additional_info = [
    "price_disclaimer" => isset($jsonData["price_disclaimer"]) ? $jsonData["price_disclaimer"] : "",
    "freight_disclaimer_au" => isset($jsonData["freight_disclaimer_au"]) ? $jsonData["freight_disclaimer_au"] : "",
    "freight_disclaimer_nz" => isset($jsonData["freight_disclaimer_nz"]) ? $jsonData["freight_disclaimer_nz"] : "",
    "additional_info" => isset($jsonData["additional_info"]) ? $jsonData["additional_info"] : "",
    "change_log_au" => isset($jsonData["change_log_au"]) ? $jsonData["change_log_au"] : "",
    "change_log_nz" => isset($jsonData["change_log_nz"]) ? $jsonData["change_log_nz"] : ""
];

$outputData['additional_info'] = $additional_info;


$files = array_map(function($file) {
    $tag = null;
    if ($file["name"] === "ProductLineDrawing") {
        $tag = "Line Drawing";
    } elseif ($file["name"] === "ProductCertificate") {
        $tag = "Certificate";
    }

    return [
        "name" => $file["name"],
        "tag" => $tag,
        "url" => $file["url"]
    ];
}, $jsonData["files"]);

// Replace files array in jsonData
$outputData['files'] = $files;

$outputData['product_url'] = $jsonData['product_url'];


$newPricetableAU = array_map(function ($entry) {
    $entry['country'] = 'AU';
    $entry['instruction'] = '';
    return $entry;
}, $jsonData['pricetable_au']);

$newPricetableNZ = null;
if (isset($jsonData['pricetable_nz'])) {
    $newPricetableNZ = array_map(function ($entry) {
        $entry['country'] = 'NZ';
        $entry['instruction'] = '';
        return $entry;
    }, $jsonData['pricetable_nz']);
}

// Update pricetable arrays in the output JSON
$outputData['AU'] = $newPricetableAU;
if ($newPricetableNZ) {
    $outputData['NZ'] = $newPricetableNZ;
}

$lowestPriceAU = null;
foreach ($jsonData['pricetable_au'] as $price) {
    for ($i = 9; $i >= 1; $i--) {
        $priceKey = 'price' . $i;
        if (!empty($price[$priceKey])) {
            $lowestPriceAU = $price[$priceKey];
            break;
        }
    }
    if ($lowestPriceAU !== null) {
        break;
    }
}

$lowestPriceNZ = null;
if (isset($jsonData['pricetable_nz'])) {
    foreach ($jsonData['pricetable_nz'] as $price) {
        for ($i = 9; $i >= 1; $i--) {
            $priceKey = 'price' . $i;
            if (!empty($price[$priceKey])) {
                $lowestPriceNZ = $price[$priceKey];
                break;
            }
        }
        if ($lowestPriceNZ !== null) {
            break;
        }
    }
}

$lowestPrice = [
    "lowest_priceAU" => $lowestPriceAU,
    "lowest_priceNZ" => $lowestPriceNZ
];

$outputData['lowest_price'] = $lowestPrice;


$hasAUPricing = array_key_exists("pricetable_au", $jsonData);
$hasNZPricing = array_key_exists("pricetable_nz", $jsonData);


// Determine available_leadtime based on pricing data
$availableCountry = $hasNZPricing ? "AU, NZ" : ($hasAUPricing ? "AU" : "");

// Add available_leadtime to the output JSON
$outputData['availableCountry'] = $availableCountry;


$outputDataJson = json_encode($outputData, JSON_UNESCAPED_SLASHES);

// 将处理过的数据插入到数据库
$sql = 'INSERT INTO Products (Product_Code, Product_Details,Supplier_Name) VALUES (?,?,?)';
$stmt = $pdo->prepare($sql);
$values = [$jsonData["product_code"],$outputDataJson, $jsonData["supplier_code"]];


if ($stmt->execute($values)) {
    echo "Data inserted successfully!";
} else {
    echo "Error inserting data.";
}

?>