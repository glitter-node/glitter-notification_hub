<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_hub_plans', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('알림 전달 계획 UUID');
            $table->uuid('correlation_id')->unique('uq_nh_plans_correlation')->comment('외부 및 내부 로그 연결용 UUID');
            $table->string('definition_type', 100)->comment('코어 알림 정의 유형');
            $table->string('hook_prefix', 100)->comment('코어 알림 훅 접두사');
            $table->string('extension_type', 20)->comment('알림을 요청한 확장 유형');
            $table->string('extension_identifier', 100)->comment('알림을 요청한 확장 식별자');
            $table->string('source_type', 100)->nullable()->comment('업무 원본 객체 유형');
            $table->string('source_id', 191)->nullable()->comment('업무 원본 객체 식별자');
            $table->string('recipient_type', 32)->comment('수신자 유형');
            $table->unsignedBigInteger('recipient_id')->nullable()->comment('회원 수신자 ID');
            $table->char('recipient_identifier_hash', 64)->nullable()->comment('비회원 수신 식별자의 SHA-256 해시');
            $table->char('recipient_identity_hash', 64)->comment('중복 방지용 수신자 정규화 식별자 SHA-256 해시');
            $table->text('recipient_snapshot')->nullable()->comment('최소화된 수신자 스냅샷');
            $table->string('channel', 50)->comment('전달 채널 식별자');
            $table->text('data')->nullable()->comment('전달에 필요한 최소 알림 데이터');
            $table->text('context')->nullable()->comment('전달에 필요한 최소 실행 문맥');
            $table->text('policy_snapshot')->nullable()->comment('계획 생성 시점의 최소 정책 스냅샷');
            $table->char('idempotency_hash', 64)->comment('원본을 저장하지 않는 멱등성 키 SHA-256 해시');
            $table->string('status', 32)->default('pending')->comment('알림 전달 계획 상태');
            $table->string('claimed_from_status', 32)->nullable()->comment('선점 직전 실행 대기 상태');
            $table->uuid('claim_token')->nullable()->comment('선점 소유권 검증 UUID');
            $table->timestamp('claimed_at')->nullable()->comment('실행 계획 선점 시각');
            $table->string('retry_policy', 32)->default('none')->comment('재시도 정책 식별자');
            $table->unsignedSmallInteger('attempt_count')->default(0)->comment('현재까지 생성된 전달 시도 수');
            $table->unsignedSmallInteger('max_attempts')->default(1)->comment('허용할 최대 전달 시도 수');
            $table->timestamp('scheduled_at')->nullable()->comment('예약 실행 시각');
            $table->timestamp('next_attempt_at')->nullable()->comment('다음 재시도 예정 시각');
            $table->timestamp('processing_started_at')->nullable()->comment('현재 처리 시작 시각');
            $table->timestamp('accepted_at')->nullable()->comment('코어 채널이 예외 없이 수락한 시각');
            $table->timestamp('failed_at')->nullable()->comment('최종 실패 확정 시각');
            $table->timestamp('cancelled_at')->nullable()->comment('취소 시각');
            $table->timestamp('expires_at')->nullable()->comment('계획 만료 시각');
            $table->string('last_error_code', 100)->nullable()->comment('마지막 오류 코드');
            $table->string('last_error_message', 1000)->nullable()->comment('길이를 제한한 마지막 오류 메시지');
            $table->unsignedBigInteger('created_by')->nullable()->comment('계획 생성 요청 사용자 ID');
            $table->uuid('retry_of_plan_id')->nullable()->comment('수동 재시도 원본 계획 UUID');
            $table->string('manual_retry_reason', 500)->nullable()->comment('정제된 수동 재시도 사유');
            $table->unsignedBigInteger('manual_retry_by')->nullable()->comment('수동 재시도 요청 관리자 ID');
            $table->string('cancel_reason', 500)->nullable()->comment('정제된 취소 사유');
            $table->unsignedBigInteger('cancelled_by')->nullable()->comment('취소 요청 관리자 ID');
            $table->timestamp('created_at')->nullable()->comment('계획 생성 시각');
            $table->timestamp('updated_at')->nullable()->comment('계획 수정 시각');

            $table->unique([
                'extension_type',
                'extension_identifier',
                'definition_type',
                'idempotency_hash',
                'recipient_type',
                'recipient_identity_hash',
                'channel',
            ], 'uq_nh_plans_idempotency_identity');
            $table->index(['status', 'scheduled_at'], 'idx_nh_plans_status_scheduled');
            $table->index(['status', 'next_attempt_at'], 'idx_nh_plans_status_next');
            $table->index(['status', 'claimed_at'], 'idx_nh_plans_status_claimed');
            $table->unique('claim_token', 'uq_nh_plans_claim_token');
            $table->index(['extension_type', 'extension_identifier'], 'idx_nh_plans_extension');
            $table->index('recipient_id', 'idx_nh_plans_recipient');
            $table->index('channel', 'idx_nh_plans_channel');
            $table->index('expires_at', 'idx_nh_plans_expires');
            $table->index('retry_of_plan_id', 'idx_nh_plans_retry_of');

            $table->foreign('recipient_id', 'fk_nh_plans_recipient')
                ->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by', 'fk_nh_plans_creator')
                ->references('id')->on('users')->nullOnDelete();
            $table->foreign('manual_retry_by', 'fk_nh_plans_retry_actor')->references('id')->on('users')->nullOnDelete();
            $table->foreign('cancelled_by', 'fk_nh_plans_cancel_actor')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_hub_plans');
    }
};
