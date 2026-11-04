@extends('user.master_page')
@section('body_attributes', 'data-native-scroll')
@section('title', 'Contact | Forward Edge Consulting')
@section('main')
    @include('user.partials.contact-details', ['contactPage' => true])
@endsection
