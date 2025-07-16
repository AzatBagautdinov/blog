<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostFetchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Post::factory()->count(15)->create([
            'user_id' => $user->id,
        ]);
    }

    public function test_it_returns_10_posts_by_default()
    {
        $response = $this->getJson('/api/posts');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json());
    }

    public function test_it_respects_limit_and_offset()
    {
        $response = $this->getJson('/api/posts?limit=5&offset=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json());
    }

    public function test_it_sorts_by_title_asc()
    {
        Post::query()->delete();
        $user = User::factory()->create();

        Post::factory()->create(['title' => 'Бета', 'user_id' => $user->id]);
        Post::factory()->create(['title' => 'Альфа', 'user_id' => $user->id]);
        Post::factory()->create(['title' => 'Гамма', 'user_id' => $user->id]);

        $response = $this->getJson('/api/posts?sort=title&order=asc&limit=3');

        $response->assertStatus(200);
        $titles = array_column($response->json(), 'title');
        $this->assertEquals(['Альфа', 'Бета', 'Гамма'], $titles);
    }

    public function test_invalid_sort_parameter_returns_400()
    {
        $response = $this->getJson('/api/posts?sort=unknown');

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Недопустимый параметр сортировки',]);
    }

    public function test_it_filters_by_date_range()
    {
        Post::query()->delete();
        $user = User::factory()->create();

        Post::factory()->create(['title' => 'Пост в 2023', 'user_id' => $user->id, 'created_at' => now()->subYears(2),]);
        Post::factory()->create(['title' => 'Пост в 2024', 'user_id' => $user->id, 'created_at' => now()->subYear(),]);
        Post::factory()->create(['title' => 'Пост в 2025', 'user_id' => $user->id, 'created_at' => now(),]);

        $from = now()->subMonths(14)->toDateString(); // захватывает 2024
        $to = now()->subMonths(2)->toDateString();    // не захватывает 2025

        $response = $this->getJson("/api/posts?date_from=$from&date_to=$to");
        $response->assertStatus(200);
        $titles = array_column($response->json(), 'title');

        $this->assertEquals(['Пост в 2024'], $titles);
    }

    public function test_it_sorts_by_created_at_desc()
    {
        Post::query()->delete();

        $user = User::factory()->create();
        Post::factory()->create(['title' => 'Первый', 'user_id' => $user->id, 'created_at' => now()->subDays(2)]);
        Post::factory()->create(['title' => 'Второй', 'user_id' => $user->id, 'created_at' => now()->subDay()]);
        Post::factory()->create(['title' => 'Третий', 'user_id' => $user->id, 'created_at' => now()]);

        $response = $this->getJson('/api/posts?sort=created_at&order=desc&limit=3');

        $response->assertStatus(200);
        $titles = array_column($response->json(), 'title');
        $this->assertEquals(['Третий', 'Второй', 'Первый'], $titles);
    }

    public function test_it_sorts_by_created_at_asc()
    {
        $response = $this->getJson('/api/posts?sort=created_at&order=asc&limit=10');

        $response->assertStatus(200);
        $posts = $response->json();
        $timestamps = array_column($posts, 'created_at');
        $sorted = $timestamps;
        sort($sorted);
        $this->assertEquals($sorted, $timestamps);
    }

    public function test_it_filters_from_date_only()
    {
        $from = now()->subDays(3)->toDateString();

        $response = $this->getJson("/api/posts?date_from={$from}");

        $response->assertStatus(200);
        foreach ($response->json() as $post) {
            $this->assertGreaterThanOrEqual($from, substr($post['created_at'], 0, 10));
        }
    }

    public function test_it_filters_to_date_only()
    {
        $to = now()->toDateString();

        $response = $this->getJson("/api/posts?date_to={$to}");

        $response->assertStatus(200);
        foreach ($response->json() as $post) {
            $this->assertLessThanOrEqual($to, substr($post['created_at'], 0, 10));
        }
    }

    public function test_it_filters_from_and_to_dates_combined()
    {
        $from = now()->subDays(5)->toDateString();
        $to   = now()->toDateString();

        $response = $this->getJson("/api/posts?date_from={$from}&date_to={$to}");

        $response->assertStatus(200);
        foreach ($response->json() as $post) {
            $created = substr($post['created_at'], 0, 10);
            $this->assertTrue($created >= $from && $created <= $to);
        }
    }

    public function test_it_returns_empty_if_no_posts_in_range()
    {
        $from = now()->addDays(5)->toDateString();
        $to = now()->addDays(10)->toDateString();

        $response = $this->getJson("/api/posts?date_from={$from}&date_to={$to}");

        $response->assertStatus(200);
        $this->assertCount(0, $response->json());
    }

    public function test_it_includes_user_name()
    {
        $response = $this->getJson('/api/posts');

        $response->assertStatus(200);
        foreach ($response->json() as $post) {
            $this->assertArrayHasKey('user', $post);
            $this->assertArrayHasKey('name', $post['user']);
        }
    }

    public function test_each_post_has_required_fields()
    {
        $response = $this->getJson('/api/posts?limit=5');

        $response->assertStatus(200);
        foreach ($response->json() as $post) {
            $this->assertArrayHasKey('title', $post);
            $this->assertArrayHasKey('content', $post);
            $this->assertArrayHasKey('user', $post);
        }
    }


}
