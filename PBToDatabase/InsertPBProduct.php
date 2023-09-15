<?php
require 'db_connection.php';


function insertPBProduct($jsonData) {

global $pdo;

//读取数据保存为json
$outputData = array();

$outputData['product_code'] = $jsonData['Product_Code'];
$outputData['product_name'] = $jsonData['Name'];
$outputData['related_product_code'] = ($jsonData['Linked_Product'] === "") ? "null" : $jsonData['Linked_Product'];
$outputData['product_is_discontinued'] = false;

$result = [];
$index = 1;
foreach ($jsonData['Category'] as $category) {
    // 如果存在子分类
    if (isset($category['Child_Category']) && !empty($category['Child_Category'])) {
        foreach ($category['Child_Category'] as $child) {
            $result["category$index"] = $category['Category_Name'] . "/" . $child['Category_Name'];
            $index++;
        }
    } else {
        // 没有子分类
        $result["category$index"] = $category['Category_Name'];
        $index++;
    }
}

$outputData['supplier_categories'] = $result;

$parts = explode(' / ', $jsonData["APPA_Categories"]);

$outputData['categorychild'] = end($parts);

$outputData['short_description'] = null;

$outputData['full_description'] = $jsonData['Description'];

//tag
$promoList = [];
$featureList = [];

if ($jsonData["Is_Trending"] == 1) {
    $promoList[] = "Trending";
}
if ($jsonData["Is_New"] == 1) {
    $promoList[] = "New";
}
if ($jsonData["Is_Sale"] == 1) {
    $promoList[] = "Sale";
}

if ($jsonData["Is_Eco"] == 1) {
    $featureList[] = "Eco";
}

$Promo = implode(",", $promoList);
$Feature = implode(",", $featureList);

$outputData['Promo'] = $Promo;
$outputData['Feature'] = $Feature;

//keywords
$dom = new DOMDocument;
$dom->loadHTML($jsonData['Description']);
$paragraphs = $dom->getElementsByTagName('p');
$lastParagraph = $paragraphs->item($paragraphs->length - 1);
$lastParagraphText = trim($lastParagraph->textContent);

$keywords = null;

if (strpos($lastParagraphText, 'Keywords:') === 0) {
    $keywordsText = trim(substr($lastParagraphText, strlen('Keywords:')));
    $keywords = explode(', ', $keywordsText);
}

$outputData['keywords'] = $keywords;

$availableColour = $jsonData['Colour'];

$Colour = [];
if ($availableColour === "Natural Bamboo and Borosilicate Glass") {
    $Colour = ["Natural", "Glass"];
} else {
    $Colour = explode(',', $availableColour);
    $Colour = array_map('trim', $Colour);
}

// Transform each element to uppercase, replace spaces with underscores, and convert "GRAY" to "GREY"
$Colour = array_map(function ($color) {
    $color = str_replace(' ', '_', strtoupper($color));
    $color = str_replace('GRAY', 'GREY', $color);

    if (strpos($color, 'BRASS') !== false) {
        $color = str_replace('BRASS', 'BRONZE', $color);
    }
    
    return $color;
}, $Colour);


$outputData['availbale_colour'] = $Colour;

$branding = $jsonData['Branding'];
$brandingArray = explode(' | ', $branding);

$processedArray = array_map(function ($item) {
    return str_replace(' ', '_', strtoupper($item));
}, $brandingArray);

$outputData['available_branding'] = $processedArray;

$outputData['colour_pms'] = $jsonData['Colour'];

//specification
$specification = array();
for ($i = 1; $i <= 4; $i++) {
    $nameKey = "Detail_Name_$i";
    $valueKey = "Detail_Description_$i";

    $name = $jsonData[$nameKey];
    $value = $jsonData[$valueKey];

    if ($i > 3) {
        break;
    }

    $specification["specification_name$i"] = !empty($name) ? $name : "";
    $specification["specification_value$i"] = !empty($value) ? $value : "";
}

$outputData['specification'] = $specification;


$packaging = array(
    "packaging_type" => isset($jsonData["Packing"]) ? $jsonData["Packing"] : "",
    "carton_length" => isset($jsonData["Carton_Length"]) ? (string)$jsonData["Carton_Length"] : "",
    "carton_width" => isset($jsonData["Carton_Width"]) ? (string)$jsonData["Carton_Width"] : "",
    "carton_height" => isset($jsonData["Carton_Height"]) ? (string)$jsonData["Carton_Height"] : "",
    "carton_weight" => isset($jsonData["Carton_Weight"]) ? (string)$jsonData["Carton_Weight"] : "",
    "carton_qty" => isset($jsonData["Qty_per_Carton"]) ? (string)$jsonData["Qty_per_Carton"] : ""
);

$outputData['packaging'] = $packaging;

$shippingCost = array(
    "shipping_au" => isset($jsonData["Split_Delivery"]) ? $jsonData["Split_Delivery"] : 0,
    "shipping_nz" => isset($jsonData["Split_Delivery"]) ? $jsonData["Split_Delivery"] : 0
);
$outputData['shipping_cost'] = $shippingCost;

// Create images array with tag value added
foreach ($jsonData["Product_Images"] as $name => $imageArray) {
    if (is_array($imageArray)) {
        $tagName = str_replace(["product", "Images"], "", $name);
        foreach ($imageArray as $imageDetails) {
            $images[] = [
                "name" => $name,
                "tag" => $tagName,
                "colour" => null,
                "url" => $imageDetails["mediaItemUrl"]
            ];
        }
    }
}

$outputData['images'] = $images;

//leadtime

$results = [];
$minDays = PHP_INT_MAX; // 初始化为最大的整数

foreach ($jsonData["Hightlights"] as $highlight) {
    $highlightText = $highlight["Highlights"];

    // 如果是24 Hours，先替换成1 Day
    if ($highlightText == "24 Hours Service") {
        $highlightText = "1 Day Service";
    }

    // 使用正则表达式匹配数字
    if (preg_match("/(\d+)\s*Days?/", $highlightText, $matches)) {
        $dayNumber = intval($matches[1]);
        $results[] = "EQ" . $dayNumber . "D";

        // 更新最小值
        if ($dayNumber < $minDays) {
            $minDays = $dayNumber;
        }
    }
}

$outputData['avaliable_leadtime'] = $results;

$outputData['lowest_leadtime'] = $minDays;

$additional_info = [
    "price_disclaimer" => isset($jsonData["Price_Disclaimer"]) ? $jsonData["Price_Disclaimer"] : "",
    "freight_disclaimer_au" => null,
    "freight_disclaimer_nz" => null,
    "additional_info" => null,
    "change_log_au" => null,
    "change_log_nz" => null
];

$outputData['additional_info'] = $additional_info;

//files


$files = [];

foreach ($jsonData["Product_Files"] as $key => $fileData) {
    if ($fileData !== null) {
        $item = [];

        // 为'name'字段设置值
        $capitalizedKey = ucfirst($key);
        $name = str_replace("File", "", $capitalizedKey);
        $item['name'] = $name;

        // 根据$key来设置'tag'
        switch ($key) {
            case 'productLineDrawingFile':
                $item['tag'] = 'Line Drawing';
                break;

            case 'productGuideFile':
                $item['tag'] = 'Guide';
                break;

            case 'productCertificateFile':
                $item['tag'] = 'Certificate';
                break;
            
            default:
                $item['tag'] = $name;
        }

        $item['url'] = $fileData['mediaItemUrl'];

        $files[] = $item;
    }
}


$outputData['files'] = $files;

$outputData['product_url'] = $jsonData['Product_Link'];



$pricing = [];
$minPrice = 100000; // Start with the maximum possible integer value

if (isset($jsonData['Product_Price_Table']["Product_Price_table4"])) {
    $table = $jsonData['Product_Price_Table']["Product_Price_table4"];
    $newStructure = [];

    $newStructure['description'] = $table['productPricetable4Des'];
    $newStructure['instruction'] = $table['productPricetable4Note'];

    $newStructure['moq'] = null;
    $newStructure['moq_surcharge'] = null;

    $newStructure['lowest_price'] = null;

    for ($i = 1; $i <= 9; $i++) {
        $qtyKey = 'productPricetable4Qty' . $i;
        $priceKey = 'productPricetable4Price' . $i;

        if (isset($table[$qtyKey]) && $table[$qtyKey] !== null) {
            $newStructure['qty' . $i] = $table[$qtyKey];
            $newStructure['price' . $i] = $table[$priceKey];
            
            // Check if the current price is the lowest
            if($table[$priceKey] < $minPrice) {
                $minPrice = $table[$priceKey];
            }
        } else {
            $newStructure['qty' . $i] = "";
            $newStructure['price' . $i] = "";
        }
    }

    // Assign the lowest price to the 'lowest_price' key
    $newStructure['lowest_price'] = $minPrice == PHP_INT_MAX ? null : $minPrice;
    $pricing['AU'] = [$newStructure];
}

$outputData['pricing'] = $pricing;



$minOnHand = 100000; // Start with the maximum possible integer value

foreach ($jsonData['Inventory'] as $item) {
    if (isset($item['InventoryDetails']['onHand'])) {
        $cleanedValue = str_replace([',', '+'], '', $item['InventoryDetails']['onHand']);
        $onHandValue = intval($cleanedValue);
        if ($onHandValue < $minOnHand) {
            $minOnHand = $onHandValue;
        }
    }
}
$outputData['available_stock'] = $minOnHand;



$lowestPrice = [
    "lowest_priceAU" => $minPrice,
    "lowest_priceNZ" => null
];

$outputData['lowest_price'] = $lowestPrice;

$outputData['avaliable_moq'] = null;

$outputData['availableCountry'] = "AU";

$outputDataJson = json_encode($outputData, JSON_UNESCAPED_SLASHES);

$data = $jsonData["Last_Modified"];
$date = new DateTime($data);
$timezone = new DateTimeZone('Australia/Melbourne');
$date->setTimezone($timezone);
$formattedDate = $date->format('Y-m-d H:i:s'); 


// 将处理过的数据插入到数据库
$sql = 'INSERT INTO Products (Product_Code, Product_Details,Supplier_Name,Last_Modified) VALUES (?,?,?,?)';
$stmt = $pdo->prepare($sql);
$values = [$jsonData["Product_Code"],$outputDataJson, "PB",$formattedDate];


if ($stmt->execute($values)) {
    echo  $jsonData["Product_Code"] . " inserted successfully!\n";
}
 else {
    echo "Error inserting data.";
}
}


function convertCategories($categories, $parentCategory = "") {
    $result = [];
    $allowedCategoryIds = [1, 2, 3, 4]; // 允许的类别 ID

    foreach ($categories as $category) {
        $categoryId = $category["Category_ID"];
        $categoryName = $category["Category_Name"];
        $fullCategoryName = $parentCategory ? "$parentCategory/$categoryName" : $categoryName;

        // 检查是否在允许的类别 ID 范围内
        if (in_array($categoryId, $allowedCategoryIds)) {
            $result["category$categoryId"] = $fullCategoryName;
        }

        if (isset($category["Child_Category"])) {
            $childCategories = $category["Child_Category"];
            $childResult = convertCategories($childCategories, $fullCategoryName);
            $result = array_merge($result, $childResult);
        }
    }

    return $result;
}
?>