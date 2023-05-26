@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Logística')
@section('subtitle', 'Graficos')

@section('content')
    <graphic-form
        :url = "'{{ route('dashboard.graphics.get_articles') }}'"
        :current_date = "'{{ $init_datetime }}'"
    ></graphic-form>
    <graphic-line
    ></graphic-line>

	<loading></loading>
@endsection