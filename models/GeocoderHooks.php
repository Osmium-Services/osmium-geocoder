<?php

declare(strict_types=1);

namespace Osmium\Services\Geocoder\Models;

/**
 * Answers core's location.geocode hook (fired when Company > Location is saved with a postcode and no coordinates).
 */
class GeocoderHooks
{
    /** @param array{postcode?: string} $payload */
    public static function geocode(array $payload): ?array
    {
        $postcode = \trim((string) ($payload['postcode'] ?? ''));
        if ($postcode === '') return null;

        return (new PostcodeGeocoder())->geocode($postcode);
    }
}
