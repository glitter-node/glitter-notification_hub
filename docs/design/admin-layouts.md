# 관리자 Layout

관리자 화면은 전달 계획, 전달 시도, Dead Letter의 목록과 상세를 읽기 전용 API로 조회한다. 계획 취소·수동 재시도와 Dead Letter 해결·수동 재시도는 공식 `apiCall` handler로 제공하며 성공 후 해당 data source를 다시 불러온다.

세 목록 화면은 `admin-page-content` 최상위 wrapper, `PageHeader`, `admin-card` 필터, 결과 영역 순서로 통일한다. 필터 행은 공식 flex wrapping 패턴을 사용하며 모든 입력은 보이는 label, 상태 변경 action, 실제 Request/DTO/Repository query key를 갖는다. 자유 문자열 입력은 Enter 또는 필터 적용 버튼으로 조회하고, Select는 변경 즉시 조회한다. 실패 보관함의 `search`는 민감 payload가 아니라 사유 코드와 Plan/Attempt ID만 검색한다.

모듈 route의 `layout` 값, `resources/layouts/admin`의 파일 stem, JSON의 `layout_name`은 동일해야 한다. 코어는 route와 등록 양쪽에 `glitter-notification_hub.` 접두사를 붙이므로 `plans`는 `glitter-notification_hub.plans`로 등록·조회된다. 여섯 관리자 Layout 모두 이 계약을 따르며 `/api/layouts/sirsoft-admin_basic/{등록명}.json`에서 조회된다.

레이아웃은 hash, claim token, recipient snapshot, raw data/context/policy 및 request/response metadata를 참조하지 않는다. `accepted`는 원격 수신 완료가 아니라 코어 채널의 처리 수락을 뜻한다.

수동 재시도 API의 `request_id`는 선택값이다. Layout은 UUID를 생성하거나 전송하지 않으며 서버가 요청마다 UUID를 생성한다. API 클라이언트가 네트워크 재요청의 멱등성을 원하면 UUID를 직접 제공할 수 있다. 상세 화면은 재시도 가능한 상태에서만 모달을 표시하고 새 Plan만 생성한다.

렌더링과 API 액션은 Mock 환경에서 검증하며 실제 API, DB, Queue, Notification에는 연결하지 않는다. 설정 API가 아직 없으므로 설정 화면과 메뉴도 공개하지 않는다.

## 권한 가시성

Layout 전체의 read permission과 별개로 모든 쓰기 trigger와 modal 본체는 대응 write permission을 가진다. 서버가 Layout을 제공할 때 `permissions` 배열을 AND 조건으로 평가하며, 미보유 컴포넌트와 하위 action을 응답 트리에서 제거한다. 상태 `if` 조건도 함께 유지하므로 권한과 상태가 모두 허용될 때만 작업이 보인다.

현재 코어 구현에는 `admin` 전용 Gate bypass가 없다. 대신 `admin` 역할에 모든 leaf permission이 명시적으로 배정되어 사실상 전체 접근 권한을 가지므로 read-only 제한 대상으로 사용하지 않는다.

조회 전용 역할은 `notification_hub_viewer`를 사용한다. 이 역할에는 `plans.read`, `attempts.read`, `dead-letters.read`만 배정하고, `plans.cancel`, `plans.retry`, `dead-letters.resolve`, `dead-letters.retry`는 배정하지 않는다. 기존 `manager` 역할은 콘텐츠·사용자 관리 쓰기 권한을 이미 보유하므로 Hub 조회 전용 역할로 재사용하지 않는다. 상위 `알림 허브` 메뉴와 하위 메뉴 3개에는 별도의 menu read 연결이 필요하며, 실제 사용자는 운영자의 별도 승인 후 역할에 연결한다.
