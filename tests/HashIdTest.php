<?php

namespace Atldays\HashIds\Tests;

use Atldays\HashIds\HashId;
use Hashids\Hashids;

class HashIdTest extends TestCase
{
    public function test_instance(): void
    {
        $hashId = HashId::instance('test_salt');
        $this->assertInstanceOf(HashId::class, $hashId);
    }

    public function test_hash_ids(): void
    {
        $hashId = HashId::instance('test_salt');
        $this->assertInstanceOf(Hashids::class, $hashId->hashIds());
    }

    public function test_is_disabled(): void
    {
        $hashId = HashId::instance('test_salt');
        $this->assertInstanceOf(HashId::class, $hashId);
    }

    public function test_set_enable(): void
    {
        $hashId = HashId::instance('test_salt');
        $hashId->setSalt('another_salt');
        $this->assertInstanceOf(HashId::class, $hashId);
    }

    public function test_encode_decode(): void
    {
        $hashId = HashId::instance('test_salt');

        $encoded = $hashId->encode(123);
        $this->assertEquals('ewRA7205P7dn', $encoded);

        $decoded = $hashId->decode($encoded);
        $this->assertEquals(123, $decoded);
    }

    public function test_encode_decode_with_enable(): void
    {
        $hashId = HashId::instance('test_salt');

        $encoded = $hashId->encode(123);
        $this->assertEquals('ewRA7205P7dn', $encoded);

        $decoded = $hashId->decode($encoded);
        $this->assertEquals(123, $decoded);
    }

    public function test_equals_keys_instance(): void
    {
        $hashIdA = HashId::instance('App\Models\Addon', 'Atldays\Database\Models\Addon');

        $this->assertEquals('60vLzl8zq48D', $hashIdA->encode(1));

        $hashIdB = HashId::instance('Atldays\Database\Models\Addon');

        $this->assertEquals('60vLzl8zq48D', $hashIdB->encode(1));
    }
}
