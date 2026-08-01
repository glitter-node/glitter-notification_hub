<?php

namespace Modules\Glitter\NotificationHub\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RetryNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
