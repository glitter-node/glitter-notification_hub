# Changelog

## [0.8.5] - 2026-08-02

### Changed

- 관리자 메뉴의 상위 명칭을 모듈 표시명과 동일한 “알림 허브”로 통일했습니다.

## [0.8.4] - 2026-08-02

### Changed

- 관리자 알림 전달 목록의 필터 입력 상태와 화면 간격을 일관되게 개선했습니다.
- 실패 보관함에 설명과 검색 필터를 보완하고 빈 목록 화면의 높이와 여백을 정리했습니다.

## [0.8.3] - 2026-08-02

### Fixed

- 관리자 알림 전달 화면이 Layout을 찾지 못해 열리지 않던 문제를 수정했습니다.

## [0.8.2] - 2026-08-02

### Security

- 조회 권한만 가진 관리자의 화면에서 취소·재시도·해결 작업이 노출되지 않도록 개선했습니다.
- 각 관리자 작업이 해당 쓰기 권한을 가진 경우에만 표시되도록 보호했습니다.

## [0.8.1] - 2026-08-02

### Fixed

- Fixed module translation registration during installation.
- Fixed policy-based skips and protected claimed plans from stale queue work.
- Improved idempotent administrator retries for resolved delivery failures.
- Expanded masking of authentication credentials in errors and administrator notes.

## [0.6.0] - 2026-08-01

### Added

- Added administrator access to delivery plans, attempts, and terminal failure records.
- Added safe cancellation and new-plan manual retry workflows without immediate delivery.
- Repeated manual retry requests now converge using an administrator-provided request identifier.
- Protected recipient snapshots, hashes, claim tokens, and raw delivery metadata from administrator API responses.

## [0.5.0] - 2026-08-01

### Added

- Added safe claiming of due notification plans to reduce duplicate scheduled execution.
- Added policy-based retry scheduling for explicitly recognized transient delivery failures.
- Added separate tracking for terminal delivery failures after retries are unavailable or exhausted.
- Scheduled processing and queue dispatch remain disabled until explicitly enabled.

## [0.4.0] - 2026-08-01

### Added

- Added safe one-at-a-time processing of delivery plans through core notification channels.
- Inactive definitions, templates, or channels are now skipped before creating a delivery attempt.
- Delivery remains blocked unless both the Hub and external delivery are explicitly enabled.

## [0.3.0] - 2026-08-01

### Added

- Improved protection against duplicate notification delivery plans.
- Added consistent lifecycle tracking for delivery plans and individual attempts.
- Prevented accidental external delivery while no delivery adapter is explicitly enabled.

## [0.2.0] - 2026-08-01

### Added

- Added persistent notification plans and delivery attempt audit records.
- Added stable statuses, retry policy values, and repository contracts for orchestration data.
- Added privacy-conscious idempotency and recipient identity tracking.

## [0.1.0] - 2026-08-01

### Added

- Added the initial module skeleton.
# 0.7.0

- 전달 계획과 시도 내역, 최종 실패 항목을 관리자 화면에서 조회할 수 있도록 추가했습니다.
- 대기 중인 전달 계획을 관리자 화면에서 안전하게 취소할 수 있도록 추가했습니다.
- 내부 식별값과 민감한 수신자 정보가 관리자 화면에 노출되지 않도록 보호했습니다.
# 0.8.0

- 실패하거나 취소된 전달 계획을 관리자 화면에서 새 계획으로 다시 요청할 수 있도록 추가했습니다.
- 수동 재시도 요청 식별값을 서버에서 안전하게 생성하고 응답하도록 개선했습니다.
- 동일한 요청 식별값을 제공하면 중복 재시도가 발생하지 않도록 보호했습니다.
