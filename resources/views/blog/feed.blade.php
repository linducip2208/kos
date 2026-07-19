<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ setting('app_name', 'Kos Manager') }} Blog</title>
        <link>{{ route('blog.index') }}</link>
        <description>Artikel, tips, dan informasi seputar manajemen kos</description>
        <language>id</language>
        <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>
        <atom:link href="{{ route('blog.feed') }}" rel="self" type="application/rss+xml"/>

        @foreach($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ route('blog.show', $post->slug) }}</link>
            <guid>{{ route('blog.show', $post->slug) }}</guid>
            <pubDate>{{ $post->published_at->toRssString() }}</pubDate>
            <description>{{ $post->excerpt }}</description>
            @if($post->category)<category>{{ $post->category->name }}</category>@endif
        </item>
        @endforeach
    </channel>
</rss>
