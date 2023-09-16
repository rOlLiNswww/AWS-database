<?php
require 'db_connection.php';

function insertDecoration($jsonData, $Status) {

global $pdo;

$decorations = isset($jsonData['decorations']) ? $jsonData['decorations'] : array();
$groupedByNames = array();
$availableBranding = isset($jsonData['available_branding']) ? $jsonData['available_branding'] : null;
if ($availableBranding === "") {
    $availableBranding = "SCREEN_PRINT";
}
$imprintTypes = $availableBranding ? array_map('trim', explode(',', $availableBranding)) : array();

$previousImprintType = null; 
foreach ($decorations as $decoration) {
    $decorationName = $decoration['Name'];

    $words = explode(' ', $decorationName);

    $decorationName = preg_replace('/\d+|-|\.|Days|Weeks|Hrs/i', '',  $decorationName);
    $decorationName = trim($decorationName);

    $Imprint_Area = $decoration['Size'];
    $Product_Code = $jsonData['product_code'];
    $Supplier_Name = $jsonData['supplier_code'];
    
    $imprintType = array_shift($imprintTypes);
   
    if ($imprintType !== null) {
        $imprintType = strtoupper(str_replace(' ', '_', $imprintType));
        if ($imprintType === 'DIRECT_DIGITAL') {
            $imprintType = 'DIGITAL_DIRECT';
        }
    }
    

    if ($imprintType === null) {
        $imprintType = $previousImprintType;
    } else {
        $previousImprintType = $imprintType;
    }

    $hasAUPricing = array_key_exists("pricetable_au", $jsonData);
    $hasNZPricing = array_key_exists("pricetable_nz", $jsonData);
    
    // Determine available_leadtime based on pricing data
    $availableCountry = $hasNZPricing ? ["AU", "NZ"] : ($hasAUPricing ? ["AU"] : []);
    $avaliableCountryJson = json_encode($availableCountry);



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
        
        if (in_array('NZ', $availableCountry)) {
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

foreach ($groupedByNames as $name => $data) {
    // 首先检查是否已经存在记录
    $checkSQL = 'SELECT * FROM Decoration WHERE Decoration_Name = ? AND Product_Code = ?';
    $stmtCheck = $pdo->prepare($checkSQL);
    $stmtCheck->execute([$name, $data['Product_Code']]);
    $exists = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    $nzData = isset($data['NZ']) ? $data['NZ'] : array();

    if (!$exists) {
        // Insert
        $sql = 'INSERT INTO Decoration (Decoration_Name, Imprint_Area, Product_Code, Supplier_Name, Imprint_Type, Avaliable_Country, Services) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $values = array(
            $name,
            $data['Imprint_Area'],
            $data['Product_Code'],
            $data['Supplier_Name'],
            $data['Imprint_Type'],
            $avaliableCountryJson,
            json_encode(array('AU' => $data['AU'], 'NZ' => $nzData))
        );
    } else {
        // Update
        $sql = 'UPDATE Decoration SET Imprint_Area = ?, Supplier_Name = ?, Imprint_Type = ?, Avaliable_Country = ?, Services = ? WHERE Decoration_Name = ? AND Product_Code = ?';
        $values = array(
            $data['Imprint_Area'],
            $data['Supplier_Name'],
            $data['Imprint_Type'],
            $avaliableCountryJson,
            json_encode(array('AU' => $data['AU'], 'NZ' => $nzData)),
            $name,
            $data['Product_Code']
        );
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
}


}
?>