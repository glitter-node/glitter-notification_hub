<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_hub_delivery_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('알림 전달 시도 UUID');
            $table->uuid('plan_id')->comment('알림 전달 계획 UUID');
            $table->unsignedSmallInteger('attempt_number')->comment('계획 내 전달 시도 순번');
            $table->string('status', 32)->default('pending')->comment('전달 시도 상태');
            $table->string('provider', 100)->nullable()->comment('메일러 또는 Webhook 제공자 식별자');
            $table->timestamp('started_at')->nullable()->comment('전달 시도 시작 시각');
            $table->timestamp('completed_at')->nullable()->comment('전달 시도 종료 시각');
            $table->unsignedInteger('duration_ms')->nullable()->comment('전달 처리 시간(밀리초)');
            $table->boolean('retryable')->default(false)->comment('재시도 가능 여부');
            $table->timestamp('next_retry_at')->nullable()->comment('다음 재시도 예정 시각');
            $table->string('error_category', 50)->nullable()->comment('마스킹된 오류 분류');
            $table->string('error_code', 100)->nullable()->comment('마스킹된 오류 코드');
            $table->string('error_message', 1000)->nullable()->comment('길이와 민감정보를 제한한 오류 메시지');
            $table->string('provider_message_id', 191)->nullable()->comment('제공자가 반환한 메시지 식별자');
            $table->unsignedSmallInteger('response_status')->nullable()->comment('제공자 응답 상태 코드');
            $table->text('request_metadata')->nullable()->comment('비밀값과 본문을 제외한 요청 메타데이터');
            $table->text('response_metadata')->nullable()->comment('비밀값과 본문을 제외한 응답 메타데이터');
            $table->unsignedBigInteger('core_notification_log_id')->nullable()->comment('연결된 코어 알림 로그 ID');
            $table->timestamp('created_at')->nullable()->comment('전달 시도 생성 시각');
            $table->timestamp('updated_at')->nullable()->comment('전달 시도 수정 시각');

            $table->unique(['plan_id', 'attempt_number'], 'uq_nh_attempts_plan_number');
            $table->index(['status', 'next_retry_at'], 'idx_nh_attempts_status_retry');
            $table->index('core_notification_log_id', 'idx_nh_attempts_core_log');
            $table->index('provider_message_id', 'idx_nh_attempts_provider_message');

            $table->foreign('plan_id', 'fk_nh_attempts_plan')
                ->references('id')->on('notification_hub_plans')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_hub_delivery_attempts');
    }
};
