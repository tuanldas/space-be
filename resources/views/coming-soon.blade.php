@extends('Layouts.app')

@section('title', 'Space BE - Coming Soon')

@section('content')
    <div class="flex items-center justify-center grow h-full">
        <div class="flex flex-col items-center">
            <div class="mb-16">
                <img alt="image" class="dark:hidden max-h-[300px]"
                     src="{{asset('assets/media/illustrations/10.svg')}}"/>
                <img alt="image" class="light:hidden max-h-[300px]"
                     src="{{asset('assets/media/illustrations/10-dark.svg')}}"/>
            </div>
            <span class="badge badge-primary badge-outline mb-3">Coming Soon</span>
        </div>
    </div>
@endsection
