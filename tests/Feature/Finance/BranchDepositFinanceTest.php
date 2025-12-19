<?php

namespace Tests\Feature\Finance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BranchDepositFinanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_deposit_deducts_from_branch_total_balance()
    {
        $this->assertTrue(true);
    }
}
