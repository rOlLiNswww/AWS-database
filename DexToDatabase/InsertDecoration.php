<?php
require 'db_connection.php';

// 初始化
$decorations = isset($jsonData['decorations']) ? $jsonData['decorations'] : array();
$groupedByNames = array();
$availableBranding = isset($jsonData['available_branding']) ? $jsonData['available_branding'] : null;
if ($availableBranding === "") {
    $availableBranding = "Screen Print";
}
$imprintTypes = $availableBranding ? array_map('trim', explode(',', $availableBranding)) : array();

foreach ($decorations as $decoration) {
    $decorationName = $decoration['Name'];

    // 从第一段代码中获取名称处理
    $words = explode(' ', $decorationName);
    if (count($words) > 3) {
        $decorationName = implode(' ', array_slice($words, -3));
    }

    $Imprint_Area = $decoration['Size'];
    $Product_Code = $jsonData['product_code'];
    $Supplier_Name = $jsonData['supplier_code'];
    $imprintType = array_shift($imprintTypes);


    $hasAUPricing = array_key_exists("pricetable_au", $jsonData);
    $hasNZPricing = array_key_exists("pricetable_nz", $jsonData);
    
    // Determine available_leadtime based on pricing data
    $availableCountry = $hasNZPricing ? "AU, NZ" : ($hasAUPricing ? "AU" : "");


    if (!isset($groupedByNames[$decorationName])) {
        $groupedByNames[$decorationName] = array(
            'AU' => array(
                'moq_surcharge' => null,
                'setup_new' => $decoration['new_setup_au'],
                'setup_repeat' => $decoration['repeat_setup_au'],
                'instruction' => $decorationName,
                'details' => array()
            ),
            'Imprint_Area' => $Imprint_Area,
            'Product_Code' => $Product_Code,
            'Supplier_Name' => $Supplier_Name,
            'Imprint_Type' => $imprintType,
            'Avaliable_Country' => $availableCountry
        );
        
        if ($availableCountry !== 'AU') {
            $groupedByNames[$decorationName]['NZ'] = array(
                'moq_surcharge' => null,
                'setup_new' => $decoration['new_setup_nz'],
                'setup_repeat' => $decoration['repeat_setup_nz'],
                'instruction' => $decorationName,
                'details' => array()
            );
        }
    }

    $orderNumberAU = count($groupedByNames[$decorationName]['AU']['details']) + 1;
    $orderNumberNZ = isset($groupedByNames[$decorationName]['NZ']) ? count($groupedByNames[$decorationName]['NZ']['details']) + 1 : 0;

    $groupedByNames[$decorationName]['AU']['details'][] = array(
        'order' => (string) $orderNumberAU,
        'leadtime' => $decoration['leadtime_au'],
        'cost' => $decoration['cost_au'],
        'maxqty' => $decoration['maxqty']
    );

    if (isset($groupedByNames[$decorationName]['NZ']) && isset($decoration['leadtime_nz'])) {
        $groupedByNames[$decorationName]['NZ']['details'][] = array(
            'order' => (string) $orderNumberNZ,
            'leadtime' => $decoration['leadtime_nz'],
            'cost' => $decoration['cost_nz'],
            'maxqty' => $decoration['maxqty']
        );
    }
}

// 使用 PDO 执行 SQL
//$pdo = new PDO(/* 数据库连接信息 */);
foreach ($groupedByNames as $name => $data) {
    $sql = 'INSERT INTO Decoration (Decoration_Name, Imprint_Area, Product_Code, Supplier_Name, Imprint_Type, Avaliable_Country, Services) VALUES (?, ?, ?, ?, ?, ?, ?)';
    $nzData = isset($data['NZ']) ? $data['NZ'] : array();
    $values = array(
        $name,
        $data['Imprint_Area'],
        $data['Product_Code'],
        $data['Supplier_Name'],
        $data['Imprint_Type'],
        $data['Avaliable_Country'],
        json_encode(array('AU' => $data['AU'], 'NZ' => $nzData))

    );
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
}


?>