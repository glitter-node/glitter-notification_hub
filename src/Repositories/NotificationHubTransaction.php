<?php
namespace Modules\Glitter\NotificationHub\Repositories;use Illuminate\Support\Facades\DB;use Modules\Glitter\NotificationHub\Contracts\Repositories\NotificationHubTransactionInterface;class NotificationHubTransaction implements NotificationHubTransactionInterface{public function run(callable $callback):mixed{return DB::transaction($callback);}}
