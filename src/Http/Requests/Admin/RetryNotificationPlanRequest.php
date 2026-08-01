<?php
namespace Modules\Glitter\NotificationHub\Http\Requests\Admin;use Illuminate\Foundation\Http\FormRequest;use Illuminate\Validation\Rule;use Modules\Glitter\NotificationHub\Enums\RetryPolicy;
class RetryNotificationPlanRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['request_id'=>'nullable|uuid','reason'=>'required|string|max:500','scheduled_at'=>'nullable|date','retry_policy'=>['nullable',Rule::enum(RetryPolicy::class)],'max_attempts'=>'nullable|integer|min:1|max:20'];} }
