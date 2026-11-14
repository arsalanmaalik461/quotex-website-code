@extends('admin.layouts.app')
@section('title', 'Add Payment Category')
@section('page-title', 'Add Payment Category')

@section('content')

<div class="card" style="max-width:980px;margin:0 auto;">
    <div class="card-header">
        <div class="card-title">New Payment Category</div>
    </div>

    <form method="POST" action="{{ route('admin.payment-categories.store') }}" enctype="multipart/form-data">
        @include('admin.payment-categories._form')
    </form>
</div>

@endsection
