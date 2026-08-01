<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\DTO\Admin;use Modules\Glitter\NotificationHub\DTO\Admin\NotificationAttemptSearchData;use PHPUnit\Framework\TestCase;class NotificationAttemptSearchDataTest extends TestCase{public function test_pagination_values_are_carried():void{$d=NotificationAttemptSearchData::fromArray(['per_page'=>50]);$this->assertSame(50,$d->perPage);}}
