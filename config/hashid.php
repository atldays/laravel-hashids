<?php

return [
    /*
     * Controls the HTTP integration layer of the package.
     *
     * When enabled, route binding, validation rules and FormRequest helpers
     * expect hash IDs in incoming HTTP data and route generation uses hash IDs.
     *
     * When disabled, those HTTP-oriented helpers work with plain numeric values
     * instead, which is useful for local development and debugging.
     */
    'http_enabled' => (bool)env('HASH_ID_HTTP_ENABLED', !env('APP_DEBUG')),

    /*
     * Defines the minimum generated hash ID length.
     *
     * This affects encoded values returned by the core HashId service and all
     * model helpers that expose hash IDs, such as getHashId() and hash_id.
     */
    'min_length' => 12,
];
