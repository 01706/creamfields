<?php

$eventIdentifier = 'creamfields';
$editionIdentifier = 'creamfields2024';
$xProtect = '320eaa4db66823c8ce4de7a7f823a451';

$dataFolder = 'data/2024/';

if(!file_exists($dataFolder)){
    mkdir($dataFolder, recursive: true);
}

$urlStages = 'https://app.appmiral.com/api/v6/events/' . $eventIdentifier . '/editions/' . $editionIdentifier . '/stages?max_per_page=30';
$urlArtists = 'https://app.appmiral.com/api/v6/events/' . $eventIdentifier . '/editions/' . $editionIdentifier . '/artists?max_per_page=300';

$rawResultStages = sendGetRequest($xProtect, $urlStages);
$rawResultArtists = sendGetRequest($xProtect, $urlArtists);

file_put_contents($dataFolder . 'stages.json', $rawResultStages);
file_put_contents($dataFolder . 'artists.json', $rawResultArtists);

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
