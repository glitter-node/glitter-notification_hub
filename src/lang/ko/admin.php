<?php
return [
 'menus'=>['root'=>'알림 허브','plans'=>'전달 계획','attempts'=>'전달 시도','dead_letters'=>'실패 보관함'],
 'pages'=>['plans'=>['title'=>'전달 계획','description'=>'알림 전달 계획을 조회합니다.'],'plan_detail'=>['title'=>'전달 계획 상세','description'=>'민감 정보를 제외한 전달 계획을 확인합니다.'],'attempts'=>['title'=>'전달 시도','description'=>'채널별 전달 시도를 조회합니다.'],'attempt_detail'=>['title'=>'전달 시도 상세'],'dead_letters'=>['title'=>'실패 보관함','description'=>'최종 실패 내역을 조회합니다.'],'dead_letter_detail'=>['title'=>'실패 보관함 상세']],
 'sections'=>['attempts'=>'전달 시도'],
 'filters'=>['all_statuses'=>'모든 상태','all_channels'=>'모든 채널','all_resolution'=>'모든 해결 상태'],
 'actions'=>['view'=>'상세','view_plan'=>'계획 보기','cancel'=>'취소','confirm_cancel'=>'계획 취소','resolve'=>'해결 표시','retry'=>'새 계획으로 재시도','view_new_plan'=>'새 계획 보기'],
 'modals'=>['cancel'=>['title'=>'전달 계획 취소','help'=>'민감 정보를 제외한 취소 사유를 입력할 수 있습니다.'],'resolve'=>['title'=>'실패 항목 해결','help'=>'민감 정보를 제외한 해결 내용을 입력하세요.'],'retry'=>['help'=>'민감정보를 입력하지 마세요. 새 전달 계획만 생성되며 즉시 발송되지 않습니다.'],'retry_plan'=>['title'=>'전달 계획 재시도'],'retry_dead_letter'=>['title'=>'실패 항목 재시도']],
 'placeholders'=>['definition_type'=>'알림 유형','search'=>'검색','provider'=>'Provider','cancel_reason'=>'취소 사유','resolution_note'=>'해결 내용','retry_reason'=>'재시도 이유'],
 'help'=>[],
 'empty'=>['plans'=>'전달 계획이 없습니다.','attempts'=>'전달 시도가 없습니다.','dead_letters'=>'실패 항목이 없습니다.'],
 'errors'=>['action_failed'=>'요청을 처리하지 못했습니다.','retry_failed'=>'새 전달 계획을 생성하지 못했습니다.'],
 'success'=>['plan_cancelled'=>'전달 계획을 취소했습니다.','dead_letter_resolved'=>'실패 항목을 해결 상태로 표시했습니다.','plan_retry_created'=>'새 전달 계획을 생성했습니다.','dead_letter_retry_created'=>'실패 항목에서 새 전달 계획을 생성했습니다.'],
 'statuses'=>['plan'=>['pending'=>'대기','scheduled'=>'예약','retry_scheduled'=>'재시도 예약','claimed'=>'선점','processing'=>'처리 중','accepted'=>'수락','skipped'=>'건너뜀','failed'=>'실패','cancelled'=>'취소','expired'=>'만료'],'attempt'=>['pending'=>'대기','processing'=>'처리 중','accepted'=>'수락','failed'=>'실패','cancelled'=>'취소']],
 'channels'=>['mail'=>'메일','database'=>'앱 알림'], 'boolean'=>['resolved'=>'해결됨','unresolved'=>'미해결'], 'retry_policies'=>['none'=>'재시도 없음','standard'=>'표준','aggressive'=>'적극적'],
];
