<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/posts', [
            'title' => 'My First Post',
            'body' => 'Some content here.',
            'published' => true,
            'category_id' => $category->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('title', 'My First Post')
            ->assertJsonPath('user_id', $user->id);

        $this->assertDatabaseHas('posts', [
            'title' => 'My First Post',
            'user_id' => $user->id,
        ]);
    }

    public function test_guest_cannot_create_post(): void
    {
        $response = $this->postJson('/api/posts', [
            'title' => 'Unauthorized Post',
            'body' => 'Should not be allowed.',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_update_another_users_post(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $post = Post::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser, 'sanctum')->putJson("/api/posts/{$post->id}", [
            'title' => 'Hijacked Title',
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $post->title,
        ]);
    }

    public function test_user_can_update_their_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/posts/{$post->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('title', 'Updated Title');
    }

    public function test_unpublished_posts_do_not_appear_in_public_listing(): void
    {
        Post::factory()->create(['published' => false, 'title' => 'Draft Post']);
        Post::factory()->create(['published' => true, 'title' => 'Published Post']);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonMissing(['title' => 'Draft Post'])
            ->assertJsonFragment(['title' => 'Published Post']);
    }
}
