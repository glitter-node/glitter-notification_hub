<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\DTO\Admin;use Modules\Glitter\NotificationHub\DTO\Admin\NotificationDeadLetterSearchData;use PHPUnit\Framework\TestCase;class NotificationDeadLetterSearchDataTest extends TestCase{public function test_unresolved_first_is_default():void{$this->assertSame('unresolved_first',NotificationDeadLetterSearchData::fromArray([])->sort);}}
