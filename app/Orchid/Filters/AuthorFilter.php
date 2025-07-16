<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;
use App\Models\User;

class AuthorFilter extends Filter
{
    public function name(): string
    {
        return 'Автор';
    }

    public function parameters(): ?array
    {
        return ['author'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->when($this->request->get('author'), function ($query) {
            $query->where('user_id', $this->request->get('author'));
        });
    }

    public function display(): iterable
    {
        return [
            Select::make('author')
                ->fromModel(User::class, 'name')
                ->title('Выберите автора')
                ->empty('Все авторы'),
        ];
    }
}
