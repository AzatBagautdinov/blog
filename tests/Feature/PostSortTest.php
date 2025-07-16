<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostSortTest extends TestCase
{
    use RefreshDatabase;

    public function test_sort_posts_by_title_asc()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Post::factory()->create(['title' => 'Zzz']);
        Post::factory()->create(['title' => 'Aaa']);

        $response = $this->get('/admin/posts?sort=title&direction=asc');
        $response->assertStatus(200);
        $response->assertSeeInOrder(['Aaa', 'Zzz']);
    }
}
