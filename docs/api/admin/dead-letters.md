# Dead Letter API

| Method | URI | Permission | Result |
|---|---|---|---|
| GET | `/admin/dead-letters` | `dead-letters.read` | Unresolved-first pagination |
| GET | `/admin/dead-letters/{uuid}` | `dead-letters.read` | Safe failure detail |
| POST | `/admin/dead-letters/{uuid}/resolve` | `dead-letters.resolve` | Record resolution note |
| POST | `/admin/dead-letters/{uuid}/retry` | `dead-letters.retry` | Create a new Plan and resolve, HTTP 201 |

Resolve body: `{"resolution_note":"required, maximum 1000 characters"}`. Retry accepts the same optional `request_id`, reason, schedule, policy, and maximum-attempt fields as Plan retry. Omission generates and returns a server UUID; a supplied UUID enables an idempotent client retry. Replaying the same Dead Letter and `request_id` returns the same new Plan, including after resolution. A different `request_id` against an already resolved Dead Letter returns 409. Activity hooks are emitted only for the first successful creation. The administrator Layout omits `request_id`. Resolution never changes the failed Plan.

Example: `POST /admin/dead-letters/{uuid}/resolve` with `{"resolution_note":"Reviewed by operations"}` returns `{"success":true,"data":{"id":"UUID","resolved":true,"resolved_at":"ISO-8601"}}`.
