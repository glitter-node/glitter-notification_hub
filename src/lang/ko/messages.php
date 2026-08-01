<?php

return [
    'plans_retrieved'=>'알림 전달 계획을 조회했습니다.','plan_retrieved'=>'알림 전달 계획을 조회했습니다.','plan_cancelled'=>'알림 전달 계획을 취소했습니다.','plan_retry_created'=>'수동 재시도를 위한 새 알림 전달 계획을 생성했습니다.','attempts_retrieved'=>'알림 전달 시도를 조회했습니다.','attempt_retrieved'=>'알림 전달 시도를 조회했습니다.','dead_letters_retrieved'=>'실패 보관함을 조회했습니다.','dead_letter_retrieved'=>'실패 내역을 조회했습니다.','dead_letter_resolved'=>'실패 내역을 해결 처리했습니다.','recipient_user'=>'회원 #:id','recipient_guest'=>'게스트','recipient_role'=>'역할','recipient_webhook'=>'Webhook',
    'status'=>['pending'=>'대기','scheduled'=>'예약','claimed'=>'선점','processing'=>'처리 중','accepted'=>'수락','retry_scheduled'=>'재시도 예약','skipped'=>'건너뜀','failed'=>'실패','cancelled'=>'취소','expired'=>'만료'],
    'module_name' => '알림 허브',
    'plans' => [
        'title' => '알림 전달 계획',
        'empty' => '알림 전달 계획이 없습니다.',
    ],
    'attempts' => [
        'title' => '알림 전달 시도',
        'empty' => '알림 전달 시도가 없습니다.',
    ],
    'actions' => [
        'retry' => '다시 시도',
    ],
    'dispatch_disabled' => '알림 허브 예약 실행 또는 큐 전달이 비활성화되어 있습니다.',
    'dispatch_summary' => '대상 :eligibleCount건, 선점 :claimedCount건, 전달 작업 :dispatchedCount건, 실패 :failedDispatchCount건입니다.',
    'prune_disabled' => '알림 허브 예약 정리 작업이 비활성화되어 있습니다.',
    'prune_summary' => '정리 후보 :candidatePlans건, 삭제 계획 :deletedPlans건, 보호된 데드 레터 :protectedUnresolvedDeadLetters건입니다.',
];
