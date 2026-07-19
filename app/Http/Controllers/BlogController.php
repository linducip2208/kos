<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::published()->with(['category', 'author'])->latest('published_at');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%"));
        }

        $posts = $query->paginate(9);
        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])->get();

        $seo = [
            'title' => 'Blog — ' . setting('app_name', 'Kos Manager'),
            'description' => 'Artikel, tips, dan informasi seputar manajemen kos, properti, dan investasi.',
            'canonical' => route('blog.index'),
        ];

        return view('blog.index', compact('posts', 'categories', 'seo'));
    }

    public function show($slug)
    {
        $post = BlogPost::published()->where('slug', $slug)
            ->with(['category', 'author'])->firstOrFail();

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->latest('published_at')->take(3)->get();

        $recent = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->latest('published_at')->take(5)->get();

        $seo = [
            'title' => $post->meta_title ?: $post->title . ' — Blog ' . setting('app_name', 'Kos Manager'),
            'description' => $post->meta_description ?: $post->excerpt,
            'canonical' => route('blog.show', $post->slug),
            'og_image' => $post->featured_image ? asset('storage/' . $post->featured_image) : null,
        ];

        return view('blog.show', compact('post', 'related', 'recent', 'seo'));
    }

    public function category($slug)
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();
        $posts = BlogPost::published()->byCategory($slug)
            ->with(['author'])->latest('published_at')->paginate(9);
        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])->get();

        $seo = [
            'title' => "Kategori: {$category->name} — Blog " . setting('app_name', 'Kos Manager'),
            'description' => $category->description ?: "Artikel dalam kategori {$category->name}",
            'canonical' => route('blog.category', $category->slug),
        ];

        return view('blog.index', compact('posts', 'categories', 'seo', 'category'));
    }

    public function feed()
    {
        $posts = BlogPost::published()->with('category')->latest('published_at')->take(20)->get();

        return response()->view('blog.feed', compact('posts'))
            ->header('Content-Type', 'application/rss+xml');
    }
}
