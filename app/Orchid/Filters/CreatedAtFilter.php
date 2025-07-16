<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\DateTimer;

class CreatedAtFilter extends Filter
{
    public function name(): string
    {
        return 'Дата создания';
    }

    public function parameters(): ?array
    {
        return ['created_from', 'created_to'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder
            ->when($this->request->get('created_from'), fn ($q) =>
            $q->whereDate('created_at', '>=', $this->request->get('created_from')))
            ->when($this->request->get('created_to'), fn ($q) =>
            $q->whereDate('created_at', '<=', $this->request->get('created_to')));
    }

    public function display(): iterable
    {
        return [
            DateTimer::make('created_from')
                ->title('С даты')
                ->allowInput(),

            DateTimer::make('created_to')
                ->title('По дату')
                ->allowInput(),
        ];
    }
}
