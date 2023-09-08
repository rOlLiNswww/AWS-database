const mysql = require('mysql2');
const fs = require('fs');

// 创建数据库连接池
const pool = mysql.createPool({
  host: '127.0.0.1',
  user: 'wdc',
  password: 'CVd9M#YF',
  database: 'StandardBuffDB',
});


// 获取连接
pool.getConnection((err, connection) => {
  if (err) {
    console.error('Error connecting to MySQL:', err);
    return;
  }



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
        if (jsonData['pricetable_nz']) {
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
          
      } else {
        
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
        
        let newPricetableNZ = null;
        if (jsonData["pricetable_nz"]) {
          newPricetableNZ = jsonData["pricetable_nz"].map(entry => ({
              ...entry,
              "country": "NZ",
              "instruction": "",
          }));   
      } else {
      }
      

        // Update pricetable arrays in the output JSON
        jsonData['AU'] = newPricetableAU;
        if (newPricetableNZ) {
        jsonData['NZ'] = newPricetableNZ;}


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
        }outputData
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



        // 创建 INSERT 语句
        const sql = 'INSERT INTO Products (Product_Code,Product_Details,Supplier_Name) VALUES (?,?,?)';
        const values = [ jsonData["product_code"],outputData,jsonData["supplier_code"]]; // 使用从 JSON 中读取的值



         // 执行 INSERT 语句
         connection.query(sql, values, (err, results) => {
          if (err) {
            console.error('Error inserting data:', err);
          } else {
            console.log('Data inserted successfully!');
          }

          // 释放连接
          connection.release();
        });
        

//////////////////////// Insert Decorations table/////////////////////
const decorations = jsonData.decorations || [];
const groupedByNames = {};
const availableBranding = jsonData["available_branding"];
const imprintTypes = availableBranding ? availableBranding.split(',').map(type => type.trim()) : [];

for (const decoration of decorations) {
  let decorationName = decoration.Name;

  // 从第一段代码中获取名称处理
  const words = decorationName.split(' ');
  if (words.length > 3) {
    decorationName = words.slice(-3).join(' ');
  }

  const Imprint_Area = decoration.Size;
  const Product_Code = jsonData["product_code"];
  const Supplier_Name = jsonData["supplier_code"];
  const imprintType = imprintTypes.shift();
  const availableCountry = jsonData['availableCountry'];


  if (!groupedByNames[decorationName]) {
    groupedByNames[decorationName] = {
      AU: {
        moq_surcharge: null,
        setup_new: decoration["new_setup_au"],
        setup_repeat: decoration["repeat_setup_au"],
        instruction: decorationName,
        details: []
      },
      
      Imprint_Area,
      Product_Code,
      Supplier_Name,
      Imprint_Type: imprintType,
      Avaliable_Country: availableCountry
    };if (availableCountry !== 'AU') {
      groupedByNames[decorationName].NZ = {
          moq_surcharge: null,
          setup_new: decoration["new_setup_nz"],
          setup_repeat: decoration["repeat_setup_nz"],
          instruction: decorationName,
          details: []
      };
  }
  }

  const orderNumberAU = groupedByNames[decorationName].AU.details.length + 1;
  let orderNumberNZ = 0;
  if(groupedByNames[decorationName].NZ) {
      orderNumberNZ = groupedByNames[decorationName].NZ.details.length + 1;
  }

  groupedByNames[decorationName].AU.details.push({
    order: orderNumberAU.toString(),
    leadtime: decoration["leadtime_au"],
    cost: decoration["cost_au"],
    maxqty: decoration["maxqty"]
  });

  if(groupedByNames[decorationName].NZ && decoration["leadtime_nz"]) {
    groupedByNames[decorationName].NZ.details.push({
      order: orderNumberNZ.toString(),
      leadtime: decoration["leadtime_nz"],
      cost: decoration["cost_nz"],
      maxqty: decoration["maxqty"]
    });
  }
  
}

for (const [name, data] of Object.entries(groupedByNames)) {
  const sql = 'INSERT INTO Decoration (Decoration_Name, Imprint_Area, Product_Code, Supplier_Name, Imprint_Type, Avaliable_Country, Services) VALUES (?,?,?,?,?,?,?)';
  const values = [name, data.Imprint_Area, data.Product_Code, data.Supplier_Name, data.Imprint_Type, data.Avaliable_Country, JSON.stringify({
    AU: data.AU,
    NZ: data.NZ
  })];

  connection.query(sql, values, (err, results) => {
    if (err) {
      console.error('Error inserting data:', err);
    } else {
    }
  });
}


/////////////////////////////////////////////////////////////////////

    } catch (parseError) {
        console.error('Error parsing input.json:', parseError);
    }
    pool.end();
});

});




