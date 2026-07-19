@extends('pseo._layout')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-extrabold text-slate-900">{{ $seo['title'] }}</h1>
    <p class="text-slate-500 mt-4">{{ $seo['description'] }}</p>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
        @foreach($properties as $p)
        <a href="{{ route('landing.property', $p) }}" class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-lg transition-all">
            <h3 class="font-bold text-slate-800">{{ $p->name }}</h3>
            <p class="text-sm text-slate-500">{{ Str::limit($p->address, 60) }}</p>
        </a>
        @endforeach
    </div>
</div>
@endsection

