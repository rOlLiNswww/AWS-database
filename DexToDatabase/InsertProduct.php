<?php
require 'db_connection.php';


function insertProduct($jsonData) {

global $pdo;

//读取数据保存为json
$outputData = array();

$outputData['product_code'] = $jsonData['product_code'];
$outputData['product_name'] = $jsonData['product_name'];
$outputData['related_product_code'] = $jsonData['related_product_code'];
$outputData['product_is_discontinued'] = $jsonData['product_is_discontinued'];

if (isset($jsonData['categories'])) {
    $outputData['supplier_categories'] = $jsonData['categories'];
}



$parts = explode(' / ', $jsonData["appa_categories"]);
$category = end($parts);
$outputData['categorychild'] = $category;

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
    } else {
        $outputData['Promo'] = null;
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
    }else {
        $outputData['Feature'] = null;
    }
}

$outputData['keywords'] = $jsonData['keywords'];

$availableColour = $jsonData['availbale_colour'];

if ($availableColour === "Range of Colours" || strpos($jsonData['tag'], 'full-colour') !== false) {
    $outputData['availbale_colour'] = [
        "WHITE",
        "YELLOW",
        "ORANGE",
        "RED",
        "PURPLE",
        "PINK",
        "GREEN",
        "BLUE",
        "BLACK"
    ];
    
}  else {
    $pattern = '/\([^)]*\)|\|/';
    $cleanedColour = preg_replace($pattern, '|', $availableColour);
    $colourArray = array_filter(explode("|", $cleanedColour), 'trim'); 

    // 去除可能的空值
    $colourArray = array_filter($colourArray, function($value) { return trim($value) !== ''; });

    // 将颜色名称全部大写并替换空格为下划线
    $outputData['availbale_colour'] = array_values(array_map(function ($colour) {
        return str_replace(' ', '_', strtoupper(trim($colour)));
    }, $colourArray));


}


$availableBranding = $jsonData['available_branding'];
if ($availableBranding === "") {
    $availableBranding = "SCREEN_PRINT";
}
if (strpos($availableBranding, "Direct Digital") !== false) {
    $availableBranding = str_replace("Direct Digital", "Digital Direct", $availableBranding);
}
$availableBranding = str_replace(' ', '_', strtoupper($availableBranding));
$outputData['available_branding'] = $availableBranding;


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

$leadtimes = [];

foreach ($jsonData['decorations'] as $decoration) {
    $leadtimeAU = $decoration['leadtime_au'];
    
    // 处理 "8-10 Weeks" 的情况
    if ($leadtimeAU === "8-10 Weeks") {
        $leadtimeAU = "EQ8W";
    } else {
        $leadtimeAU = preg_replace('/(\d+)\s*(\w+)/', 'EQ$1' . (strpos($leadtimeAU, 'Hours') !== false ? 'H' : 'D'), $leadtimeAU);
        $leadtimeAU = str_replace(' Days', '', $leadtimeAU);
    }

    if (!in_array($leadtimeAU, $leadtimes)) {
        $leadtimes[] = $leadtimeAU;
    }
}

$outputData['avaliable_leadtime'] = $leadtimes;

$convertedLeadtimes = array_map(function ($leadtime) {
    $leadtime = str_replace('8W', '32D', $leadtime);
    $leadtime = str_replace('24H', '1D', $leadtime); 
    return $leadtime;
}, $leadtimes);


$numericValues = array_map(function ($leadtime) {
    return (int)str_replace(['EQ', 'D'], '', $leadtime);
}, $convertedLeadtimes);

$minValue = min($numericValues);
$outputData['lowest_leadtime'] = $minValue;




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
$outputData['pricing']['AU'] = $newPricetableAU;
if ($newPricetableNZ) {
    $outputData['pricing']['NZ'] = $newPricetableNZ;
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

$onHandValues = array_column($jsonData['inventory'], 'onHand');

// 找到最小的 "onHand" 值
if (!empty($onHandValues)) {
    $minOnHand = min($onHandValues);
    $minOnHand = round($minOnHand);  // 四舍五入到最近的整数
} else {
    $minOnHand = 0; // 设置一个默认最小值
}

$outputData['available_stock'] = $minOnHand;



$lowestPrice = [
    "lowest_priceAU" => $lowestPriceAU,
    "lowest_priceNZ" => $lowestPriceNZ
];



$outputData['lowest_price'] = $lowestPrice;



$hasAUPricing = array_key_exists("pricetable_au", $jsonData);
$hasNZPricing = array_key_exists("pricetable_nz", $jsonData);



// Determine available_leadtime based on pricing data
$availableCountry = $hasNZPricing ? ["AU", "NZ"] : ($hasAUPricing ? "AU" : "");

// Add available_leadtime to the output JSON
$outputData['availableCountry'] = $availableCountry;


$outputDataJson = json_encode($outputData, JSON_UNESCAPED_SLASHES);

// 将处理过的数据插入到数据库
$sql = 'INSERT INTO Products (Product_Code, Product_Details,Supplier_Name) VALUES (?,?,?)';
$stmt = $pdo->prepare($sql);
$values = [$jsonData["product_code"],$outputDataJson, $jsonData["supplier_code"]];


if ($stmt->execute($values)) {
    echo  $jsonData["product_code"] . " inserted successfully!\n";
}
 else {
    echo "Error inserting data.";
}
}
?>