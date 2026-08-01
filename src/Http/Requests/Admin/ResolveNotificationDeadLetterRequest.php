<?php
namespace Modules\Glitter\NotificationHub\Http\Requests\Admin;use Illuminate\Foundation\Http\FormRequest;
class ResolveNotificationDeadLetterRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['resolution_note'=>'required|string|max:1000'];} }
