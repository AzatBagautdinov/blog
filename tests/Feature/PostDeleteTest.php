<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_post()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $post = Post::factory()->create();

        $response = $this->post('/admin/posts/remove', [
            'id' => $post->id,
        ]);

        $response->assertStatus(302); // redirect после удаления
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
