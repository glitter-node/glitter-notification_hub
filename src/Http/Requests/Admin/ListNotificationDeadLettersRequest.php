<?php
namespace Modules\Glitter\NotificationHub\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;use Illuminate\Validation\Rule;
class ListNotificationDeadLettersRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['resolved'=>'nullable|boolean','retry_exhausted'=>'nullable|boolean','reason_code'=>'nullable|string|max:100','failed_from'=>'nullable|date','failed_to'=>'nullable|date|after_or_equal:failed_from','plan_id'=>'nullable|uuid','attempt_id'=>'nullable|uuid','search'=>'nullable|string|max:191','sort'=>['nullable',Rule::in(['unresolved_first','failed_at','-failed_at','resolved_at','-resolved_at','created_at','-created_at'])],'per_page'=>'nullable|integer|min:1|max:100'];} }
