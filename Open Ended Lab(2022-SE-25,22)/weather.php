<?php
// Include configuration
require_once 'C:\Users\dell\Desktop\data\config.php';

// OpenWeatherMap API configuration
$baseUrl = "https://api.openweathermap.org/data/2.5/weather";

// Get city from query parameter
$city = isset($_GET['city']) ? $_GET['city'] : '';

if (empty($city)) {
    http_response_code(400);
    echo json_encode(['error' => 'City parameter is required']);
    exit;
}

// Build API URL - now using the constant from config
$url = $baseUrl . "?q=" . urlencode($city) . "&appid=" . OPENWEATHER_API_KEY . "&units=metric";

// Rest of the code remains the same...
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_error($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch weather data: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

header('Content-Type: application/json');
http_response_code($httpCode);
echo $response;
?>