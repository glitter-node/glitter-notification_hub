# Plans API

| Method | URI | Permission | Result |
|---|---|---|---|
| GET | `/admin/plans` | `plans.read` | Paginated safe Plan summaries |
| GET | `/admin/plans/{uuid}` | `plans.read` | Safe Plan detail |
| POST | `/admin/plans/{uuid}/cancel` | `plans.cancel` | Cancel pending/scheduled/retry-scheduled/claimed Plan |
| POST | `/admin/plans/{uuid}/retry` | `plans.retry` | Create a new Plan, HTTP 201 |

List filters include status, definition/channel/extension/recipient/source identifiers, correlation UUID, date ranges, dead-letter presence, safe search, allowlisted sort, and `per_page` 1–100.

Cancel body: `{"reason":"optional, maximum 500 characters"}`.

Retry body: `{"request_id":"optional UUID","reason":"required","scheduled_at":null,"retry_policy":"standard","max_attempts":6}`. When omitted, the server generates a UUID and returns it. When supplied, reusing the same UUID for the same original Plan converges on the same new Plan. The administrator Layout omits this field, so each UI submission is independent. Accepted Plans cannot be retried, and creating a Plan does not dispatch a Job.

Success is 200 for reads/cancel and 201 for retry. Errors: 403 permission, 404 missing UUID, 409 invalid state, 422 validation.

Example: `GET /admin/plans?status=failed&sort=-created_at&per_page=20` returns `{"success":true,"data":{"data":[{"id":"UUID","status":"failed","attempt_count":1}],"pagination":{"current_page":1,"total":1}}}`. Sensitive snapshots, hashes, payloads, and claim tokens are absent.
