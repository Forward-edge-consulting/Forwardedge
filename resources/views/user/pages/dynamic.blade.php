@extends('user.master_page')

@section('body_attributes', $page->blocks->pluck('type')->intersect(\App\Support\PageBlueprint::modernTypes())->isNotEmpty() ? 'data-native-scroll' : '')
@section('title', ($page->title ?? 'Page') . ' | Forward Edge Consulting')

@section('main')
    @if (session('success') || $errors->any())
        <div class="container pt-4">@include('user.partials.form-alerts')</div>
    @endif

    {{-- Optional global header/footer from config --}}
    @php $headerType = config('pagebuilder.globals.header_block_type'); @endphp
    @if($headerType) @includeIf('user.pages.block.' . $headerType, ['block' => (object)['data'=>[]]]) @endif

    @foreach($page->blocks as $block)
      @if (!empty($pbEdit))<div data-pb-block="{{ $block->id }}" data-pb-type="{{ str_replace('_', ' ', $block->type) }}">@endif
      @includeIf('user.pages.block.' . $block->type, ['block' => $block])
      @if (!empty($pbEdit))</div>@endif
    @endforeach

    @if (!empty($pbEdit))
        @include('user.pages.partials.pb-edit-overlay')
    @endif

    @php $footerType = config('pagebuilder.globals.footer_block_type'); @endphp
    @if($footerType) @includeIf('user.pages.block.' . $footerType, ['block' => (object)['data'=>[]]]) @endif

@endsection