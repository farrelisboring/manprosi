@extends('layouts.app')

@section('title', 'Edit Asset | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-amber-700">Edit aset</p>
            <h1 class="text-3xl font-semibold text-gray-950">{{ $asset->name }}</h1>
        </div>
    </section>

    <form action="{{ route('web.assets.update', $asset) }}" class="mt-8" method="POST">
        @csrf
        @method('PATCH')
        @include('assets._form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
