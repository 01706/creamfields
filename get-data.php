<?php

$eventIdentifier = 'creamfields';
$editionIdentifier = 'creamfields2024';
$xProtect = '320eaa4db66823c8ce4de7a7f823a451';

$dataFolder = 'data/2024/';

if(!file_exists($dataFolder)){
    mkdir($dataFolder, recursive: true);
}

$apiEndpoints = [
    [
        'path' => 'stages',
        'parameters' => ['max_per_page' => 30],
    ],
    [
        'path' => 'artists',
        'parameters' => ['max_per_page' => 300],
    ],
];

foreach ($apiEndpoints as $endpoint) {
    echo $endpoint['path'] . PHP_EOL;
    $base = 'https://app.appmiral.com/api/v6/events/' . $eventIdentifier . '/editions/' . $editionIdentifier . '/';

    $parameters = '';
    if (isset($endpoint['parameters']) === true) {
        $parameters = '?' . http_build_query($endpoint['parameters']);
    }

    $fullUrl = $base . $endpoint['path'] . $parameters;
    $rawResult = sendGetRequest($xProtect, $fullUrl);

    $saveName = $endpoint['path'];
    if (isset($endpoint['saveName']) === true) $saveName = $endpoint['saveName'];

    file_put_contents($dataFolder . $saveName . '.json', $rawResult);
}

function sendGetRequest(string $xProtect, string $endpoint) {

    $requestHeaders = [
        'Accept: application/json',
        'Accept-Language: ',
        'x-protect: ' . $xProtect,
    ];

    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
    
    $rawResult = curl_exec($ch);

    curl_close($ch);
    
    return $rawResult;
}
