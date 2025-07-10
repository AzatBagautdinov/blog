<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyPostsTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('TestToken')->plainTextToken;

        // Создаем 5 постов текущего пользователя
        Post::factory()->count(5)->create(['user_id' => $this->user->id]);

        // И 3 поста другого пользователя
        Post::factory()->count(3)->create();
    }

    public function test_my_posts_success()
    {
        $response = $this->withToken($this->token)->getJson('/api/myposts');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json());
    }

    public function test_my_posts_unauthorized()
    {
        $response = $this->getJson('/api/myposts');
        $response->assertStatus(401);
    }

    public function test_my_posts_filter_by_date()
    {
        Post::query()->delete();
        $user = $this->user;

        Post::factory()->create([
            'user_id' => $user->id,
            'title' => '2023 Post',
            'created_at' => now()->subYears(2)
        ]);

        Post::factory()->create([
            'user_id' => $user->id,
            'title' => '2024 Post',
            'created_at' => now()->subYear()
        ]);

        Post::factory()->create([
            'user_id' => $user->id,
            'title' => '2025 Post',
            'created_at' => now()
        ]);

        $from = now()->subMonths(14)->toDateString();
        $to = now()->subMonths(2)->toDateString();

        $response = $this->withToken($this->token)->getJson("/api/myposts?date_from=$from&date_to=$to");

        $response->assertStatus(200);
        $titles = array_column($response->json(), 'title');

        $this->assertEquals(['2024 Post'], $titles);
    }

    public function test_my_posts_sorting()
    {
        Post::query()->delete();
        $user = $this->user;

        Post::factory()->create(['title' => 'C', 'user_id' => $user->id]);
        Post::factory()->create(['title' => 'A', 'user_id' => $user->id]);
        Post::factory()->create(['title' => 'B', 'user_id' => $user->id]);

        $response = $this->withToken($this->token)->getJson('/api/myposts?sort=title&order=asc');

        $titles = array_column($response->json(), 'title');
        $this->assertEquals(['A', 'B', 'C'], $titles);
    }

    public function test_my_posts_limit_and_offset()
    {
        $response = $this->withToken($this->token)->getJson('/api/myposts?limit=2&offset=2');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json());
    }

    public function test_invalid_sort_parameter()
    {
        $response = $this->withToken($this->token)->getJson('/api/myposts?sort=invalid');

        $response->assertStatus(400)
            ->assertJson(['message' => 'Недопустимый параметр сортировки']);
    }

    public function test_posts_belong_only_to_authenticated_user()
    {
        $response = $this->withToken($this->token)->getJson('/api/myposts');

        foreach ($response->json() as $post) {
            $this->assertEquals($this->user->id, $post['user_id']);
        }
    }
}
