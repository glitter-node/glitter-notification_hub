<?php
namespace Modules\Glitter\NotificationHub\Services;
use App\Contracts\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Modules\Glitter\NotificationHub\Contracts\PlanRecipientResolverInterface;
use Modules\Glitter\NotificationHub\DTO\ResolvedPlanRecipientData;
use Modules\Glitter\NotificationHub\Exceptions\PlanRecipientResolutionException;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
class PlanRecipientResolver implements PlanRecipientResolverInterface
{
    public function __construct(private readonly UserRepositoryInterface $users) {}
    public function resolve(NotificationPlan $plan): ResolvedPlanRecipientData
    {
        if($plan->recipient_type!=='user')throw new PlanRecipientResolutionException($plan->recipient_type==='guest'?'guest_plaintext_address_unavailable':'unsupported_recipient_type');
        if($plan->recipient_id===null||($user=$this->users->findById((int)$plan->recipient_id))===null)throw new PlanRecipientResolutionException('user_not_found');
        $locale=$user instanceof HasLocalePreference?$user->preferredLocale():null;
        return new ResolvedPlanRecipientData($user,'user',(int)$user->getKey(),$user->email??null,$locale,false);
    }
}
