const fs = require('fs');

fs.readFile('input.json', 'utf8', (err, data) => {
    if (err) {
        console.error('Error reading input.json:', err);
        return;
    }

    try {
        const jsonData = JSON.parse(data);

        // Define the order of keys to output
        const outputOrder = [
            "product_code",
            "product_name",
            "related_product_code",
            "product_is_discontinued",
            "supplier_categories",
            "short_description",
            "full_description",
            "Promo",
            "Feature"
            
            // Add more keys here if needed
        ];

        // Create "Promo" key based on "tag" content
        if (jsonData.hasOwnProperty("tag")) {
            const promoTags = [];
            if (jsonData["tag"].includes("new")) {
                promoTags.push("new");
            }
            if (jsonData["tag"].includes("sale")) {
                promoTags.push("sale");
            }
            if (jsonData["tag"].includes("trending")) {
                promoTags.push("trending");
            }
            if (promoTags.length > 0) {
                jsonData["Promo"] = promoTags.join(', ');
            }
        }

        // Create "Feature" key based on "tag" content
        if (jsonData.hasOwnProperty("tag")) {
            const featureTags = [];
            if (jsonData["tag"].includes("eco")) {
                featureTags.push("eco");
            }
            if (jsonData["tag"].includes("full-colour")) {
                featureTags.push("full-colour");
            }
            if (featureTags.length > 0) {
                jsonData["Feature"] = featureTags.join(',');
            }
        }
        if (jsonData.hasOwnProperty("categories")) {
            jsonData["supplier_categories"] = jsonData["categories"];
        }

        // Filter out entries not in the output order or with supplier_name/supplier_code keys
        const filteredData = {};
        for (const key of outputOrder) {
            if (jsonData.hasOwnProperty(key) && key !== 'supplier_name' && key !== 'supplier_code') {
                filteredData[key] = jsonData[key];
            }
        }

        const outputData = JSON.stringify(filteredData, null, 4);

        fs.writeFile('output.json', outputData, 'utf8', (err) => {
            if (err) {
                console.error('Error writing to output.json:', err);
            } else {
                console.log('Selected data from input.json (excluding supplier_name and supplier_code) has been written to output.json');
            }
        });
    } catch (parseError) {
        console.error('Error parsing input.json:', parseError);
    }
});

