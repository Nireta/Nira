<?php
$client_id = "SU2506261253397613462891";
$client_version = 1;
$client_secret = "5e7d1708-a97a-413a-877c-7d480b7b9e8c";
$grant_type = "client_credentials";

$cacheFile = __DIR__ . '/token_cache.json';

// ✅ Optional caching (improve performance)
if (file_exists($cacheFile)) {
    $cache = json_decode(file_get_contents($cacheFile), true);
    if (isset($cache['access_token']) && time() < $cache['expires_at']) {
        $accessToken = $cache['access_token'];
        return;
    }
}

// 🧠 Fetch fresh token from PhonePe
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => http_build_query(array(
      'client_id' => $client_id,
      'client_version' => $client_version,
      'client_secret' => $client_secret,
      'grant_type' => $grant_type
  )),
  CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
));

$response = curl_exec($curl);
curl_close($curl);

$getToken = json_decode($response, true);

if (isset($getToken['access_token']) && $getToken['access_token'] != '') {
    $accessToken = $getToken['access_token'];
    $expires_at = time() + ($getToken['expires_in'] ?? 3600);

    // ✅ Save token to file
    file_put_contents($cacheFile, json_encode([
        'access_token' => $accessToken,
        'expires_at' => $expires_at
    ]));
} else {
    echo "<pre>Token Error:\n";
    print_r($getToken);
    echo "</pre>";
    die("❌ Access Token Not Received from PhonePe.");
}
?>
