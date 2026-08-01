<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Console;
use Modules\Glitter\NotificationHub\Console\Commands\PruneNotificationHubCommand;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
class PruneNotificationHubCommandTest extends TestCase { public function test_signature_supports_dry_run():void{$p=(new ReflectionClass(PruneNotificationHubCommand::class))->getDefaultProperties();$this->assertStringContainsString('--dry-run',$p['signature']);} }
