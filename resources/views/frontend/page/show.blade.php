@extends('frontend.layouts.app')

@section('content')
<main class="inside">
    <div class="inside-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home', ['locale' => $locale]) }}">{{ trans('common.Home') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ $locale == 'ar' ? ($page->pagetitleAR ?? $page->pagetitle) : ($page->pagetitle ?? '') }}</li>
        </ol>
        <h1>
            <span>
                <span class="before-icon"></span>
                {{ $locale == 'ar' ? ($page->pagetitleAR ?? $page->pagetitle) : ($page->pagetitle ?? '') }}
                <span class="after-icon"></span>
            </span>
        </h1>
    </div>
    
    <div class="inside-content">
        <div class="container-fluid">
            <div class="page-content" dir="{{ $locale == 'ar' ? 'rtl' : 'ltr' }}">
                {!! $locale == 'ar' ? ($page->detailsAR ?? $page->details) : ($page->details ?? '') !!}
            </div>
        </div>
    </div>
</main>
@endsection

