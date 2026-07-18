<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_redirects_root_to_docs(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302)
            ->assertRedirect('/api/documentation');
    }
}
