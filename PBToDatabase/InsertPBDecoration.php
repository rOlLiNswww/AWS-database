<?php

require 'db_connection.php';

function insertPBDecoration($jsonData) {

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
            $imprint = [];

            $description = $decorations["{$keyPrefix}Des"];
            $maxcolour = $decorations["{$keyPrefix}Max"];
            $cost = $decorations["{$keyPrefix}Cost"];
            $imprint_area = $decorations["{$keyPrefix}Size"];

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

            $sql = 'INSERT INTO Decoration (Decoration_Name, Imprint_Area, Product_Code, Supplier_Name, Imprint_Type, Max_Colour,Services) VALUES (?, ?, ?, ?, ?,?, ?)';
            $stmt = $pdo->prepare($sql);
            $values = [$description, $imprint_area, $jsonData['Product_Code'], "PB", $description, $maxcolour, json_encode($service)];

            if (!$stmt->execute($values)) {
                echo "Error inserting data.";
            }

            $imprintResults[] = $imprint;
        } else {
            break;
        }
    }
}

?>
