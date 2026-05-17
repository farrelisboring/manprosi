@extends('layouts.app')

@section('title', 'Edit Asset | Hospital Asset Manager')
@section('page-eyebrow', 'Edit aset')
@section('page-heading', $asset->name)

@section('content')
    <form action="{{ route('web.assets.update', $asset) }}" method="POST">
        @csrf
        @method('PATCH')
        @include('assets._form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
