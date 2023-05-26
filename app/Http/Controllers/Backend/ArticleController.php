<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Article;
use App\Exports\ArticleReportExport;
use Auth;
use Carbon\CarbonImmutable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;

class ArticleController extends Controller
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

		return view('backend.articles')->with(compact('get_notifications', 'number_notifications', 'notifications', 'init_datetime'));
	}

	public function list()
	{
		$warehouse_type_id = request('warehouse_type_id');
		$export = request('export');

		$warehouse_type_1 = 'PACHACAMAC';
		$warehouse_type_2 = 'VITARTE 5 JESUS DE NAZARETH';
		$warehouse_type_3 = 'VITARTE 1';

		if ($warehouse_type_id != 0) {
			$articles = Article::where('warehouse_type_id', $warehouse_type_id)
				->get();
		} else {
			$articles = Article::get();
		};

		$elements = [];

		foreach ($articles as $article) {
			$parse_date = CarbonImmutable::createFromDate($article->expiration_date)->startOfDay()->format('d/m/Y');

			if ($article->warehouse_type_id == 1) {
				$warehouse_type_name = $warehouse_type_1;
			} elseif ($article->warehouse_type_id == 2) {
				$warehouse_type_name = $warehouse_type_2;
			} elseif ($article->warehouse_type_id == 3) {
				$warehouse_type_name = $warehouse_type_3;
			};

			$article->expiration_date = $parse_date;
			$article->warehouse_type_name = $warehouse_type_name;

			array_push($elements, $article);
		}

		if ($export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:L1');
			$sheet->setCellValue('A1', 'REPORTE DE ARTICULOS DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
			$sheet->getStyle('A1')->applyFromArray([
				'font' => [
					'bold' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				]
			]);

			$sheet->setCellValue('A3', '#');
            $sheet->setCellValue('B3', 'Codigo');		
            $sheet->setCellValue('C3', 'Nombre del Producto');		
            $sheet->setCellValue('D3', 'Precio de Venta');
            $sheet->setCellValue('E3', 'Precio de Compra');
			$sheet->setCellValue('F3', 'Stock');
			$sheet->setCellValue('G3', 'Fecha de Expiración');
			$sheet->setCellValue('H3', 'Clase');
			$sheet->setCellValue('I3', 'Marca');
			$sheet->setCellValue('J3', 'Familia');
			$sheet->setCellValue('K3', 'Botica');

			$sheet->getStyle('A3:M3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($elements as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->code_product);
				$sheet->setCellValue('C'.$row_number, $element->name );
				$sheet->setCellValue('D'.$row_number, $element->sale_price);
				$sheet->setCellValue('E'.$row_number, $element->cost_price);
				$sheet->setCellValue('F'.$row_number, $element->stock);
				$sheet->setCellValue('G'.$row_number, $element->expiration_date);
				$sheet->setCellValue('H'.$row_number, $element->clase);
				$sheet->setCellValue('I'.$row_number, $element->marca);
				$sheet->setCellValue('J'.$row_number, $element->family);
				$sheet->setCellValue('K'.$row_number, $element->warehouse_type_name);

                $sheet->getStyle('D'.$row_number)->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle('E'.$row_number)->getNumberFormat()->setFormatCode('0.00');

				$row_number++;
			};

			$sheet->setCellValueExplicit('A4', 1, DataType::TYPE_NUMERIC);
			$writer = new Xls($spreadsheet);

			return $writer->save('php://output');
		} else {
			return $elements;
		};
	}

	public function deleteArticle()
	{
		$id = request('id');

		$deleted_datetime = CarbonImmutable::now()->format('Y-m-d H:m:s');

		$article = Article::find($id);

		$article->deleted_at = $deleted_datetime;
		$article->state = 'inactivo';
		$article->save();

		$response = [
			'msg' => 'Articulo eliminado con exito',
		];

		return response()->json($response);
	}

	public function updateArticle()
	{
		$warehouse_type_id = request('warehouse_type_id');
		$id = request('id');
		$stock = request('stock');
		$expired = request('expiration_date');

		$expired_datetime = CarbonImmutable::createFromDate($expired)->format('Y-m-d H:m:s');

		$article = Article::find($id);

		if ($stock != null) {
			$article->stock = $stock;
		};

		if ($expired) {
			$article->expiration_date = $expired_datetime;
		};

		$article->save();

		return $this->list();
	}
}
