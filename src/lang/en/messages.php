<?php

return [
    'plans_retrieved'=>'Notification plans retrieved.','plan_retrieved'=>'Notification plan retrieved.','plan_cancelled'=>'Notification plan cancelled.','plan_retry_created'=>'A new notification plan was created for manual retry.','attempts_retrieved'=>'Notification attempts retrieved.','attempt_retrieved'=>'Notification attempt retrieved.','dead_letters_retrieved'=>'Dead letters retrieved.','dead_letter_retrieved'=>'Dead letter retrieved.','dead_letter_resolved'=>'Dead letter resolved.','recipient_user'=>'User #:id','recipient_guest'=>'Guest','recipient_role'=>'Role','recipient_webhook'=>'Webhook',
    'status'=>['pending'=>'Pending','scheduled'=>'Scheduled','claimed'=>'Claimed','processing'=>'Processing','accepted'=>'Accepted','retry_scheduled'=>'Retry scheduled','skipped'=>'Skipped','failed'=>'Failed','cancelled'=>'Cancelled','expired'=>'Expired'],
    'module_name' => 'Notification Hub',
    'plans' => [
        'title' => 'Notification Plans',
        'empty' => 'No notification plans are available.',
    ],
    'attempts' => [
        'title' => 'Notification Attempts',
        'empty' => 'No notification attempts are available.',
    ],
    'actions' => [
        'retry' => 'Retry',
    ],
    'dispatch_disabled' => 'Notification Hub scheduling or queue dispatch is disabled.',
    'dispatch_summary' => 'Eligible: :eligibleCount, claimed: :claimedCount, dispatched: :dispatchedCount, failed: :failedDispatchCount.',
    'prune_disabled' => 'Notification Hub scheduled maintenance is disabled.',
    'prune_summary' => 'Candidate plans: :candidatePlans, deleted plans: :deletedPlans, protected dead letters: :protectedUnresolvedDeadLetters.',
];
