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
}