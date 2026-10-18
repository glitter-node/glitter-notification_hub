# Notification Hub administrator API

Base URI: `/api/modules/glitter-notification_hub/admin`. All endpoints require Sanctum administrator authentication and the listed module permission. Responses use the core response envelope. UUIDs identify resources.

Requests rejected before a module controller by Sanctum use the core authentication handler's shared HTTP 401 JSON shape (`message`). Successful responses and module domain errors use the module Resource and core ResponseHelper contracts.

The API never returns recipient snapshots or hashes, idempotency hashes, claim tokens, raw Plan payload/context/policy, or raw Attempt metadata. `accepted` means the core after-channel hook was observed; it does not mean remote receipt.

- [Plans](admin/plans.md)
- [Attempts](admin/attempts.md)
- [Dead letters](admin/dead-letters.md)
