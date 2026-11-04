@extends('user.master_page')
@section('body_attributes', 'data-native-scroll')
@section('title', $form->name . ' | Forward Edge Consulting')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/forms.css') }}?v=4">
@endpush
@section('main')
<section class="fe-form-page">
    <div class="container">
        <div class="fe-form-wrap">
            <h1>{{ $form->name }}</h1>
            @if ($form->description)<p class="fe-form-intro">{{ $form->description }}</p>@endif
            @include('user.partials.form-embed', ['form' => $form])
        </div>
    </div>
</section>
@endsection
