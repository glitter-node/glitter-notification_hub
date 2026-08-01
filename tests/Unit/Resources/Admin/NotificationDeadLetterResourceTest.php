<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Resources\Admin;use PHPUnit\Framework\TestCase;class NotificationDeadLetterResourceTest extends TestCase{public function test_resource_uses_base_api_resource():void{$this->assertTrue(is_subclass_of(\Modules\Glitter\NotificationHub\Http\Resources\Admin\NotificationDeadLetterResource::class,\App\Http\Resources\BaseApiResource::class));}}
