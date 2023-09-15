<?php

require 'db_connection.php';

function insertPBDecoration($jsonData,$Status) {

    global $pdo;

    $decorations = isset($jsonData['Product_Imprint_Method']) ? $jsonData['Product_Imprint_Method'] : array();

    $setupNew = $jsonData['New_Setup'];
    $setupRepeat = $jsonData['Repeat_Setup'];

    $leadtimes = [];
    foreach ($jsonData['Hightlights'] as $highlight) {
        if (strpos($highlight['Highlights'], 'Service') !== false) {
            $leadtimes[] = $highlight['Highlights'];
        }
    }

    for ($i = 1; $i <= 6; $i++) {
        $keyPrefix = "productImprintmethod{$i}";

        if (isset($decorations["{$keyPrefix}Des"]) && $decorations["{$keyPrefix}Des"] !== null) {

            $description = $decorations["{$keyPrefix}Des"];
            $maxcolour = $decorations["{$keyPrefix}Max"];
            $cost = $decorations["{$keyPrefix}Cost"];
            $imprint_area = $decorations["{$keyPrefix}Size"];
            if ($description == 'Laser Engraving') {
                $description = 'Laser Engrave';
            }
            if ($description == 'Wrap Laser Engraving') {
                $description = 'Wrap Laser Engrave';
            }
            $imprint_type = strtoupper(str_replace(' ', '_', $description));

            $service = [
                "AU" => [
                    "moq_surcharge" => null,
                    "setup_new" => $setupNew,
                    "setup_repeat" => $setupRepeat,
                    "instruction" => $description,
                    "details" => []
                ]
            ];

            foreach ($leadtimes as $index => $leadtime) {
                $service['AU']['details'][] = [
                    "leadtime" => $leadtime,
                    "order" => $index + 1,
                    "cost" => $cost,
                    "maxqty" => null
                ];
            }

            if ($Status == "Insert") {
                $sql = 'INSERT INTO Decoration (Decoration_Name, Imprint_Area, Product_Code, Supplier_Name, Imprint_Type, Max_Colour,Services) VALUES (?, ?, ?, ?, ?,?, ?)';
                $stmt = $pdo->prepare($sql);
                $values = [$description, $imprint_area, $jsonData['Product_Code'], "PB", $imprint_type, $maxcolour, json_encode($service)];
            } elseif ($Status == "Updated") {
                $sql = 'UPDATE Decoration SET Imprint_Area = ?, Imprint_Type = ?, Max_Colour = ?, Services = ? WHERE Product_Code = ? AND Decoration_Name = ?';
                $stmt = $pdo->prepare($sql);
                $values = [$imprint_area, $imprint_type, $maxcolour, json_encode($service), $jsonData['Product_Code'], $description];
            } else {
                // Unknown status
                continue;
            }
            if (!$stmt->execute($values)) {
                echo "Error inserting data.";
            }

            
        } else {
            break;
        }
    }
}

?>
