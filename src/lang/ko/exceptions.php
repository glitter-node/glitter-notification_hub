<?php
return [
    'admin_resource_not_found' => '요청한 알림 허브 리소스를 찾을 수 없습니다.',
    'plan_not_cancellable' => '현재 상태에서는 알림 전달 계획을 취소할 수 없습니다.',
    'plan_not_retryable' => '현재 상태에서는 새 알림 전달 계획으로 재시도할 수 없습니다.',
    'dead_letter_already_resolved' => '이미 해결 처리된 실패 내역입니다.',
    'queue_dispatch_disabled' => '알림 허브 큐 전달이 비활성화되어 있습니다.',
    'plan_claim_required' => '알림 전달 계획에 유효한 실행 선점이 없습니다.',
    'invalid_plan_transition' => '알림 전달 계획 상태를 :from에서 :to(으)로 변경할 수 없습니다.',
    'invalid_attempt_transition' => '전달 시도 상태를 :from에서 :to(으)로 변경할 수 없습니다.',
    'plan_not_found' => '알림 전달 계획을 찾을 수 없습니다.',
    'plan_already_terminal' => '종료된 알림 전달 계획은 변경할 수 없습니다.',
    'plan_processing' => '처리 중인 알림 전달 계획은 취소할 수 없습니다.',
    'duplicate_consistency' => '중복 알림 전달 계획을 일관되게 확인할 수 없습니다.',
    'external_delivery_disabled' => '외부 알림 전달이 비활성화되어 있습니다.',
    'recipient_resolution_failed'=>'알림 수신자를 복원할 수 없습니다: :reason.','correlation_scope_conflict'=>'충돌하는 알림 correlation 범위가 이미 활성화되어 있습니다.','plan_not_due'=>'예약된 알림 전달 계획의 실행 시각이 아직 되지 않았습니다.',
    'policy_definition_unavailable'=>'알림 정의를 사용할 수 없습니다.','policy_channel_not_defined'=>'알림 정의에서 채널이 활성화되지 않았습니다.','policy_template_unavailable'=>'채널 템플릿을 사용할 수 없습니다.','policy_channel_disabled'=>'확장 채널이 비활성화되어 있습니다.','policy_channel_not_ready'=>'채널이 준비되지 않았습니다.','policy_guest_not_allowed'=>'채널이 게스트 수신자를 허용하지 않습니다.',
];
