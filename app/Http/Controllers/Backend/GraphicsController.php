<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Article;
use Auth;
use Carbon\CarbonImmutable;
use stdClass;

class GraphicsController extends Controller
{
	public function index()
	{
		$init_datetime = CarbonImmutable::now()->startOfDay()->format('Y-m-d');
		$end_datetime = CarbonImmutable::now()->add(5, 'day')->endOfDay()->format('Y-m-d');

		$articles_expired = Article::select('name', 'expiration_date')
			->where('expiration_date', '>=', $init_datetime)
			->where('expiration_date', '<=', $end_datetime)
			->get();

		$articles_drained = Article::select('name', 'stock')
			->where('stock', '<=', 10)
			->get();

		$length_articles_expired = count($articles_expired);
		$length_articles_drained = count($articles_drained);

		$number_notifications = $length_articles_expired + $length_articles_drained;

		$get_notifications = false;

		if ($length_articles_expired != 0 || $length_articles_drained != 0) {
			$get_notifications = true;
		};

		$notifications = [];

		foreach ($articles_expired as $article) {

			$parse_date = CarbonImmutable::createFromDate($article->expiration_date)->startOfDay()->format('d/m/Y');

			$obj = [
				'msg' => $article->name . ' expira el ' . $parse_date,
				'icon' => 'fa-solid fa-triangle-exclamation',
				'color' => '#e62e1b'
			];

			array_push($notifications, $obj);
		}

		foreach ($articles_drained as $article) {

			$obj = [
				'msg' => $article->name . ' tiene en stock ' . $article->stock,
				'icon' => 'fa-solid fa-circle-exclamation',
				'color' => '#ffdf00'
			];

			array_push($notifications, $obj);
		}

		return view('backend.graphics')->with(compact('get_notifications', 'number_notifications', 'notifications', 'init_datetime'));
	}

	public function getArticles()
	{
		$warehouse_type_id = request('warehouse_type_id');
		$limit_date = CarbonImmutable::createFromDate(request('limit_date'))->endOfDay()->format('Y-m-d');

		$init_datetime = CarbonImmutable::now()->startOfDay()->format('Y-m-d');

		$warehouse_type_1 = 'PACHACAMAC';
		$warehouse_type_2 = 'VITARTE 5 JESUS DE NAZARETH';
		$warehouse_type_3 = 'VITARTE 1';

		if ($warehouse_type_id != 0) {
			$articles = Article::select(
				'name',
				'expiration_date',
				'stock')
				->where('warehouse_type_id', $warehouse_type_id)
				->where('expiration_date', '>=', $init_datetime)
				->where('expiration_date', '<=', $limit_date)
				// ->limit(20)
				->get();
		} else {
			$articles = Article::select(
				'name',
				'expiration_date',
				'stock')
				->where('expiration_date', '>=', $init_datetime)
				->where('expiration_date', '<=', $limit_date)
				// ->limit(20)
				->get();
		};

		$expiration_dates = [];
		$data = [];

		foreach ($articles as $article) {
			$expiration_date = CarbonImmutable::createFromDate($article->expiration_date)->startOfDay()->format('d/m/Y');

			array_push($expiration_dates, $expiration_date);
		};

		$expiration_dates = array_unique($expiration_dates);

		foreach ($expiration_dates as $expiration_date) {
			$obj = [
				'expiration_date' => $expiration_date,
				'quantity' => 0,
				'articles_names' => [],
			];

			array_push($data, $obj);
		};

		foreach ($articles as $article) {
			$expiration_date = CarbonImmutable::createFromDate($article->expiration_date)->startOfDay()->format('d/m/Y');
			$article_name = $article->name;
			$stock = $article->stock;

			$index = array_search($expiration_date, array_column($data, 'expiration_date'));

			$obj = [
				'article_name' => $article_name,
				'stock' => $stock,
			];

			$data[$index]['quantity'] += $stock;
			array_push($data[$index]['articles_names'], $obj);
		};

		return $data;
	}
}