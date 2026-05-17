@extends('layouts.app')

@section('title', 'Add Asset | Hospital Asset Manager')
@section('page-eyebrow', 'Penambahan Aset')
@section('page-heading', 'Menambahkan aset')

@section('content')
    <form action="{{ route('web.assets.store') }}" method="POST">
        @csrf
        @include('assets._form', ['submitLabel' => 'Create asset'])
    </form>
@endsection
