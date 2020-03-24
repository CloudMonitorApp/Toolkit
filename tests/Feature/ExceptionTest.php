<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CanSendExceptionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCanSendException()
    {
        $this->assertEquals(1, 1);
    }
}
