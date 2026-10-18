<?php
namespace Modules\Glitter\NotificationHub\DTO\Admin;
final readonly class NotificationAttemptSearchData { public function __construct(public array $filters,public string $sort='-created_at',public int $perPage=20){} public static function fromArray(array $v):self{$sort=$v['sort']??'-created_at';$per=(int)($v['per_page']??20);unset($v['sort'],$v['per_page']);if(array_key_exists('retryable',$v))$v['retryable']=filter_var($v['retryable'],FILTER_VALIDATE_BOOLEAN);return new self($v,$sort,$per);} }
