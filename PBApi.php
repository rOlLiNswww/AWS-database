<?php
$product_code = 'S903';  // 替换为实际的产品代码
$url = "https://api.promobrands.com.au/product?Product_Code={$product_code}";

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 设置cURL会话以返回输出
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo "cURL Error: " . curl_error($ch); // 输出cURL错误
    curl_close($ch);
    exit();
}

curl_close($ch);

// 将响应保存到本地文件
if (file_put_contents('input.json', $response) === false) {
    echo "写入文件时发生错误";
} else {
    echo "成功将数据保存到input.json文件中";
}

?>