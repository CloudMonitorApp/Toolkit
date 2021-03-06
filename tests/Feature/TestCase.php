<?php

namespace CloudMonitor\Toolkit\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase as LaravelTestCase;

abstract class TestCase extends LaravelTestCase
{
    public function testHasCredentials()
    {
        $this->assertNotNull(env('CLOUDMONITOR_SECRET', null));
        $this->assertNotNull(env('CLOUDMONITOR_KEY', null));
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
