# Attempts API

| Method | URI | Permission |
|---|---|---|
| GET | `/admin/attempts` | `attempts.read` |
| GET | `/admin/attempts/{uuid}` | `attempts.read` |

Filters: Plan UUID, status, provider, retryable, error category/code, created range, safe search, allowlisted sort, and `per_page` 1–100. Responses exclude request and response metadata so Authorization, cookies, secrets, and payload bodies cannot leak. Success is 200; missing resources return 404.

Example: `GET /admin/attempts?plan_id=UUID` returns `{"success":true,"data":{"data":[{"id":"UUID","plan_id":"UUID","status":"failed","error_code":"timeout"}]}}`.
