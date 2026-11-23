<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_terms_page_loads(): void
    {
        $this->get('/syarat-ketentuan')
            ->assertOk()
            ->assertSee('Syarat', false);
    }

    public function test_privacy_page_loads(): void
    {
        $this->get('/kebijakan-privasi')
            ->assertOk()
            ->assertSee('Privasi', false);
    }
}
