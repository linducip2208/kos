<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] ?? 'Blog — Kos Manager' }}</title>
    <meta name="description" content="{{ $seo['description'] ?? '' }}">
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: { DEFAULT: '#2563eb', 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                    },
                }
            }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @keyframes fadeSlideUp{0%{transform:translateY(30px);opacity:0}100%{transform:translateY(0);opacity:1}}
        .reveal{opacity:0;transform:translateY(30px);transition:opacity .6s,transform .6s cubic-bezier(.16,1,.3,1)}
        .reveal.visible{opacity:1;transform:translateY(0)}
        .card-lift{transition:transform .35s,box-shadow .35s}
        .card-lift:hover{transform:translateY(-6px);box-shadow:0 24px 48px -12px rgba(0,0,0,.18)}
        .prose h1{font-size:1.75rem;font-weight:800;margin-top:1.5em;margin-bottom:.5em}
        .prose h2{font-size:1.4rem;font-weight:700;margin-top:1.4em;margin-bottom:.4em}
        .prose h3{font-size:1.15rem;font-weight:600;margin-top:1.2em;margin-bottom:.3em}
        .prose p{margin-bottom:1em;line-height:1.8}
        .prose ul,.prose ol{padding-left:1.5em;margin-bottom:1em}
        .prose li{margin-bottom:.3em}
        @media (max-width:640px){
            .prose h1{font-size:1.4rem}.prose h2{font-size:1.2rem}.prose h3{font-size:1.05rem}
            .prose p{font-size:.95rem;line-height:1.7}
            .blog-header h1{font-size:1.75rem}
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased">

<header class="bg-white border-b border-slate-200 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
        <a href="/" class="flex items-center gap-2.5 font-extrabold text-xl text-primary-700">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
            </div>
            <span>{{ setting('app_name', 'Kos Manager') }}</span>
        </a>
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium">
            <a href="/" class="text-slate-600 hover:text-primary-600 transition-colors">Beranda</a>
            <a href="/blog" class="text-primary-600">Blog</a>
            <a href="/docs" class="text-slate-600 hover:text-primary-600 transition-colors">Dokumentasi</a>
        </nav>
    </div>
</header>

@if(isset($category))
<div class="bg-primary-600 py-6 md:py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <h1 class="text-2xl md:text-3xl font-extrabold text-white">Kategori: {{ $category->name }}</h1>
        @if($category->description)<p class="text-primary-100 mt-2 text-sm md:text-base">{{ $category->description }}</p>@endif
    </div>
</div>
@elseif(!isset($post))
<div class="blog-header bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 py-12 md:py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-3xl md:text-5xl font-extrabold text-white">Blog</h1>
        <p class="text-primary-100 mt-4 text-base md:text-lg max-w-2xl mx-auto">Artikel, tips, dan informasi seputar manajemen kos, properti, dan investasi.</p>
    </div>
</div>
@endif

<main class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    @if(isset($post))
        <article class="max-w-3xl mx-auto">
            @if($post->featured_image)
                <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full rounded-2xl mb-8 object-cover max-h-96">
            @endif
            <div class="flex items-center gap-3 text-sm text-slate-500 mb-4">
                @if($post->category)<span class="bg-primary-100 text-primary-700 px-3 py-1 rounded-full font-medium">{{ $post->category->name }}</span>@endif
                <span>{{ $post->published_at?->format('d M Y') }}</span>
                <span>— {{ $post->author?->name }}</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-6 leading-tight">{{ $post->title }}</h1>
            <div class="prose prose-lg max-w-none text-slate-700 leading-relaxed">
                {!! $post->content !!}
            </div>

            @if($related->count())
            <div class="mt-16 pt-12 border-t border-slate-200">
                <h3 class="text-xl font-bold text-slate-900 mb-6">Artikel Terkait</h3>
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach($related as $r)
                    <a href="{{ route('blog.show', $r->slug) }}" class="card-lift bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100">
                        @if($r->featured_image)
                            <img src="{{ asset('storage/' . $r->featured_image) }}" alt="{{ $r->title }}" class="w-full h-40 object-cover">
                        @else
                            <div class="w-full h-40 bg-gradient-to-br from-primary-100 to-indigo-100 flex items-center justify-center">
                                <svg class="w-10 h-10 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            </div>
                        @endif
                        <div class="p-4">
                            <h4 class="font-semibold text-slate-800 text-sm leading-snug line-clamp-2">{{ $r->title }}</h4>
                            <p class="text-xs text-slate-400 mt-2">{{ $r->published_at?->format('d M Y') }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </article>
    @else
        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                @if($posts->isEmpty())
                    <div class="text-center py-20 text-slate-500">Belum ada artikel.</div>
                @else
                    <div class="grid sm:grid-cols-2 gap-6">
                        @foreach($posts as $p)
                        <a href="{{ route('blog.show', $p->slug) }}" class="card-lift bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100">
                            @if($p->featured_image)
                                <img src="{{ asset('storage/' . $p->featured_image) }}" alt="{{ $p->title }}" class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gradient-to-br from-primary-100 to-indigo-100 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                </div>
                            @endif
                            <div class="p-5">
                                @if($p->category)<span class="text-xs bg-primary-50 text-primary-600 px-2.5 py-1 rounded-full font-medium">{{ $p->category->name }}</span>@endif
                                <h3 class="font-bold text-slate-800 mt-2 leading-snug line-clamp-2">{{ $p->title }}</h3>
                                <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $p->excerpt }}</p>
                                <div class="flex items-center gap-2 mt-3 text-xs text-slate-400">
                                    <span>{{ $p->published_at?->format('d M Y') }}</span>
                                    <span>·</span>
                                    <span>{{ $p->author?->name }}</span>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $posts->links() }}</div>
                @endif
            </div>

            <aside class="space-y-6">
                <div class="bg-white rounded-xl p-6 border border-slate-100">
                    <form action="{{ route('blog.index') }}" method="GET">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari artikel..."
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    </form>
                </div>
                @if($categories->count())
                <div class="bg-white rounded-xl p-6 border border-slate-100">
                    <h4 class="font-bold text-slate-800 mb-3">Kategori</h4>
                    <div class="space-y-2">
                        <a href="{{ route('blog.index') }}" class="block text-sm {{ !isset($category) ? 'text-primary-600 font-semibold' : 'text-slate-600 hover:text-primary-600' }}">Semua</a>
                        @foreach($categories as $cat)
                        <a href="{{ route('blog.category', $cat->slug) }}" class="block text-sm {{ isset($category) && $category->id == $cat->id ? 'text-primary-600 font-semibold' : 'text-slate-600 hover:text-primary-600' }}">{{ $cat->name }} ({{ $cat->posts_count }})</a>
                        @endforeach
                    </div>
                </div>
                @endif
            </aside>
        </div>
    @endif
</main>

<footer class="bg-slate-900 text-slate-400 py-12 mt-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center text-sm">
        &copy; {{ date('Y') }} {{ setting('app_name', 'Kos Manager') }}. All rights reserved.
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    });
</script>
</body>
</html>
