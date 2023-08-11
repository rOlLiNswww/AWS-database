const fs = require('fs');

fs.readFile('input.json', 'utf8', (err, data) => {
    if (err) {
        console.error('Error reading input.json:', err);
        return;
    }

    try {
        const jsonData = JSON.parse(data);

        const specification = {};
        for (let i = 1; i <= 4; i++) {
            const nameKey = `specification_name${i}`;
            const valueKey = `specification_value${i}`;
            if (jsonData[nameKey] !== undefined) {
                specification[nameKey] = jsonData[nameKey];
                specification[valueKey] = jsonData[valueKey] !== undefined ? jsonData[valueKey] : null;
            }
        }

        jsonData['specification'] = specification;


        const packaging = {
            "packaging_type": jsonData["packaging_type"] || "",
            "carton_length": jsonData["carton_length"] || "",
            "carton_width": jsonData["carton_width"] || "",
            "carton_height": jsonData["carton_height"] || "",
            "carton_weight": jsonData["carton_weight"] || "",
            "carton_qty": jsonData["carton_qty"] || ""
        };
        

        jsonData['packaging'] = packaging;


        const shippingCost = {
            "shipping_au": jsonData["shipping_per_location_au"] || 0,
            "shipping_nz": jsonData["shipping_per_location_nz"] || 0
        };

        jsonData['shipping_cost'] = shippingCost;

        // Create images array with tag value added
        const imagesWithTags = jsonData["images"].map(image => ({
            "name": image["name"],
            "tag": null,
            "colour": image["colour"],
            "url": image["url"]
        }));

        // Replace images array in output JSON
        jsonData['images'] = imagesWithTags;


        const hasAUPricing = jsonData.hasOwnProperty("pricetable_au");
        const hasNZPricing = jsonData.hasOwnProperty("pricetable_nz");

        // Determine available_leadtime based on pricing data
        const availableLeadtime = hasNZPricing ? "AU, NZ" : (hasAUPricing ? "AU" : "");

        // Add available_leadtime to the output JSON
        jsonData['available_leadtime'] = availableLeadtime;


        const additional_info = {
            "price_disclaimer": jsonData["price_disclaimer"] || "",
            "freight_disclaimer_au": jsonData["freight_disclaimer_au"] || "",
            "freight_disclaimer_nz": jsonData["freight_disclaimer_nz"] || "",
            "additional_info": jsonData["additional_info"] || "",
            "change_log_au": jsonData["change_log_au"] || "",
            "change_log_nz": jsonData["change_log_nz"] || ""
        };

        jsonData['additional_info'] = additional_info;


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
            "Feature",
            "keywords",
            "availbale_colour",
            "available_branding",
            "colour_pms",
            "specification",
            "packaging",
            "shipping_cost",
            "images",
            "available_leadtime",
            "additional_info"
            
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

        if (jsonData.hasOwnProperty('availbale_colour')) {
            jsonData['colour_pms'] = jsonData['availbale_colour'];
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
                console.log('data has been written to output.json');
            }
        });
    } catch (parseError) {
        console.error('Error parsing input.json:', parseError);
    }
});

