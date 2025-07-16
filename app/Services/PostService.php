<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostService
{
    public function getUserPosts(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Неавторизован');
        }

        $sort  = $request->query('sort', 'created_at');
        $order = $request->query('order', 'desc');
        $limit = (int) $request->query('limit', 10);
        $offset = (int) $request->query('offset', 0);
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $allowedSorts = ['created_at', 'title'];
        if (! in_array($sort, $allowedSorts, true)) {
            abort(400, 'Недопустимый параметр сортировки');
        }

        $query = Post::with('user')
            ->where('user_id', $user->id)
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit);

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->get();
    }

    public function getAllPosts(Request $request)
    {
        $sort  = $request->query('sort',  'created_at'); // title | created_at
        $order = $request->query('order', 'desc');
        $limit  = (int) $request->query('limit',  10);
        $offset = (int) $request->query('offset', 0);
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $allowedSorts = ['created_at', 'title'];
        if (!in_array($sort, $allowedSorts, true)) {
            abort(400, 'Недопустимый параметр сортировки');
        }

        $query = Post::with('user:id,name')->orderBy($sort, $order);

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->skip($offset)->take($limit)->get();
    }

    public function createPost(array $data)
    {
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Неавторизован');
        }

        $validated = validator($data, [
            'title'   => 'required|string|max:255',
            'content' => 'required|string|max:2000',
        ])->validate();

        return Post::create([
            'user_id' => $user->id,
            'title'   => $validated['title'],
            'content' => $validated['content'],
        ]);
    }

}
