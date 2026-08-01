<?php
namespace Modules\Glitter\NotificationHub\Http\Requests\Admin;use Illuminate\Foundation\Http\FormRequest;
class CancelNotificationPlanRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['reason'=>'nullable|string|max:500'];} }
