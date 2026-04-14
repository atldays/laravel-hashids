<?php

return [
    /*
     * Enable or disable the Hash ID feature.
     */
    'enable' => (bool) env('HASH_ID_ENABLE', ! env('APP_DEBUG')),

    /*
     * Enable or disable the strict mode.
     */
    'strict' => (bool) env('HASH_ID_STRICT', false),

    /*
     * The minimum length of the hash ID.
     */
    'min_length' => 12,
];
