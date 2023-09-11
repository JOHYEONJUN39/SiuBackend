<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['view-tasks']);
    
        $response = $this->post('/api/login', ['id' => $user->id, 'password' => $user->password]);
    
        $response->assertOk();
    }
}
