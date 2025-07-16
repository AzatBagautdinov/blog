<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PostService;

class PostController extends Controller
{

    public function store(Request $request, PostService $postService)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post = $postService->createPost($validated);

        return response()->json([
            'message' => 'Пост успешно опубликован',
            'post'    => $post,
        ], 201);
    }

    public function index(Request $request, PostService $postService)
    {
        return response()->json($postService->getAllPosts($request));
    }

    public function myPosts(Request $request, PostService $postService)
    {
        $posts = $postService->getUserPosts($request);
        return response()->json($posts);
    }

}
