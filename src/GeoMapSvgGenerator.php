<?php

namespace Flowaxy;

/**
 * Class GeoMapSvgGenerator
 *
 * Generates an interactive SVG map from GeoJSON data and custom markers.
 *
 * @package Flowaxy
 */

class GeoMapSvgGenerator
{

    /**
     * GeoJSON data (as array).
     * @var array
     */

    private array $geojson;

    /**
     * List of markers to render on the map.
     * Each marker may contain:
     *  - lat (float): Latitude
     *  - lng (float): Longitude
     *  - type (string): Marker type (for CSS class)
     *  - icon (string): Emoji or symbol to show instead of a circle
     *  - count (int): Number to show below the marker
     *  - name (string): Tooltip/title
     * @var array
     */

    private array $markers;

    /**
     * Width of the generated SVG.
     * @var int
     */

    private int $width;

    /**
     * Height of the generated SVG.
     * @var int
     */

    private int $height;

    /**
     * Whether to show tooltips on hover.
     * @var bool
     */

    private bool $showTooltips;

    /**
     * Language code for region names (e.g. 'uk', 'en', etc.)
     * @var string
     */

    private string $language;

    /**
     * Calculated map bounds from GeoJSON data.
     * @var array
     */

    private array $geoBounds;

    /**
     * GeoMapSvgGenerator constructor.
     *
     * @param string|array $geojson Path to GeoJSON file or decoded array
     * @param array $markers Array of custom markers
     * @param int $width SVG width
     * @param int $height SVG height
     * @param bool $showTooltips Whether to show region names as tooltips
     * @param string $language Language code for region names
     */

    public function __construct(
        string|array $geojson,
        array $markers = [],
        int $width = 800,
        int $height = 600,
        bool $showTooltips = true,
        string $language = 'uk'
    ) {
        $this->geojson = is_array($geojson) ? $geojson : json_decode(file_get_contents($geojson), true);

        if (!$this->geojson || !isset($this->geojson['features'])) {
            throw new \InvalidArgumentException("Invalid GeoJSON data.");
        }

        $this->markers = $markers;
        $this->width = $width;
        $this->height = $height;
        $this->showTooltips = $showTooltips;
        $this->language = $language;
        $this->geoBounds = $this->calculateGeoBounds();
    }

    /**
     * Internal: Calculate min/max lat/lng from the GeoJSON features.
     *
     * @return array [$minLat, $maxLat, $minLng, $maxLng]
     */

    private function calculateGeoBounds(): array
    {
        $minLat = $minLng = INF;
        $maxLat = $maxLng = -INF;
        foreach ($this->geojson['features'] as $f) {
            $coords = $f['geometry']['coordinates'];
            $type = $f['geometry']['type'];
            $polys = $type === 'Polygon' ? [$coords] : $coords;
            foreach ($polys as $poly) {
                foreach ($poly[0] as [$lng, $lat]) {
                    $minLat = min($minLat, $lat);
                    $maxLat = max($maxLat, $lat);
                    $minLng = min($minLng, $lng);
                    $maxLng = max($maxLng, $lng);
                }
            }
        }
        return [$minLat, $maxLat, $minLng, $maxLng];
    }

    /**
     * Convert geographic coordinates (lat/lng) to SVG coordinates (x/y).
     *
     * @param float $lat
     * @param float $lng
     * @return array [x, y]
     */

    public function latLngToSvg(float $lat, float $lng): array
    {
        [$minLat, $maxLat, $minLng, $maxLng] = $this->geoBounds;
        $x = ($lng - $minLng) / ($maxLng - $minLng) * $this->width;
        $y = ($maxLat - $lat) / ($maxLat - $minLat) * $this->height;
        return [$x, $y];
    }

    /**
     * Internal: Convert polygon coordinates to an SVG path string.
     *
     * @param array $polygon GeoJSON polygon coordinates
     * @return string SVG path string
     */

    private function renderPolygon(array $polygon): string
    {
        $path = '';
        foreach ($polygon as $ring) {
            $path .= 'M ';
            foreach ($ring as [$lng, $lat]) {
                [$x, $y] = $this->latLngToSvg($lat, $lng);
                $path .= "$x,$y ";
            }
            $path .= 'Z ';
        }
        return trim($path);
    }

    /**
     * Internal: Render a marker as SVG (circle, icon, or label).
     *
     * @param array $m Marker data
     * @return string SVG element
     */
    
    private function renderMarker(array $m): string
    {
        if (!isset($m['lat'], $m['lng'])) return '';

        [$x, $y] = $this->latLngToSvg($m['lat'], $m['lng']);
        $classes = ['marker'];
        if (!empty($m['type'])) $classes[] = preg_replace('/[^a-z0-9\-_]/i', '', $m['type']);
        if (!empty($m['class'])) $classes[] = $m['class'];
        $classAttr = implode(' ', array_unique($classes));
        $title = htmlspecialchars($m['name'] ?? '');
        $svg = '';

        if (!empty($m['icon'])) {
            $icon = htmlspecialchars($m['icon']);
            $svg .= "<text x=\"$x\" y=\"$y\" class=\"$classAttr icon\" text-anchor=\"middle\" dominant-baseline=\"central\">$icon<title>$title</title></text>";
        } elseif (isset($m['count'])) {
            $svg .= "<circle cx=\"$x\" cy=\"$y\" r=\"7\" class=\"$classAttr\"><title>$title</title></circle>";
            $svg .= "<text x=\"$x\" y=\"" . ($y + 3) . "\" class=\"label\">" . htmlspecialchars((string)$m['count']) . "</text>";
        } elseif (!empty($m['label'])) {
            $label = htmlspecialchars($m['label']);
            $svg .= "<text x=\"$x\" y=\"$y\" class=\"$classAttr\" text-anchor=\"middle\" dominant-baseline=\"central\">$label<title>$title</title></text>";
        } else {
            $svg .= "<circle class=\"$classAttr\" cx=\"$x\" cy=\"$y\" r=\"6\" data-name=\"$title\"></circle>";
        }

        return $svg;
    }

    /**
     * Generate the SVG string containing the map and markers.
     *
     * @param array $markers Optional: replace the markers for this generation
     * @return string SVG code
     */

    public function generateSvg(array $markers = []): string
    {
        if (!empty($markers)) {
            $this->markers = $markers;
        }

        $svg = "<svg viewBox='0 0 {$this->width} {$this->height}' xmlns='http://www.w3.org/2000/svg'>";

        foreach ($this->geojson['features'] as $feature) {
            $coords = $feature['geometry']['coordinates'];
            $type = $feature['geometry']['type'];

            $name = $feature['properties']["name:{$this->language}"]
                ?? $feature['properties']['name']
                ?? '—';

            $path = '';
            if ($type === 'Polygon') {
                $path = $this->renderPolygon($coords);
            } elseif ($type === 'MultiPolygon') {
                foreach ($coords as $poly) {
                    $path .= $this->renderPolygon($poly) . ' ';
                }
            }

            $tooltip = $this->showTooltips ? "<title>" . htmlspecialchars($name) . "</title>" : '';
            $svg .= "<path class='region' d='" . trim($path) . "' fill-rule='evenodd'>$tooltip</path>";
        }

        foreach ($this->markers as $m) {
            $svg .= $this->renderMarker($m);
        }

        $svg .= '</svg>';
        return $svg;
    }
}