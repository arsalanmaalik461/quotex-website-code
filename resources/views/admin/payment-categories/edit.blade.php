@extends('admin.layouts.app')
@section('title', 'Edit Payment Category: ' . $category->name)
@section('page-title', 'Edit Payment Category')

@section('content')

<div class="card" style="max-width:980px;margin:0 auto;">
    <div class="card-header">
        <div class="card-title">Edit: {{ $category->name }}</div>
    </div>

    <form method="POST" action="{{ route('admin.payment-categories.update', $category) }}" enctype="multipart/form-data">
        @include('admin.payment-categories._form')
    </form>
</div>

@endsection
