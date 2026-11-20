@extends('user.master_page')
@section('body_attributes', 'data-native-scroll')
@section('title', $form->name . ' | Forward Edge Consulting')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/forms.css') }}?v=5">
@endpush
@section('main')
<section class="fe-form-page">
    <div class="container">
        <div class="fe-form-card">
            <header class="fe-form-card-head">
                <h1>{{ $form->name }}</h1>
                @if ($form->description)<p class="fe-form-intro">{{ $form->description }}</p>@endif
            </header>
            @include('user.partials.form-embed', ['form' => $form, 'returnTo' => $returnTo ?? null])
        </div>
    </div>
</section>
@endsection
