<?php
date_default_timezone_set('UTC');

$dataFolder = 'data/2024/';

if (!file_exists($dataFolder . 'stages.json') 
    || !file_exists($dataFolder . 'artists.json'))
{
    die('Unable to find either stages.json or artists.json within ' . $dataFolder);
}

$stageContent = json_decode(file_get_contents($dataFolder . 'stages.json'));

$stages = [];

foreach ($stageContent->data as $stage) {
    $stages[$stage->id] = [
        'id' => $stage->id,
        'name' => $stage->name,
        'priority' => $stage->priority
    ];
}

$artistContent = json_decode(file_get_contents($dataFolder . 'artists.json'));

$artists = [];
$performances = [];

foreach ($artistContent->data as $artist) {
    $artists[$artist->id] = [
        'name' => $artist->name,
    ];

    if (isset($artist->performances) === null) {
        echo 'No performances: ' . $artist->name . PHP_EOL;
        continue;
    }

    foreach ($artist->performances as $performance) {

        $performances[] = [
            'artist_name' => $artists[$performance->artist_id]['name'],
            'stage_name' => $stages[$performance->stage_id]['name']??null,
            'edition_day_id' => $performance->edition_day_id,
            'start_time' => $performance->start_time,
            'end_time' => $performance->end_time,
        ];
    }
}
unset($stageContent, $stages, $artistContent, $artists);

/// --------------------

$days = [
    [
        'edition_day_id' => 2994, // 2024-08-22
        'name' => 'Thursday'
    ],
    [
        'edition_day_id' => 2995, // 2024-08-23
        'name' => 'Friday'
    ],
    [
        'edition_day_id' => 2996, // 2024-08-24
        'name' => 'Saturday'
    ],
    [
        'edition_day_id' => 2997, // 2024-08-25
        'name' => 'Sunday'
    ],
];

foreach ($days as $day) {
    $performancesOnDay = [];
    $stagesOnDay = [];

    $performanceTimes = [];

    foreach ($performances as $performance) {
        if ($performance['edition_day_id'] != $day['edition_day_id']) {
            continue;
        }

        $performancesOnDay[] = $performance;

        if (in_array($performance['stage_name'], $stagesOnDay, true) == false) {
            $stagesOnDay[] = $performance['stage_name'];
        }

        $stageIndex = array_search($performance['stage_name'], $stagesOnDay, true);

        $start = (new DateTime($performance['start_time']))->setTimezone(new DateTimeZone('Europe/London'));
        $end = (new DateTime($performance['end_time']))->setTimezone(new DateTimeZone('Europe/London'));

        // Times array is set to every 30 minutes, move the start to a 30 min slot so that it fits the table
        $tableStart = clone $start;
        if ($tableStart->format('i') == '45'
            || $tableStart->format('i') == '15'
        ) {
            $tableStart->modify('-15 minutes');
        }

        $performanceTimes[$tableStart->format('H:i')][$stageIndex] = $performance['artist_name'] . "\n(" . $start->format('H:i') . ' - ' . $end->format('H:i') . ')';
    }

    $times = createTimesArray();
    $csv = $dataFolder . 'performances-'. $day['name'] .'.csv'; 
    
    $file_pointer = fopen($csv, 'w');

    fputcsv($file_pointer, [$day['name']]);

    for($timeIndex = 0; $timeIndex < 29; $timeIndex++) {
        if ($timeIndex == 0) {
            // Header
            fputcsv($file_pointer, csvHeader($stagesOnDay));
        }
        
        $fields = [
            $times[$timeIndex],
        ];
        $fields = array_pad($fields, count($stagesOnDay) + 1 , ' ');

        if (isset($performanceTimes[$times[$timeIndex]])) {
            foreach ($performanceTimes[$times[$timeIndex]] as $key => $value) {
                $fields[$key + 1] = $value;
            }
        }

        fputcsv($file_pointer, $fields);
    }

    fclose($file_pointer);
}

function createTimesArray() {
    $times = [];
    $hour = 14;
    $minutes = 0;
    while (true) {
        $times[] = str_pad($hour, 2, 0, STR_PAD_LEFT) . ':'. str_pad($minutes, 2, 0, STR_PAD_LEFT) ;

        if ($hour == 4) {
            break;
        }
        if ($minutes == 0) {
            $minutes = 30;
            continue;
        } else {
            $minutes = 0;
        }

        if ($hour == 23) {
            $hour = 0;
            continue;
        }
        $hour++;
    }
    return($times);
}

function csvHeader(array $header): array {
    array_unshift($header, '');
    return $header;
}
