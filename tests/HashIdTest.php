<?php

namespace Atldays\HashIds\Tests;

use Atldays\HashIds\Exceptions\InvalidHashIdException;
use Atldays\HashIds\HashId;
use Atldays\HashIds\HashIdRegistry;
use Illuminate\Support\Facades\App;

class HashIdTest extends TestCase
{
    public function test_instance(): void
    {
        $hashId = HashIdRegistry::make('test_salt');
        $this->assertInstanceOf(HashId::class, $hashId);
    }

    public function test_is_disabled(): void
    {
        $hashId = HashIdRegistry::make('test_salt');
        $this->assertInstanceOf(HashId::class, $hashId);
    }

    public function test_it_can_be_resolved_with_custom_salt(): void
    {
        $hashId = App::make(HashId::class, ['salt' => 'another_salt']);

        $this->assertInstanceOf(HashId::class, $hashId);
        $this->assertNotSame($hashId->encode(123), HashIdRegistry::make('test_salt')->encode(123));
    }

    public function test_encode_decode(): void
    {
        $hashId = HashIdRegistry::make('test_salt');

        $encoded = $hashId->encode(123);
        $this->assertEquals('xR8J2EQ8E2wK', $encoded);

        $decoded = $hashId->decode($encoded);
        $this->assertEquals(123, $decoded);
    }

    public function test_encode_decode_with_enable(): void
    {
        $hashId = HashIdRegistry::make('test_salt');

        $encoded = $hashId->encode(123);
        $this->assertEquals('xR8J2EQ8E2wK', $encoded);

        $decoded = $hashId->decode($encoded);
        $this->assertEquals(123, $decoded);
    }

    public function test_equals_keys_instance(): void
    {
        $hashIdA = HashIdRegistry::make('App\Models\Addon', 'Atldays\Database\Models\Addon');

        $this->assertEquals('e9mEzjw0PJRM', $hashIdA->encode(1));

        $hashIdB = HashIdRegistry::make('Atldays\Database\Models\Addon');

        $this->assertEquals('e9mEzjw0PJRM', $hashIdB->encode(1));
    }

    public function test_it_throws_for_invalid_hash(): void
    {
        $hashId = HashIdRegistry::make('test_salt');

        $this->expectException(InvalidHashIdException::class);

        $hashId->decode('invalid-hash');
    }
}
