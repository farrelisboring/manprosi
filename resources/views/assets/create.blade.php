@extends('layouts.app')

@section('title', 'Add Asset | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-emerald-700">New asset</p>
            <h1 class="text-3xl font-semibold text-gray-950">Create an asset record</h1>
            <p class="mt-2 text-sm text-gray-600">Capture the core inventory data, placement details, and identifiers in one server-rendered workflow.</p>
        </div>
    </section>

    <form action="{{ route('web.assets.store') }}" class="mt-8" method="POST">
        @csrf
        @include('assets._form', ['submitLabel' => 'Create asset'])
    </form>
@endsection
