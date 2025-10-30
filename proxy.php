<?php
// proxy.php — безопасный relay-мост между твоим ботом и Binance Futures API
error_reporting(0);
header('Content-Type: application/json');

$path = $_GET['path'] ?? '';
if (!$path) {
    echo json_encode(['error' => 'no path']);
    exit;
}

$target = 'https://fapi.binance.com/' . $path;

$method = $_SERVER['REQUEST_METHOD'];
$body   = file_get_contents('php://input');

// Заголовки
$headers = [];
foreach (getallheaders() as $k => $v) {
    if (stripos($k, 'X-MBX-') === 0 || stripos($k, 'X-API-KEY') === 0) {
        $headers[] = "$k: $v";
    }
}
$headers[] = 'Content-Type: application/x-www-form-urlencoded';

$ch = curl_init($target);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_HTTPHEADER => $headers,
]);
$response = curl_exec($ch);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

if ($error) {
    echo json_encode(['error' => $error, 'curl_info' => $info]);
} else {
    echo $response;
}
