<?php
namespace Modules\Glitter\NotificationHub\Tests\Feature\Admin;use Tests\TestCase;class NotificationAttemptControllerTest extends TestCase{public function test_attempt_route_contract_is_declared():void{$s=file_get_contents(dirname(__DIR__,3).'/src/routes/api.php');$this->assertStringContainsString("prefix('attempts')",$s);$this->assertStringContainsString("whereUuid('attempt')",$s);}}
