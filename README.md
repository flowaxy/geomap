# GeoMapSvgGenerator

GeoMapSvgGenerator is a PHP class for generating interactive SVG maps from GeoJSON data.  
It supports region tooltips, customizable dimensions, and custom markers (icons, counts, etc).

## Features

- Convert GeoJSON to interactive SVG maps
- Render regions with optional tooltips
- Add markers with icons, labels, or counts
- Control output size and language for region names

## Installation

Use Composer to install the library:

```bash
composer require flowaxy/geomap
```

## Usage

```php
use Flowaxy\GeoMapSvgGenerator;

$geojson = 'ukraine.geojson';
$markers = [
    ['lat' => 50.45, 'lng' => 30.52, 'type' => 'capital', 'icon' => '⭐', 'name' => 'Kyiv'],
];

$generator = new GeoMapSvgGenerator($geojson, $markers, 1000, 800, true, 'uk');
$svg = $generator->generateSvg();

file_put_contents('map.svg', $svg);
```

## Marker Format

Each marker may contain:

- `lat` (float): Latitude
- `lng` (float): Longitude
- `type` (string): Marker type (used as CSS class)
- `icon` (string): Emoji or symbol instead of circle
- `count` (int): Number shown below
- `label` (string): Text label
- `name` (string): Tooltip text

## Getting GeoJSON files

You can download GeoJSON files for countries, regions, or the entire world from:

- https://geojson-maps.ash.ms
- https://datahub.io/core/geo-countries
- https://github.com/datasets/geo-countries
- https://github.com/deldersveld/topojson *(convert TopoJSON to GeoJSON using tools like https://mapshaper.org/)*

For Ukraine specifically, you can try:

- https://github.com/deldersveld/topojson/blob/master/countries/ukraine/ukraine-regions.json
- https://mapshaper.org/ to convert or simplify GeoJSON files

Make sure your file contains valid GeoJSON structure with features and geometry.

## License

MIT