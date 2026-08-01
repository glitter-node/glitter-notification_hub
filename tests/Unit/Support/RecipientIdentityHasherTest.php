<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Support;
use Modules\Glitter\NotificationHub\DTO\NotificationPlanData;
use Modules\Glitter\NotificationHub\Enums\RetryPolicy;
use Modules\Glitter\NotificationHub\Support\RecipientIdentityHasher;
use PHPUnit\Framework\TestCase;
class RecipientIdentityHasherTest extends TestCase
{
    public function test_user_and_guest_namespaces_are_stable_and_distinct(): void { $h=new RecipientIdentityHasher; $u=$this->data('user',7,null); $g=$this->data('guest',null,hash('sha256','7')); $this->assertSame($h->hash($u),$h->hash($u)); $this->assertNotSame($h->hash($u),$h->hash($g)); $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/',$h->hash($g)); }
    private function data(string $type,?int $id,?string $hash): NotificationPlanData { return new NotificationPlanData('x','x','module','m',null,null,$type,$id,$hash,[],'mail',[],[],[],'key',RetryPolicy::None,1,null,null,null); }
}
