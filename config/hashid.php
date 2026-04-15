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
     * The default salt used by the core HashId service.
     *
     * This value is passed directly to the underlying hashids/hashids package
     * and can be overridden at runtime when resolving HashId through the
     * container with custom parameters.
     */
    'salt' => env('HASH_ID_SALT', 'secret-salt'),

    /*
     * The length of generated hash IDs.
     *
     * This value is passed directly to the underlying hashids/hashids package
     * and affects encoded values returned by the core HashId service and model
     * helpers such as getHashId() and hash_id.
     *
     * Default: 12
     */
    'length' => (int)env('HASH_ID_LENGTH', 12),

    /*
     * The alphabet used by the underlying Hashids encoder.
     *
     * This value is passed directly to the underlying hashids/hashids package
     * when the core HashId service creates a new encoder instance.
     *
     * Default: 'abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890'
     *
     */
    'alphabet' => env('HASH_ID_ALPHABET', 'abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890'),
];
