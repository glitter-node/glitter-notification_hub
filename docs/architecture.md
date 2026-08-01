# Notification Hub architecture

## Responsibility boundary

The core notification system remains the source of truth for notification definitions,
templates, recipient resolution, rendering, channel delivery, in-app notifications, and
`notification_logs`. Notification Hub adds only delivery plans, delivery attempts,
scheduling intent, retry intent, idempotency, and future Webhook orchestration. Business
modules decide which business event should request a plan; channel providers own the
external transport.

## Notification Plan

A Plan is one intent to deliver one business notification to one recipient through one
channel. Multiple recipients or channels therefore create multiple Plans. Its UUID and
correlation UUID are safe opaque references for APIs and cross-system logs.

The unique identity combines extension type and identifier, core definition type, the
SHA-256 hex digest of the canonical idempotency key, recipient type, a separately
calculated recipient identity digest, and channel. The original idempotency key is never
stored. The database constraint is the final concurrency guard; a later service phase
will translate duplicate-key errors into an explicit duplicate result.

## Delivery Attempt

An Attempt is one individual try to execute a Plan through its selected channel. Attempt
numbers are unique within a Plan. Attempts are audit records and are not exposed to a
general delete workflow.

`accepted` means that the synchronous core `after_channel_send` hook was captured for the
single channel. A return from `notify()` alone is never accepted evidence. It does not
prove remote delivery, reading, or recipient acknowledgement.

`core_notification_log_id` is an optional correlation pointer. It intentionally has no
foreign key so that core log retention and module lifecycle remain independent. The core
log records core delivery behavior; an Attempt records Hub orchestration behavior.

## Privacy and metadata

SHA-256 digests use lowercase hexadecimal `char(64)`, matching existing project schema.
Guest addresses and original idempotency keys must not be stored by default. Snapshot and
metadata fields contain only the minimum values needed for delivery and audit. Request and
response metadata must exclude Authorization, Cookie, secrets, and complete bodies.
Models hide hashes, snapshots, payload data, detailed errors, and provider metadata from
default serialization. Encryption and retention policies remain a later design decision.

## Deferred scope

Webhook and plaintext guest delivery remain unimplemented. Production installation and
MariaDB metadata inspection are complete, while destructive constraint tests, Repository
CRUD/concurrency, queue/scheduler execution, core channel delivery, and activity-log
persistence remain intentionally unexecuted under the operating policy.

## State transitions

Plans move from `pending`, `scheduled`, or `retry_scheduled` to `claimed`. A claimed Plan
may become `processing`, may be restored only to its recorded waiting state, or may become
`skipped`, `cancelled`, or `expired`. A processing Plan may become `accepted`,
`retry_scheduled`, `failed`, or `skipped`. Terminal Plans never move backwards. Manual
retry creates a new Plan rather than reopening the terminal record.

Attempts move from `pending` to `processing` or `cancelled`, then from `processing` to
`accepted`, `failed`, or `cancelled`. Their terminal states are immutable. Attempt number
allocation and Plan attempt-count increment share a Repository transaction and row lock;
deliberate row-lock concurrency testing is not performed against production.

Plan creation uses INSERT-first idempotency. The database unique constraint is the final
race boundary. Only MariaDB/MySQL SQLSTATE `23000` with driver code `1062` is translated,
and the matching existing Plan must then be found. Deliberate duplicate-key execution is
not performed against production data.

## Single-plan delivery

The adapter receives exactly one recipient and one channel. It does not expand roles,
resolve a definition's complete recipient list, select channels, or subscribe to business
hooks. A user is restored only through the core User Repository. Guest delivery remains
blocked because the Plan intentionally has no encrypted plaintext address; role and
Webhook recipients must be expanded before Plan creation.

Definition, template, extension-channel toggle, readiness, and guest-channel policy are
checked through core services before an Attempt is created. Policy denial or unavailable
recipient ends the claimed/consumed Plan as `skipped`, with no Attempt or adapter call.
With an allowed policy, the Orchestrator owns persistent Plan and Attempt transitions; the
hook listener only captures an in-process result.

Correlation uses a process-local scope keyed by notification class/type, extension,
notifiable object identity, and channel. Duplicate live scopes for the same key are
rejected and every adapter path clears its scope in `finally`. This is suitable for one
synchronous notify call in conventional PHP workers, but coroutine-style same-process
parallelism remains unverified. No `_hub` data is inserted because core database
notification resources expose `data` to users.

