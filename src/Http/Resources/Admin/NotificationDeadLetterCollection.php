<?php
namespace Modules\Glitter\NotificationHub\Http\Resources\Admin;use App\Http\Resources\BaseApiCollection;use Illuminate\Http\Request;
class NotificationDeadLetterCollection extends BaseApiCollection { public function toArray(Request $request):array{return ['data'=>$this->collection->map(fn($x)=>(new NotificationDeadLetterResource($x))->toArray($request)),...$this->paginationMeta()];} }
