<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Console;
use Modules\Glitter\NotificationHub\Console\Commands\DispatchDueNotificationPlansCommand;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
class DispatchDueNotificationPlansCommandTest extends TestCase { public function test_signature_has_safe_options():void{$p=(new ReflectionClass(DispatchDueNotificationPlansCommand::class))->getDefaultProperties();$this->assertStringContainsString('--dry-run',$p['signature']);$this->assertStringContainsString('--limit=',$p['signature']);} }
