<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Support;
use Modules\Glitter\NotificationHub\Support\IdempotencyHasher;
use PHPUnit\Framework\TestCase;
class IdempotencyHasherTest extends TestCase
{
    public function test_hash_is_stable_hex_and_collision_safe_for_delimiters(): void { $h=new IdempotencyHasher; $a=$h->hash('a','bc','d','한글:key'); $this->assertSame($a,$h->hash('a','bc','d','한글:key')); $this->assertNotSame($a,$h->hash('ab','c','d','한글:key')); $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/',$a); }
}
