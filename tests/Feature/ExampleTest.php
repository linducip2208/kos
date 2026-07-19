<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_page_returns_ok(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_booking_requires_valid_property(): void
    {
        $this->get('/booking/9999')->assertNotFound();
    }
}
