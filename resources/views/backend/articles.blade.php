@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Logística')
@section('subtitle', 'Artículos')

@section('content')
	<article-form
		:url = "'{{ route('dashboard.article.list') }}'"
	></article-form>

	<article-table
		:url_delete = "'{{ route('dashboard.article.delete_article') }}'"
		:url_export_record = "'{{ route('dashboard.article.list') }}'"
	></article-table>

	<article-modal
		:url_edit ="'{{ route('dashboard.article.update_article') }}'"
	></article-modal>

	<loading></loading>
@endsection