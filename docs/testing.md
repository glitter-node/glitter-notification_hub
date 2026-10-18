# Notification Hub testing

## Safety boundary

The project uses its single production environment file. Validation must not create test
Plans, Attempts, Dead Letters, users, notifications, Jobs, Webhooks, or external delivery.
Destructive constraint, rollback, and concurrency experiments are not performed against
production data. Installation and metadata inspection use only the official module
lifecycle and read-only post-install queries.

## Safe validation

- PHP lint, PSR-4, Interface and DI reflection
- JSON/YAML parsing and backend/frontend translation parity
- readonly DTO and Enum contracts
- mocked Repository, core service, adapter, Job, and Orchestrator behavior
- state transition, idempotency hashing, sanitizer, and correlation cleanup
- migration DDL compilation through connection pretend mode
- installed MariaDB table, column, index, comment, and FK metadata reads
- administrator route, permission, menu, Resource, and Layout contracts
- route layout references, JSON `layout_name` values, module sync names, and public
  Layout API lookup names must resolve to the same six administrator layout names
- Layout Vitest with mocked data sources and action dispatch

The production installation has three empty Hub tables. Deliberate duplicate-key, FK
violation, deletion, migration rollback, `SKIP LOCKED` concurrency, and Attempt-number
competition tests remain unexecuted by operating policy and must not be reported as
complete.

## Commands

DB-free Pure/Mock Unit tests:

```bash
/usr/local/bin/php83 vendor/bin/phpunit --bootstrap modules/_bundled/glitter-notification_hub/tests/unit-bootstrap.php modules/_bundled/glitter-notification_hub/tests/Unit --exclude-group database
```

Layout tests:

- three administrator list layouts share the same header, filter, result, empty-state, and full-height page contract
- list inputs are neither disabled nor read-only and update the exact backend-supported filter keys
- Plan `definition_type`/`search`, Attempt `provider`, and Dead Letter `search` are bound to their Request, DTO, and Repository contracts

```bash
cd modules/_bundled/glitter-notification_hub
npm run test:run
```

The Layout harness uses the same AND/removal semantics as the server's official
component `permissions` filter. Read-only cases receive only `plans.read`,
`attempts.read`, and `dead-letters.read`; write cases receive one explicit action
permission at a time. Regression tests verify that an unauthorized trigger, modal, and
its `apiCall` are all absent, while state conditions continue to hide otherwise
authorized actions in incompatible states.

The production read-only role is `notification_hub_viewer`. It has exactly those three
Hub read permissions, no Hub write permissions, and read links to the parent Hub menu and
its three children. No user is attached until an operator separately approves a target
account. The core `manager` role is not a read-only test substitute because it already
contains unrelated content and user-management write permissions.

## Delivery safety

`enabled`, `allow_external_delivery`, scheduler, queue dispatch, and retry all default to
false; allowed channels default to an empty array. Tests use mock APIs and fake adapters.
They do not call actual administrator POST endpoints, Mail, database notifications,
Webhooks, Jobs, workers, or Scheduler commands.
