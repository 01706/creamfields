<?php
/**
 * api v7
 * $apiEndpoints = [
 *   [
 *       'path' => 'maps',
 *   ],
 *   [
 *       'saveName' => 'pois-p1',
 *       'path' => 'pois',
 *       'parameters' => [
 *           'page' => 1,
 *           'max_per_page' => 100
 *       ],
 *   ],
 *   [
 *       'saveName' => 'pois-p2',
 *       'path' => 'pois',
 *       'parameters' => [
 *           'page' => 2,
 *           'max_per_page' => 100
 *       ],
 *   ],
 *   [
 *       'saveName' => 'pois-p3',
 *       'path' => 'pois',
 *       'parameters' => [
 *           'page' => 3,
 *           'max_per_page' => 100
 *       ],
 *   ],
 *   [
 *       'saveName' => 'pois-p4',
 *       'path' => 'pois',
 *       'parameters' => [
 *           'page' => 4,
 *           'max_per_page' => 100
 *       ],
 *   ],
 */

$dataFolder = 'data/2024/';

$mapFile = 'maps.json';

$mapOverlayCategoryId = 13099;

$poisFiles = [
    'pois-p1.json',
    'pois-p2.json',
    'pois-p3.json',
];

$maps = json_decode(file_get_contents($dataFolder . $mapFile), false);

foreach ($maps->data as $map) {
    echo $map->name . PHP_EOL;
    echo 'Categories: ' . count($map->categories) . PHP_EOL;

    $geojsonFeatures = [];
    $mapOverlays = [];

    foreach($poisFiles as $poiFile) {
        $pois = json_decode(file_get_contents($dataFolder . $poiFile), false);
        echo 'File: ' . $poiFile . PHP_EOL;
        foreach ($pois->data as $poi) {
            if (isset($poi->map_id) === false || $poi->map_id != $map->id) continue;

            if ($poi->category_id == $mapOverlayCategoryId) {
                $mapOverlays[] = $poi;
                continue;
            }

            $category = findCategoryById($map->categories, $poi->category_id);
            $feature = createFeature($category, $poi);
            $geojsonFeatures[] = $feature;
        }
    }

    $geojsonFeatureCollection = [
        "type" => "FeatureCollection",
        "features" => $geojsonFeatures
    ];

    file_put_contents($dataFolder . 'map-' . $map->id . '.geojson', json_encode($geojsonFeatureCollection, JSON_PRETTY_PRINT));
    
    foreach ($mapOverlays as $mapOverlay) {
        echo $mapOverlay->map_overlay_image->{'3000'} . PHP_EOL;
    }
}

function findCategoryById($categories, int $id) {
    foreach ($categories as $category) {
        if ($category->id === $id)
            return $category;
    }
    return null;
}

function createFeature($category, $poi): array {
    $geometry = createGeoJsonGeometry($poi->type, $poi->coordinates);
    return [
        'type' => 'Feature',
        'properties' => [
            'name' => $poi->name,
            'category' => $category->name,
            '_umap_options' => [
                'fillColor' => $category->color,
            ]
        ],
        'geometry' => $geometry
    ];
}

function createGeoJsonGeometry(string $geoType, $geoCoordinates) {

    if ($geoType != 'polygon') return;

    $coordinates = [];
    foreach ($geoCoordinates as $coordinate) {
        $coordinates[] = [$coordinate->lng, $coordinate->lat];
    }

    return [
        'type' => 'Polygon',
        'coordinates' => [$coordinates]
    ];
};
