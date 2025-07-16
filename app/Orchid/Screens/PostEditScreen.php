<?php

namespace App\Orchid\Screens;

use App\Models\Post;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Textarea;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Screen;

class PostEditScreen extends Screen
{
    public $name = 'Редактирование публикации';
    public $description = 'Создание и изменение поста';

    public $post;

    public function query(Post $post): iterable
    {
        $this->post = $post;
        return [
            'post' => $post,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Сохранить')
                ->icon('bs.save')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('post.title')
                    ->title('Заголовок')
                    ->required(),

                Textarea::make('post.content')
                    ->title('Содержимое')
                    ->rows(10)
                    ->required(),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $request->validate([
            'post.title' => 'required|string|max:255',
            'post.content' => 'required|string',
        ]);

        $this->post->fill($request->get('post'))->save();

        Toast::info('Пост сохранён!');
        return redirect()->route('platform.posts');
    }
}
