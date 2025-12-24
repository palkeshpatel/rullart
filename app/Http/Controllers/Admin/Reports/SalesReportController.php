<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

class SalesReportController extends Controller
{
    public function datewise(Request $request)
    {
        $query = Order::select(
            DB::raw('DATE(orderdate) as order_date'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total) as total_sales')
        )
            ->groupBy(DB::raw('DATE(orderdate)'))
            ->orderBy('order_date', 'desc');

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('orderdate', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('orderdate', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 50);
        $reports = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.reports.partials.datewise-table', compact('reports'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $reports])->render(),
            ]);
        }

        return view('admin.reports.sales-report-date', compact('reports'));
    }

    public function monthwise(Request $request)
    {
        $query = Order::select(
            DB::raw('DATE_FORMAT(orderdate, "%Y-%m") as order_month'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total) as total_sales')
        )
            ->groupBy(DB::raw('DATE_FORMAT(orderdate, "%Y-%m")'))
            ->orderBy('order_month', 'desc');

        // Year filter
        if ($request->filled('year')) {
            $query->whereYear('orderdate', $request->year);
        }

        $perPage = $request->get('per_page', 50);
        $reports = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.reports.partials.monthwise-table', compact('reports'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $reports])->render(),
            ]);
        }

        return view('admin.reports.sales-report-month', compact('reports'));
    }

    public function yearwise(Request $request)
    {
        $query = Order::select(
            DB::raw('YEAR(orderdate) as order_year'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total) as total_sales')
        )
            ->groupBy(DB::raw('YEAR(orderdate)'))
            ->orderBy('order_year', 'desc');

        $perPage = $request->get('per_page', 50);
        $reports = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.reports.partials.yearwise-table', compact('reports'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $reports])->render(),
            ]);
        }

        return view('admin.reports.sales-report-year', compact('reports'));
    }

    public function customerwise(Request $request)
    {
        $query = Order::with('customer')
            ->select(
                'fkcustomerid',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_sales')
            )
            ->groupBy('fkcustomerid')
            ->orderBy('total_sales', 'desc');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 50);
        $reports = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.reports.partials.customerwise-table', compact('reports'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $reports])->render(),
            ]);
        }

        return view('admin.reports.sales-report-customer', compact('reports'));
    }

    public function topProductMonth(Request $request)
    {
        $query = DB::table('orderitems as oi')
            ->join('products as p', 'oi.fkproductid', '=', 'p.productid')
            ->join('ordermaster as om', 'oi.fkorderid', '=', 'om.orderid')
            ->select(
                'p.productid',
                'p.title',
                'p.productcode',
                DB::raw('SUM(oi.qty) as total_qty'),
                DB::raw('SUM(oi.subtotal) as total_sales')
            )
            ->whereMonth('om.orderdate', $request->get('month', date('m')))
            ->whereYear('om.orderdate', $request->get('year', date('Y')))
            ->groupBy('p.productid', 'p.title', 'p.productcode')
            ->orderBy('total_qty', 'desc');

        $perPage = $request->get('per_page', 50);
        $reports = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.reports.partials.top-product-month-table', compact('reports'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $reports])->render(),
            ]);
        }

        return view('admin.reports.top-product-month', compact('reports'));
    }

    public function topProductRate(Request $request)
    {
        $query = DB::table('productrating as pr')
            ->join('products as p', 'pr.fkproductid', '=', 'p.productid')
            ->select(
                'p.productid',
                'p.title',
                'p.productcode',
                DB::raw('AVG(pr.rate) as avg_rate'),
                DB::raw('COUNT(pr.rateid) as total_ratings')
            )
            ->groupBy('p.productid', 'p.title', 'p.productcode')
            ->orderBy('avg_rate', 'desc')
            ->orderBy('total_ratings', 'desc');

        $perPage = $request->get('per_page', 50);
        $reports = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.reports.partials.top-product-rate-table', compact('reports'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $reports])->render(),
            ]);
        }

        return view('admin.reports.top-product-rate', compact('reports'));
    }

    public function exportDatewise(Request $request, $format = 'excel')
    {
        $query = Order::select(
            DB::raw('DATE(orderdate) as order_date'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total) as total_sales')
        )
            ->groupBy(DB::raw('DATE(orderdate)'))
            ->orderBy('order_date', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('orderdate', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('orderdate', '<=', $request->date_to);
        }

        $reports = $query->get();

        if ($format === 'pdf') {
            return $this->exportToPdf($reports, 'Sales Report Datewise', ['Order Date', 'Order Count', 'Total Sales'], 'date');
        }

        return $this->exportToExcel($reports, 'Sales Report Datewise', ['Order Date', 'Order Count', 'Total Sales'], 'date');
    }

    public function printDatewise(Request $request)
    {
        $query = Order::select(
            DB::raw('DATE(orderdate) as order_date'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total) as total_sales')
        )
            ->groupBy(DB::raw('DATE(orderdate)'))
            ->orderBy('order_date', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('orderdate', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('orderdate', '<=', $request->date_to);
        }

        $reports = $query->get(); // Get ALL data, no pagination

        return view('admin.reports.pdf.print', [
            'data' => $reports,
            'title' => 'Sales Report Datewise',
            'headers' => ['Order Date', 'Order Count', 'Total Sales'],
            'type' => 'date'
        ]);
    }

    public function exportMonthwise(Request $request, $format = 'excel')
    {
        $query = Order::select(
            DB::raw('DATE_FORMAT(orderdate, "%Y-%m") as order_month'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total) as total_sales')
        )
            ->groupBy(DB::raw('DATE_FORMAT(orderdate, "%Y-%m")'))
            ->orderBy('order_month', 'desc');

        if ($request->filled('year')) {
            $query->whereYear('orderdate', $request->year);
        }

        $reports = $query->get();

        if ($format === 'pdf') {
            return $this->exportToPdf($reports, 'Sales Report Monthwise', ['Month', 'Order Count', 'Total Sales'], 'month');
        }

        return $this->exportToExcel($reports, 'Sales Report Monthwise', ['Month', 'Order Count', 'Total Sales'], 'month');
    }

    public function printMonthwise(Request $request)
    {
        $query = Order::select(
            DB::raw('DATE_FORMAT(orderdate, "%Y-%m") as order_month'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total) as total_sales')
        )
            ->groupBy(DB::raw('DATE_FORMAT(orderdate, "%Y-%m")'))
            ->orderBy('order_month', 'desc');

        if ($request->filled('year')) {
            $query->whereYear('orderdate', $request->year);
        }

        $reports = $query->get();

        return view('admin.reports.pdf.print', [
            'data' => $reports,
            'title' => 'Sales Report Monthwise',
            'headers' => ['Month', 'Order Count', 'Total Sales'],
            'type' => 'month'
        ]);
    }

    public function exportYearwise(Request $request, $format = 'excel')
    {
        $query = Order::select(
            DB::raw('YEAR(orderdate) as order_year'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total) as total_sales')
        )
            ->groupBy(DB::raw('YEAR(orderdate)'))
            ->orderBy('order_year', 'desc');

        $reports = $query->get();

        if ($format === 'pdf') {
            return $this->exportToPdf($reports, 'Sales Report Yearwise', ['Year', 'Order Count', 'Total Sales'], 'year');
        }

        return $this->exportToExcel($reports, 'Sales Report Yearwise', ['Year', 'Order Count', 'Total Sales'], 'year');
    }

    public function printYearwise(Request $request)
    {
        $query = Order::select(
            DB::raw('YEAR(orderdate) as order_year'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total) as total_sales')
        )
            ->groupBy(DB::raw('YEAR(orderdate)'))
            ->orderBy('order_year', 'desc');

        $reports = $query->get();

        return view('admin.reports.pdf.print', [
            'data' => $reports,
            'title' => 'Sales Report Yearwise',
            'headers' => ['Year', 'Order Count', 'Total Sales'],
            'type' => 'year'
        ]);
    }

    public function exportCustomerwise(Request $request, $format = 'excel')
    {
        $query = Order::with('customer')
            ->select(
                'fkcustomerid',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_sales')
            )
            ->groupBy('fkcustomerid')
            ->orderBy('total_sales', 'desc');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $reports = $query->get();

        if ($format === 'pdf') {
            return $this->exportToPdf($reports, 'Sales Report Customerwise', ['Customer Name', 'Email', 'Order Count', 'Total Sales'], 'customer');
        }

        return $this->exportToExcel($reports, 'Sales Report Customerwise', ['Customer Name', 'Email', 'Order Count', 'Total Sales'], 'customer');
    }

    public function printCustomerwise(Request $request)
    {
        $query = Order::with('customer')
            ->select(
                'fkcustomerid',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_sales')
            )
            ->groupBy('fkcustomerid')
            ->orderBy('total_sales', 'desc');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $reports = $query->get();

        return view('admin.reports.pdf.print', [
            'data' => $reports,
            'title' => 'Sales Report Customerwise',
            'headers' => ['Customer Name', 'Email', 'Order Count', 'Total Sales'],
            'type' => 'customer'
        ]);
    }

    public function exportTopProductMonth(Request $request, $format = 'excel')
    {
        $query = DB::table('orderitems as oi')
            ->join('products as p', 'oi.fkproductid', '=', 'p.productid')
            ->join('ordermaster as om', 'oi.fkorderid', '=', 'om.orderid')
            ->select(
                'p.productid',
                'p.title',
                'p.productcode',
                DB::raw('SUM(oi.qty) as total_qty'),
                DB::raw('SUM(oi.subtotal) as total_sales')
            )
            ->whereMonth('om.orderdate', $request->get('month', date('m')))
            ->whereYear('om.orderdate', $request->get('year', date('Y')))
            ->groupBy('p.productid', 'p.title', 'p.productcode')
            ->orderBy('total_qty', 'desc');

        $reports = $query->get();

        if ($format === 'pdf') {
            return $this->exportToPdf($reports, 'Top Selling Products', ['Product Code', 'Product Name', 'Total Quantity', 'Total Sales'], 'product');
        }

        return $this->exportToExcel($reports, 'Top Selling Products', ['Product Code', 'Product Name', 'Total Quantity', 'Total Sales'], 'product');
    }

    public function printTopProductMonth(Request $request)
    {
        $query = DB::table('orderitems as oi')
            ->join('products as p', 'oi.fkproductid', '=', 'p.productid')
            ->join('ordermaster as om', 'oi.fkorderid', '=', 'om.orderid')
            ->select(
                'p.productid',
                'p.title',
                'p.productcode',
                DB::raw('SUM(oi.qty) as total_qty'),
                DB::raw('SUM(oi.subtotal) as total_sales')
            )
            ->whereMonth('om.orderdate', $request->get('month', date('m')))
            ->whereYear('om.orderdate', $request->get('year', date('Y')))
            ->groupBy('p.productid', 'p.title', 'p.productcode')
            ->orderBy('total_qty', 'desc');

        $reports = $query->get();

        return view('admin.reports.pdf.print', [
            'data' => $reports,
            'title' => 'Top Selling Products',
            'headers' => ['Product Code', 'Product Name', 'Total Quantity', 'Total Sales'],
            'type' => 'product'
        ]);
    }

    public function exportTopProductRate(Request $request, $format = 'excel')
    {
        $query = DB::table('productrating as pr')
            ->join('products as p', 'pr.fkproductid', '=', 'p.productid')
            ->select(
                'p.productid',
                'p.title',
                'p.productcode',
                DB::raw('AVG(pr.rate) as avg_rate'),
                DB::raw('COUNT(pr.rateid) as total_ratings')
            )
            ->groupBy('p.productid', 'p.title', 'p.productcode')
            ->orderBy('avg_rate', 'desc')
            ->orderBy('total_ratings', 'desc');

        $reports = $query->get();

        if ($format === 'pdf') {
            return $this->exportToPdf($reports, 'Top Rating Products', ['Product Code', 'Product Name', 'Average Rating', 'Total Ratings'], 'rating');
        }

        return $this->exportToExcel($reports, 'Top Rating Products', ['Product Code', 'Product Name', 'Average Rating', 'Total Ratings'], 'rating');
    }

    public function printTopProductRate(Request $request)
    {
        $query = DB::table('productrating as pr')
            ->join('products as p', 'pr.fkproductid', '=', 'p.productid')
            ->select(
                'p.productid',
                'p.title',
                'p.productcode',
                DB::raw('AVG(pr.rate) as avg_rate'),
                DB::raw('COUNT(pr.rateid) as total_ratings')
            )
            ->groupBy('p.productid', 'p.title', 'p.productcode')
            ->orderBy('avg_rate', 'desc')
            ->orderBy('total_ratings', 'desc');

        $reports = $query->get();

        return view('admin.reports.pdf.print', [
            'data' => $reports,
            'title' => 'Top Rating Products',
            'headers' => ['Product Code', 'Product Name', 'Average Rating', 'Total Ratings'],
            'type' => 'rating'
        ]);
    }

    private function exportToExcel($data, $title, $headers, $type = 'date')
    {
        $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('Y-m-d') . '.csv';

        $headers_array = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data, $headers, $title, $type) {
            $file = fopen('php://output', 'w');

            // Add title
            fputcsv($file, [$title]);
            fputcsv($file, []); // Empty row

            // Add headers
            fputcsv($file, $headers);

            // Add data
            foreach ($data as $row) {
                $rowData = [];
                if ($type === 'date') {
                    $rowData[] = $row->order_date ? \Carbon\Carbon::parse($row->order_date)->format('d-M-Y') : 'N/A';
                    $rowData[] = $row->order_count ?? 0;
                    $rowData[] = number_format($row->total_sales ?? 0, 2);
                } elseif ($type === 'month') {
                    $rowData[] = $row->order_month ? \Carbon\Carbon::createFromFormat('Y-m', $row->order_month)->format('M Y') : 'N/A';
                    $rowData[] = $row->order_count ?? 0;
                    $rowData[] = number_format($row->total_sales ?? 0, 2);
                } elseif ($type === 'year') {
                    $rowData[] = $row->order_year ?? 'N/A';
                    $rowData[] = $row->order_count ?? 0;
                    $rowData[] = number_format($row->total_sales ?? 0, 2);
                } elseif ($type === 'customer') {
                    $customer = $row->customer ?? null;
                    $name = $customer ? trim(($customer->firstname ?? '') . ' ' . ($customer->lastname ?? '')) : 'N/A';
                    $rowData[] = $name ?: 'N/A';
                    $rowData[] = $customer->email ?? 'N/A';
                    $rowData[] = $row->order_count ?? 0;
                    $rowData[] = number_format($row->total_sales ?? 0, 2);
                } elseif ($type === 'product') {
                    $rowData[] = $row->productcode ?? 'N/A';
                    $rowData[] = $row->title ?? 'N/A';
                    $rowData[] = $row->total_qty ?? 0;
                    $rowData[] = number_format($row->total_sales ?? 0, 2);
                } elseif ($type === 'rating') {
                    $rowData[] = $row->productcode ?? 'N/A';
                    $rowData[] = $row->title ?? 'N/A';
                    $rowData[] = number_format($row->avg_rate ?? 0, 2);
                    $rowData[] = $row->total_ratings ?? 0;
                } else {
                    foreach ($row->toArray() as $value) {
                        $rowData[] = $value;
                    }
                }
                fputcsv($file, $rowData);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers_array);
    }

    private function exportToPdf($data, $title, $headers, $type = 'date')
    {
        $html = view('admin.reports.pdf.report', compact('data', 'title', 'headers', 'type'))->render();

        // Configure mPDF
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
            'tempDir' => $tempDir,
        ]);

        // Set document information
        $mpdf->SetTitle($title);
        $mpdf->SetAuthor('Rullart Admin');
        $mpdf->SetCreator('Rullart Admin Panel');

        // Write HTML content
        $mpdf->WriteHTML($html);

        // Generate filename
        $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('Y-m-d') . '.pdf';

        // Get PDF as string (S = string)
        $pdfContent = $mpdf->Output('', 'S');

        // Return PDF as download response with force download headers
        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($pdfContent))
            ->header('Cache-Control', 'must-revalidate')
            ->header('Pragma', 'public');
    }
}
