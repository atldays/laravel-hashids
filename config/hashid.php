<?php

return [
    /*
    |--------------------------------------------------------------------------
    | External Hash IDs
    |--------------------------------------------------------------------------
    |
    | Controls how the package behaves in the application's external layer.
    | When enabled, routing, validation, request decoding and serialized
    | output use hash IDs for values coming from and returned to the outside
    | world.
    |
    | When disabled, those integrations use plain numeric values instead.
    | This does not disable the core HashId service itself. Encoding,
    | decoding and model-level hash ID features continue to work normally.
    |
     */
    'enabled' => (bool)env('HASH_ID_ENABLED', !env('APP_DEBUG')),

    /*
    |--------------------------------------------------------------------------
    | Default Salt
    |--------------------------------------------------------------------------
    |
    | The default salt used by the core HashId service.
    |
    | This value is passed directly to the underlying hashids/hashids
    | package and can be overridden at runtime when resolving HashId
    | through the container with custom parameters.
    |
     */
    'salt' => env('HASH_ID_SALT', 'secret-salt'),

    /*
    |--------------------------------------------------------------------------
    | Hash ID Length
    |--------------------------------------------------------------------------
    |
    | The length of generated hash IDs.
    |
    | This value is passed directly to the underlying hashids/hashids
    | package and affects encoded values returned by the core HashId
    | service and model helpers such as getHashId() and hash_id.
    |
     */
    'length' => (int)env('HASH_ID_LENGTH', 12),

    /*
    |--------------------------------------------------------------------------
    | Hash ID Alphabet
    |--------------------------------------------------------------------------
    |
    | The alphabet used by the underlying Hashids encoder.
    |
    | This value is passed directly to the underlying hashids/hashids
    | package when the core HashId service creates a new encoder instance.
    |
     */
    'alphabet' => env('HASH_ID_ALPHABET', 'abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890'),
];
