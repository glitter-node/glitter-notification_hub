<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_hub_dead_letters', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('데드 레터 UUID');
            $table->uuid('plan_id')->unique('uq_nh_dead_letters_plan')->comment('최종 실패한 전달 계획 UUID');
            $table->uuid('attempt_id')->nullable()->comment('마지막 전달 시도 UUID');
            $table->string('reason_code', 100)->comment('최종 실패 사유 코드');
            $table->string('reason_message', 1000)->nullable()->comment('정제된 최종 실패 사유 메시지');
            $table->timestamp('failed_at')->comment('최종 실패 확정 시각');
            $table->boolean('retry_exhausted')->default(false)->comment('재시도 소진 여부');
            $table->timestamp('resolved_at')->nullable()->comment('관리자 해결 처리 시각');
            $table->unsignedBigInteger('resolved_by')->nullable()->comment('해결 처리 사용자 ID');
            $table->string('resolution_note', 1000)->nullable()->comment('정제된 해결 메모');
            $table->timestamp('created_at')->nullable()->comment('데드 레터 생성 시각');
            $table->timestamp('updated_at')->nullable()->comment('데드 레터 수정 시각');

            $table->index(['resolved_at', 'failed_at'], 'idx_nh_dead_letters_resolution');
            $table->foreign('plan_id', 'fk_nh_dead_letters_plan')->references('id')->on('notification_hub_plans')->restrictOnDelete();
            $table->foreign('attempt_id', 'fk_nh_dead_letters_attempt')->references('id')->on('notification_hub_delivery_attempts')->restrictOnDelete();
            $table->foreign('resolved_by', 'fk_nh_dead_letters_resolver')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_hub_dead_letters');
    }
};
