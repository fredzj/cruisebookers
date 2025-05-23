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
     * Retrieve all blogs for external use.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Blog::orderBy('timestamp', 'desc')->get();
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