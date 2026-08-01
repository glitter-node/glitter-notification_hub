<?php
namespace Modules\Glitter\NotificationHub\Http\Resources\Admin;use App\Http\Resources\BaseApiCollection;use Illuminate\Http\Request;
class NotificationPlanCollection extends BaseApiCollection { public function toArray(Request $request):array{return ['data'=>$this->collection->map(fn($x)=>(new NotificationPlanResource($x))->toArray($request)),...$this->paginationMeta()];} }
