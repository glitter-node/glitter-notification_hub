<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Repositories;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
class PlanClaimRepositoryTest extends TestCase {
 public function test_claim_contract_is_explicit():void{foreach(['claimDuePlans','releaseExpiredClaims','releaseClaim','consumeClaim'] as $m)$this->assertTrue((new ReflectionMethod(PlanRepositoryInterface::class,$m))->isPublic());}
 public function test_claim_consumption_declares_one_locked_transaction_boundary():void{$method=new ReflectionMethod(\Modules\Glitter\NotificationHub\Repositories\PlanRepository::class,'consumeClaim');$source=file($method->getFileName());$body=implode('',array_slice($source,$method->getStartLine()-1,$method->getEndLine()-$method->getStartLine()+1));$this->assertStringContainsString('DB::transaction',$body);$this->assertStringContainsString('lockForUpdate()',$body);$this->assertStringContainsString("'claim_token' => null",$body);$this->assertStringContainsString("'status' => PlanStatus::Processing",$body);}
}
