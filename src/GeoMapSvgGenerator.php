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
}