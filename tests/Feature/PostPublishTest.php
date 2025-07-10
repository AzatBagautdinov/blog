<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPublishTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->token = $user->createToken('TestToken')->plainTextToken;
    }

    public function test_successful_post_creation()
    {
        $response = $this->withToken($this->token)->postJson('/api/posts', [
            'title' => 'Тестовый заголовок',
            'text' => 'Тестовое содержимое поста.',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Пост успешно опубликован',
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Тестовый заголовок',
        ]);
    }

    public function test_post_creation_without_token()
    {
        $response = $this->postJson('/api/posts', [
            'title' => 'Без токена',
            'text' => 'Тестовое содержимое.',
        ]);

        $response->assertStatus(401);
    }

    public function test_post_creation_with_invalid_token()
    {
        $response = $this->withToken('InvalidToken')->postJson('/api/posts', [
            'title' => 'Невалидный токен',
            'text' => 'Контент',
        ]);

        $response->assertStatus(401);
    }

    public function test_post_creation_without_title()
    {
        $response = $this->withToken($this->token)->postJson('/api/posts', [
            'text' => 'Без заголовка',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_post_creation_without_text()
    {
        $response = $this->withToken($this->token)->postJson('/api/posts', [
            'title' => 'Только заголовок',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }

    public function test_post_creation_with_empty_fields()
    {
        $response = $this->withToken($this->token)->postJson('/api/posts', [
            'title' => '',
            'text' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'text']);
    }

    public function test_post_title_and_text_max_length_validation()
    {
        $longTitle = str_repeat('Заг', 200); // 600+ символов
        $longText = str_repeat('Текст ', 1000); // 5000+ символов

        $response = $this->withToken($this->token)->postJson('/api/posts', [
            'title' => $longTitle,
            'text' => $longText,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'text']);
    }

    public function test_mass_post_creation()
    {
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->withToken($this->token)->postJson('/api/posts', [
                'title' => "Заголовок $i",
                'text' => "Контент $i",
            ]);

            $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Пост успешно опубликован',
                ]);
        }

        $this->assertDatabaseCount('posts', 5);
    }

    public function test_post_belongs_to_authenticated_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('AnotherToken')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/posts', [
            'title' => 'Пост пользователя',
            'text' => 'Контент пользователя',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'title' => 'Пост пользователя',
        ]);
    }
}
