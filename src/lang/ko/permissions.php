<?php

return [
    'plans' => ['name' => '전달 계획', 'description' => '전달 계획 권한', 'read' => '전달 계획 조회', 'cancel' => '전달 계획 취소', 'retry' => '전달 계획 재시도'],
    'attempts' => ['name' => '전달 시도', 'description' => '전달 시도 권한', 'read' => '전달 시도 조회'],
    'dead-letters' => ['name' => '실패 보관함', 'description' => '실패 보관함 권한', 'read' => '실패 내역 조회', 'resolve' => '실패 내역 해결', 'retry' => '실패 내역 재시도'],
];
