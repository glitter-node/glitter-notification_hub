<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Modules\Glitter\NotificationHub\DTO\Admin\NotificationAttemptSearchData;
use Modules\Glitter\NotificationHub\DTO\Admin\NotificationDeadLetterSearchData;
use Modules\Glitter\NotificationHub\DTO\Admin\NotificationPlanSearchData;
use Modules\Glitter\NotificationHub\Http\Requests\Admin\ListNotificationAttemptsRequest;
use Modules\Glitter\NotificationHub\Http\Requests\Admin\ListNotificationDeadLettersRequest;
use Modules\Glitter\NotificationHub\Http\Requests\Admin\ListNotificationPlansRequest;

class LayoutRegistrationContractTest extends TestCase
{
    private const MODULE_IDENTIFIER = 'glitter-notification_hub';

    /** @return array<string, array{string, string}> */
    public static function adminLayouts(): array
    {
        return [
            'plans' => ['plans', 'glitter-notification_hub.plans'],
            'plan detail' => ['plan_detail', 'glitter-notification_hub.plan_detail'],
            'attempts' => ['attempts', 'glitter-notification_hub.attempts'],
            'attempt detail' => ['attempt_detail', 'glitter-notification_hub.attempt_detail'],
            'dead letters' => ['dead_letters', 'glitter-notification_hub.dead_letters'],
            'dead letter detail' => ['dead_letter_detail', 'glitter-notification_hub.dead_letter_detail'],
        ];
    }

    #[DataProvider('adminLayouts')]
    public function test_route_sync_and_api_lookup_names_are_identical(
        string $baseName,
        string $expectedRegisteredName,
    ): void {
        $root = dirname(__DIR__, 2);
        $routes = json_decode(
            file_get_contents($root.'/resources/routes.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $matchingRoutes = array_values(array_filter(
            $routes['routes'],
            static fn (array $route): bool => ($route['layout'] ?? null) === $baseName,
        ));

        $this->assertCount(1, $matchingRoutes, "Missing or duplicate route layout: {$baseName}");

        $layoutPath = $root.'/resources/layouts/admin/'.$baseName.'.json';
        $this->assertFileExists($layoutPath);
        $layout = json_decode(file_get_contents($layoutPath), true, 512, JSON_THROW_ON_ERROR);

        // ModuleManager prefixes both values with the module identifier. They must share
        // the same base name or the router requests a name that was never registered.
        $this->assertSame($baseName, $layout['layout_name'] ?? null);

        $routeLookupName = self::MODULE_IDENTIFIER.'.'.$matchingRoutes[0]['layout'];
        $syncRegistrationName = self::MODULE_IDENTIFIER.'.'.$layout['layout_name'];

        $this->assertSame($expectedRegisteredName, $routeLookupName);
        $this->assertSame($expectedRegisteredName, $syncRegistrationName);
        $this->assertSame(
            '/api/layouts/sirsoft-admin_basic/'.$expectedRegisteredName.'.json',
            '/api/layouts/sirsoft-admin_basic/'.$routeLookupName.'.json',
        );
    }

    /** @return array<string, array{string, class-string, class-string, string, list<string>}> */
    public static function listFilterContracts(): array
    {
        return [
            'plans' => ['plans', ListNotificationPlansRequest::class, NotificationPlanSearchData::class, 'PlanRepository.php', ['status', 'channel', 'definition_type', 'search']],
            'attempts' => ['attempts', ListNotificationAttemptsRequest::class, NotificationAttemptSearchData::class, 'AttemptRepository.php', ['status', 'provider']],
            'dead letters' => ['dead_letters', ListNotificationDeadLettersRequest::class, NotificationDeadLetterSearchData::class, 'DeadLetterRepository.php', ['resolved', 'search']],
        ];
    }

    #[DataProvider('listFilterContracts')]
    public function test_list_layout_filters_match_request_dto_and_repository_contracts(
        string $layoutName,
        string $requestClass,
        string $dtoClass,
        string $repositoryFile,
        array $filterKeys,
    ): void {
        $root = dirname(__DIR__, 2);
        $layout = json_decode(file_get_contents($root.'/resources/layouts/admin/'.$layoutName.'.json'), true, 512, JSON_THROW_ON_ERROR);
        $params = $layout['data_sources'][0]['params'] ?? [];
        $rules = (new $requestClass)->rules();
        $values = array_fill_keys($filterKeys, 'sample');
        $dto = $dtoClass::fromArray($values);
        $repository = file_get_contents($root.'/src/Repositories/'.$repositoryFile);

        foreach ($filterKeys as $key) {
            $this->assertArrayHasKey($key, $params, "Layout query is missing {$layoutName}.{$key}");
            $this->assertArrayHasKey($key, $rules, "FormRequest is missing {$layoutName}.{$key}");
            $this->assertArrayHasKey($key, $dto->filters, "DTO dropped {$layoutName}.{$key}");
            $this->assertStringContainsString("'{$key}'", $repository, "Repository does not handle {$layoutName}.{$key}");
        }
    }

    public function test_list_layouts_share_safe_interactive_page_structure(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['plans', 'attempts', 'dead_letters'] as $layoutName) {
            $layout = json_decode(file_get_contents($root.'/resources/layouts/admin/'.$layoutName.'.json'), true, 512, JSON_THROW_ON_ERROR);
            $serialized = json_encode($layout, JSON_THROW_ON_ERROR);
            $page = $layout['slots']['content'][0] ?? [];

            $this->assertStringContainsString('admin-page-content', $page['props']['className'] ?? '');
            $this->assertStringNotContainsString('"style"', $serialized);
            $this->assertStringNotContainsString('"disabled":true', $serialized);
            $this->assertStringNotContainsString('"readOnly":true', $serialized);
            $this->assertStringNotContainsString('"method":"POST"', $serialized);
            $this->assertDoesNotMatchRegularExpression('/recipient_snapshot|claim_token|request_metadata|response_metadata/', $serialized);
        }
    }
}
