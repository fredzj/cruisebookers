<?php

namespace App\Http\Controllers;

use App\Models\Blog;

class BlogController extends Controller
{
    /**
     * Display a listing of the blog posts.
     */
    public function index()
    {
        $blogs = Blog::orderBy('timestamp', 'desc')->paginate(10);
        return view('blog.index', compact('blogs'));
    }

    /**
     * Display a specific blog post.
     */
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();
        return view('blog.show', compact('blog'));
    }
}