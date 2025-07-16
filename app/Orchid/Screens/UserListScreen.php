<?php

namespace App\Orchid\Screens;

use App\Models\User;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use App\Orchid\Filters\CreatedAtFilter;
use App\Orchid\Filters\RoleFilter;

class UserListScreen extends Screen
{
    public $name = 'Пользователи';

    public function query(): array
    {
        return [
            'users' => User::filters()
                ->defaultSort('id')
                ->paginate(),
        ];
    }

    public function filters(): iterable
    {
        return [
            CreatedAtFilter::class,
            RoleFilter::class,
        ];
    }

    public function layout(): array
    {
        return [
            Layout::selection([
                CreatedAtFilter::class,
                RoleFilter::class,
            ]),

            Layout::table('users', [
                TD::make('id', 'ID'),
                TD::make('name', 'Имя'),
                TD::make('email', 'Email'),
                TD::make('role', 'Роль'),
                TD::make('created_at', 'Создан'),
                TD::make('updated_at', 'Обновлён'),
            ]),
        ];
    }
}
