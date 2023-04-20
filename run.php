<?php
// USE command: php run.php
include_once 'config.php';
include_once 'curl.php';

if (!(API_KEY || API_EMAIL || DOMAIN)) {
    die('Check your configuration');
}

print_r("Start bulk delete domain: " . DOMAIN . "\n");
$headers = [
    'X-Auth-Email: ' . API_EMAIL,
    'Authorization: Bearer ' . API_KEY,
];
// get record from cf
$response        = request("GET", 'https://api.cloudflare.com/client/v4/zones?name=' . DOMAIN, $headers);
$zone_identifier = $response['result'][0]['id'];
print_r("Zones: {$zone_identifier}\n");
if (!$zone_identifier) {
    die('Zone not found');
}
// get dns from zone identifier
$response = request("GET", "https://api.cloudflare.com/client/v4/zones/{$zone_identifier}/dns_records", $headers);
if ($response['success'] === false) {
    print_r(json_encode($response, JSON_PRETTY_PRINT));
    die();
}
// loop through records and delete it all!!
foreach ($response['result'] as $record) {
    $identifier = $record['id'];
    print_r("Delete name: {$record['name']}, type: {$record['type']}, content: {$record['content']}\n");
    $response = request("DELETE", "https://api.cloudflare.com/client/v4/zones/{$zone_identifier}/dns_records/{$identifier}", $headers);
    if ($response['success'] === false) {
        print_r(json_encode($response, JSON_PRETTY_PRINT));
        die();
    }
    sleep(0.5);
}
