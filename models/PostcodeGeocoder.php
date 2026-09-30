<?php

declare(strict_types=1);

namespace Osmium\Services\Geocoder\Models;

/**
 * Looks up a UK postcode's lat/lng via postcodes.io - a free, no-key-required
 * service. Returns null (rather than throwing) on an invalid/unrecognised
 * postcode or network failure, since a map pin is a nice-to-have, not
 * something that should block saving whatever record owns the postcode.
 *
 * A pure capability service with no admin page of its own - other services
 * declare "requires": {"services": ["geocoder"]} in their manifest and
 * reference this class directly once ServiceDependencyResolver confirms it's
 * installed (see that class's doc comment for why no separate "loading"
 * mechanism is needed beyond the install-time guarantee).
 */
class PostcodeGeocoder
{
    public function geocode(string $postcode): ?array
    {
        $encodedPostcode = \rawurlencode($postcode);
        $url = "https://api.postcodes.io/postcodes/{$encodedPostcode}";

        $ch = \curl_init($url);
        \curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);
        $response = \curl_exec($ch);
        $httpCode = \curl_getinfo(handle: $ch, option: CURLINFO_HTTP_CODE);

        $lookupFailed = $response === false || $httpCode !== 200;
        if ($lookupFailed) return null;

        $data = \json_decode($response, associative: true);
        $hasResult = isset($data['result']['latitude'], $data['result']['longitude']);
        if (!$hasResult) return null;

        return [
            'latitude' => (float) $data['result']['latitude'],
            'longitude' => (float) $data['result']['longitude'],
        ];
    }
}
