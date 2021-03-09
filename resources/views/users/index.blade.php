@extends('layouts.default')
@section('title','所有用户')

@section('content')
<div class="offset-md-2 col-md-8">
    <div class="md-4 text-center">所有用户</div>
    <div class="list-group list-group-flush">
        @foreach($users as $user)
         @include('users._user')
        @endforeach
    </div>
    <div class="mt-3">
        {!! $users->render() !!}
    </div>
</div>

@endsection
