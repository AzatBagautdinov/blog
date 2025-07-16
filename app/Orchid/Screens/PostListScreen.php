<?php

namespace App\Orchid\Screens;

use App\Models\Post;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;
use App\Orchid\Filters\CreatedAtFilter;
use App\Orchid\Filters\AuthorFilter;

class PostListScreen extends Screen
{
    public $name = 'Публикации';
    public $description = 'Список всех публикаций';

    public function query(): iterable
    {
        return [
            'posts' => Post::filters()
                ->defaultSort('id', 'desc')
                ->paginate(),
        ];
    }

    public function filters(): iterable
    {
        return [
            CreatedAtFilter::class,
            AuthorFilter::class,
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::selection([
                CreatedAtFilter::class,
                AuthorFilter::class,
            ]),

            Layout::table('posts', [
                TD::make('id', 'ID')->sort(),
                TD::make('title', 'Заголовок')
                    ->sort()
                    ->render(fn(Post $post) =>
                    Link::make($post->title)
                        ->route('platform.post.edit', $post->id)
                    ),
                TD::make('content', 'Содержимое')
                    ->render(fn(Post $post) =>
                    \Illuminate\Support\Str::limit($post->content, 100)
                    ),
                TD::make('user.name', 'Автор')
                    ->sort()
                    ->render(fn(Post $post) => $post->user->name ?? '-'),
                TD::make('created_at', 'Создано')
                    ->sort()
                    ->render(fn(Post $post) => $post->created_at->format('d.m.Y H:i')),
                TD::make(__('Actions'))->alignRight()
                    ->render(fn(Post $post) =>
                    DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make('Edit')
                                ->route('platform.post.edit', $post->id)
                                ->icon('bs.pencil'),

                            Button::make('Delete')
                                ->icon('bs.trash')
                                ->confirm('Вы уверены, что хотите удалить этот пост?')
                                ->method('remove', [
                                    'id' => $post->id,
                                ]),
                        ])
                    ),
            ]),
        ];
    }

    public function remove(Request $request): void
    {
        Post::findOrFail($request->get('id'))->delete();
        Toast::info('Пост удалён');
    }
}
