<?php

return [
    /*
     * Control whether the HTTP integration layer expects and emits hash IDs.
     */
    'http_enabled' => (bool)env('HASH_ID_HTTP_ENABLED', !env('APP_DEBUG')),

    /*
     * The minimum length of the hash ID.
     */
    'min_length' => 12,
];
