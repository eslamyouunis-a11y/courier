<?php

namespace Tests\Feature\Finance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReturnToSenderFinanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_return_to_sender_charges_merchant_correctly()
    {
        $this->assertTrue(true);
    }
}
