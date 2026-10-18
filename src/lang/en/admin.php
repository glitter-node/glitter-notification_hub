<?php
return [
 'menus'=>['root'=>'Notification Hub','plans'=>'Delivery Plans','attempts'=>'Delivery Attempts','dead_letters'=>'Dead Letters'],
 'pages'=>['plans'=>['title'=>'Delivery Plans','description'=>'Review notification delivery plans.'],'plan_detail'=>['title'=>'Delivery Plan Details','description'=>'Review a plan without sensitive data.'],'attempts'=>['title'=>'Delivery Attempts','description'=>'Review channel delivery attempts.'],'attempt_detail'=>['title'=>'Delivery Attempt Details'],'dead_letters'=>['title'=>'Dead Letters','description'=>'Review terminal delivery failures.'],'dead_letter_detail'=>['title'=>'Dead Letter Details']],
 'sections'=>['attempts'=>'Delivery Attempts'],
 'filters'=>['all_statuses'=>'All statuses','all_channels'=>'All channels','all_resolution'=>'All resolution states'],
 'actions'=>['view'=>'View','view_plan'=>'View plan','cancel'=>'Cancel','confirm_cancel'=>'Cancel plan','resolve'=>'Resolve','retry'=>'Retry as new plan','view_new_plan'=>'View new plan'],
 'modals'=>['cancel'=>['title'=>'Cancel delivery plan','help'=>'Optionally enter a reason without sensitive information.'],'resolve'=>['title'=>'Resolve dead letter','help'=>'Enter a resolution note without sensitive information.'],'retry'=>['help'=>'Do not enter sensitive information. This creates a new delivery plan without sending it immediately.'],'retry_plan'=>['title'=>'Retry delivery plan'],'retry_dead_letter'=>['title'=>'Retry dead letter']],
 'placeholders'=>['definition_type'=>'Notification type','search'=>'Search','provider'=>'Provider','cancel_reason'=>'Cancellation reason','resolution_note'=>'Resolution note','retry_reason'=>'Retry reason'],
 'help'=>[],
 'empty'=>['plans'=>'No delivery plans found.','attempts'=>'No delivery attempts found.','dead_letters'=>'No dead letters found.'],
 'errors'=>['action_failed'=>'The request could not be completed.','retry_failed'=>'The new delivery plan could not be created.'],
 'success'=>['plan_cancelled'=>'The delivery plan was cancelled.','dead_letter_resolved'=>'The dead letter was marked resolved.','plan_retry_created'=>'A new delivery plan was created.','dead_letter_retry_created'=>'A new delivery plan was created from the dead letter.'],
 'statuses'=>['plan'=>['pending'=>'Pending','scheduled'=>'Scheduled','retry_scheduled'=>'Retry scheduled','claimed'=>'Claimed','processing'=>'Processing','accepted'=>'Accepted','skipped'=>'Skipped','failed'=>'Failed','cancelled'=>'Cancelled','expired'=>'Expired'],'attempt'=>['pending'=>'Pending','processing'=>'Processing','accepted'=>'Accepted','failed'=>'Failed','cancelled'=>'Cancelled']],
 'channels'=>['mail'=>'Mail','database'=>'In-app'], 'boolean'=>['resolved'=>'Resolved','unresolved'=>'Unresolved'], 'retry_policies'=>['none'=>'No retry','standard'=>'Standard','aggressive'=>'Aggressive'],
];
