<?php
namespace Modules\Glitter\NotificationHub\DTO\Admin;
final readonly class NotificationDeadLetterSearchData { public function __construct(public array $filters,public string $sort='unresolved_first',public int $perPage=20){} public static function fromArray(array $v):self{$sort=$v['sort']??'unresolved_first';$per=(int)($v['per_page']??20);unset($v['sort'],$v['per_page']);foreach(['resolved','retry_exhausted']as$k)if(array_key_exists($k,$v))$v[$k]=filter_var($v[$k],FILTER_VALIDATE_BOOLEAN);return new self($v,$sort,$per);} }