Core log creation has no ID in hook context and does not feed an ID back to later
listeners, so `core_notification_log_id` remains nullable. Time-based lookup is forbidden
because it could link the wrong log.

The Null adapter remains the default. The core adapter is selected only when both
`enabled` and `allow_external_delivery` are true, and a Plan channel must also be in the
explicit allowlist. `database` is treated as delivery because it writes user data.

## Scheduling, claims, retry, and dead letters

`claimed` separates a scheduler-owned Plan from a Plan actively delivering. A claim stores
its previous waiting state, a UUID token, and the claim time. Repository claiming uses one
transaction with `FOR UPDATE SKIP LOCKED`; MariaDB 10.11 and Laravel 12 support the syntax,
but deliberate concurrent execution is not performed against production. A timed-out claim
with no Attempt is restored only to its recorded prior state.

The scheduler invokes `notification-hub:dispatch-due`; the command claims a bounded batch
and passes only Plan UUID and claim token to a one-try queue Job. Job consumption locks the
Plan row and verifies ID, `claimed` status, and token in the same transaction that changes
the Plan to `processing` and clears claim metadata. A stale or re-claimed token is a no-op.
Actual MariaDB row-lock concurrency remains unverified. Because the current queue
is `sync`, both scheduler and queue-dispatch kill switches default to false. The module
schedule declaration cannot express `withoutOverlapping`; database claiming is therefore
the authoritative overlap boundary. `onOneServer` is not used because the file cache is
not a distributed lock.

Retry uses fixed, deterministic backoff arrays. Only explicit transient text patterns are
eligible; this is conservative classification, not proof of provider semantics. Each retry
creates a new Attempt. Terminal failures optionally create one dead letter per Plan.
Dead letters are Hub audit records and do not replace Laravel `failed_jobs`, which remains
for Job infrastructure failures. Unresolved dead letters are never pruned automatically.

Activation order keeps delivery and schedules disabled through install/update, verifies
production metadata and empty Hub tables read-only, then separately approves any channel
and enables scheduling last.

## Administrator API boundary

Administrator reads use allowlisted filters and sorting and never expose recipient
snapshots/hashes, idempotency or claim values, raw Plan payloads, or Attempt metadata.
Waiting and claimed Plans may be cancelled; cancelling a claim clears its token so a
queued stale Job exits. Processing and terminal Plans cannot be cancelled.

Manual retry never reopens a terminal Plan or reuses an Attempt. Failed, skipped,
cancelled, or expired Plans produce a new Plan linked by `retry_of_plan_id`. The canonical
key includes the original Plan UUID and optional administrator `request_id`; when omitted,
the server generates a fresh UUID. A supplied ID lets network replays converge through the
existing INSERT-first idempotency boundary. Accepted Plans
are excluded due to duplicate-delivery risk. API creation never dispatches a Job.

Resolving a dead letter only records resolution metadata. Dead-letter retry locks the dead
letter, creates the new Plan before resolution, and emits activity hooks only for the
first successful request. Replaying the same `request_id` returns the linked Plan even
after resolution; a different ID after resolution returns conflict. The database unique
constraint remains the creation race boundary. Atomic cross-repository behavior is not
forced in production and requires observation during real use.

The API routes remain the authorization boundary for writes. Administrator Layouts also
put the matching write permission on both each action trigger and its modal, so read-only
roles receive no write action tree. The current core implementation does not have a
special `admin` Gate bypass. Instead, the `admin` role is explicitly assigned every leaf
permission and therefore has effectively complete access; it is not suitable as a
read-only role.

The dedicated `notification_hub_viewer` role has only `plans.read`, `attempts.read`, and
`dead-letters.read`. It has no Hub write permission and is connected with read access to
the parent Notification Hub menu and its three child menus. The existing core
`manager` role is not reused because it already owns broad content and user-management
write permissions. Users are connected to the viewer role only after separate operator
approval.

## 관리자 Layout

관리자 Layout은 Plan, Attempt, Dead Letter의 안전한 Resource 필드만 표시한다. 세부 화면과 상태별 액션 정책은 `docs/design/admin-layouts.md`를 따른다.

수동 재시도 식별자는 클라이언트가 제공하면 재사용하고 생략하면 서버가 UUID를 생성한다. 응답은 실제 식별자를 반환한다. UI 재시도는 새 Plan 생성만 수행하며 Queue 또는 전달 kill switch를 우회하지 않는다.
