<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;

class Post extends Model
{
    use HasFactory, Filterable;

    protected $fillable = ['user_id', 'title', 'content', 'text'];

    protected $allowedSorts = [
        'id',
        'title',
        'created_at',
        'user.name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getContentAttribute()
    {
        return $this->attributes['content'] ?? null;
    }

    public function getContent()
    {
        return $this->content;
    }
}
