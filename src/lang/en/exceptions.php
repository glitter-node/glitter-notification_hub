<?php
return [
    'admin_resource_not_found' => 'The requested Notification Hub resource was not found.',
    'plan_not_cancellable' => 'The notification plan cannot be cancelled in its current state.',
    'plan_not_retryable' => 'The notification plan cannot be retried in its current state.',
    'dead_letter_already_resolved' => 'The dead letter has already been resolved.',
    'queue_dispatch_disabled' => 'Notification Hub queue dispatch is disabled.',
    'plan_claim_required' => 'The notification plan does not have a valid execution claim.',
    'invalid_plan_transition' => 'Notification plan cannot transition from :from to :to.',
    'invalid_attempt_transition' => 'Delivery attempt cannot transition from :from to :to.',
    'plan_not_found' => 'Notification plan was not found.',
    'plan_already_terminal' => 'A terminal notification plan cannot be changed.',
    'plan_processing' => 'A processing notification plan cannot be cancelled.',
    'duplicate_consistency' => 'The duplicate notification plan could not be resolved consistently.',
    'external_delivery_disabled' => 'External notification delivery is disabled.',
    'recipient_resolution_failed'=>'The notification recipient could not be restored: :reason.','correlation_scope_conflict'=>'A conflicting notification correlation scope is already active.','plan_not_due'=>'The scheduled notification plan is not due yet.',
    'policy_definition_unavailable'=>'The notification definition is unavailable.','policy_channel_not_defined'=>'The channel is not enabled by the definition.','policy_template_unavailable'=>'The channel template is unavailable.','policy_channel_disabled'=>'The extension channel is disabled.','policy_channel_not_ready'=>'The channel is not ready.','policy_guest_not_allowed'=>'The channel does not allow guest recipients.',
];
