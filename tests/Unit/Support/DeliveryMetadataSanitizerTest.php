<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Support;
use Modules\Glitter\NotificationHub\Support\DeliveryMetadataSanitizer;
use PHPUnit\Framework\TestCase;
class DeliveryMetadataSanitizerTest extends TestCase
{
    public function test_nested_secrets_limits_and_objects_are_safe(): void { $s=new DeliveryMetadataSanitizer(2,2,4); $r=$s->sanitize(['Authorization'=>'Bearer x','nested'=>['Password'=>'x','deep'=>['secret'=>'x']],'extra'=>'x']); $this->assertSame('[REDACTED]',$r['Authorization']); $this->assertSame('[REDACTED]',$r['nested']['Password']); $this->assertTrue($r['_truncated']); $this->assertSame('abcd',$s->sanitize(['v'=>'abcdef'])['v']); $this->assertSame('[UNSUPPORTED]',(new DeliveryMetadataSanitizer)->sanitize(['o'=>new \stdClass])['o']); }

    public function test_free_text_secret_formats_are_redacted(): void
    {
        $input = "Cookie: session=abc\nSet-Cookie: sid=abc\nProxy-Authorization: Basic abc\nprivate_key=secret\nAuthorization: Bearer abc\n\"access_token\":\"xyz\"\n'client_secret' => 'value'\n-----BEGIN PRIVATE KEY-----\nsecret-material\n-----END PRIVATE KEY-----";
        $sanitized = (new DeliveryMetadataSanitizer)->sanitizeText($input, 5000);

        foreach (['session=abc', 'sid=abc', 'Basic abc', 'secret-material', 'Bearer abc', 'xyz', "'value'"] as $secret) {
            $this->assertStringNotContainsString($secret, (string) $sanitized);
        }
        $this->assertGreaterThanOrEqual(7, substr_count((string) $sanitized, '[REDACTED]'));
    }

    public function test_token_key_is_consistent_for_array_and_text_metadata(): void
    {
        $sanitizer = new DeliveryMetadataSanitizer;
        $this->assertSame('[REDACTED]', $sanitizer->sanitize(['ToKeN' => 'abc'])['ToKeN']);
        $this->assertStringNotContainsString('abc', (string) $sanitizer->sanitizeText('token=abc'));
    }
}
