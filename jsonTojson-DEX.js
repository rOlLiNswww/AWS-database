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
        const availableCountry = hasNZPricing ? "AU, NZ" : (hasAUPricing ? "AU" : "");

        // Add available_leadtime to the output JSON
        jsonData['availableCountry'] = availableCountry;


        const additional_info = {
            "price_disclaimer": jsonData["price_disclaimer"] || "",
            "freight_disclaimer_au": jsonData["freight_disclaimer_au"] || "",
            "freight_disclaimer_nz": jsonData["freight_disclaimer_nz"] || "",
            "additional_info": jsonData["additional_info"] || "",
            "change_log_au": jsonData["change_log_au"] || "",
            "change_log_nz": jsonData["change_log_nz"] || ""
        };

        jsonData['additional_info'] = additional_info;


        const files = jsonData["files"].map(files => {
            let tag = null;
            if (files["name"] === "ProductLineDrawing") {
                tag = "Line Drawing";
            } else if (files["name"] === "ProductCertificate") {
                tag = "Certificate";
            }
            
            return {
                "name": files["name"],
                "tag": tag,
                "url": files["url"]
            };
        });

        // Replace images array in output JSON
        jsonData['files'] = files;


        //inventory
        const inventory = {
            "name": jsonData["inventory"]["itemName"],
            "code": jsonData["inventory"]["itemNumber"],
            "colour": {
                "name": jsonData["inventory"]["colour"],
                "hex": "",
                "pms": ""
            },
            "onHand": jsonData["inventory"]["onHand"],
            "onOrder": jsonData["inventory"]["onOrder"],
            "incoming": jsonData["inventory"]["incomingStock"],
            "available_country": "",//?
            "supplier_id": jsonData["supplier_code"]
        };

        // Replace "inventory" object in output JSON
        jsonData['inventory'] = inventory;



        let lowestPriceAU = null;
        for (const price of jsonData['pricetable_au']) {
            for (let i = 9; i >= 1; i--) {
                const priceKey = 'price' + i;
                if (price[priceKey] !== '') {
                    lowestPriceAU = price[priceKey];
                    break;
                }
            }
            if (lowestPriceAU !== null) {
                break;
            }
        }

        // Find lowest price in pricetable_nz
        let lowestPriceNZ = null;
        for (const price of jsonData['pricetable_nz']) {
            for (let i = 9; i >= 1; i--) {
                const priceKey = 'price' + i;
                if (price[priceKey] !== '') {
                    lowestPriceNZ = price[priceKey];
                    break;
                }
            }
            if (lowestPriceNZ !== null) {
                break;
            }
        }
      

        // Create lowest_price objec
        const lowestPrice = {
            "lowest_priceAU": lowestPriceAU,
            
            "lowest_priceNZ": lowestPriceNZ,
            
        };

        // Add lowest_price to output JSON
        jsonData['lowest_price'] = lowestPrice;


    

        // Modify pricetable_au and pricetable_nz
        const newPricetableAU = jsonData["pricetable_au"].map(entry => ({
            ...entry,
            "country": "AU",
            "instruction": "",
        }));
        
        const newPricetableNZ = jsonData["pricetable_nz"].map(entry => ({
            ...entry,
            "country": "NZ",
            "instruction": "",
        }));

        // Update pricetable arrays in the output JSON
        jsonData['AU'] = newPricetableAU;
        jsonData['NZ'] = newPricetableNZ;


        jsonData['product_url'] = jsonData['product_url'];


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
            "additional_info",
            "files",
            "inventory",
            "product_url",
            "AU",
            "NZ",
            "lowest_price",

            "availableCountry",
            
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
