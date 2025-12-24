<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .meta {
            color: #666;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .text-right {
            text-align: right;
        }
        @media print {
            @page {
                margin: 1cm;
            }
            body {
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
        
        /* Auto print when page loads */
        @media print {
            body {
                visibility: hidden;
            }
            .print-content {
                visibility: visible;
            }
        }
    </style>
</head>
<body>
    <div class="print-content">
        <div class="header">
            <h1>{{ $title }}</h1>
            <p class="meta">Generated on: {{ date('Y-m-d H:i:s') }}</p>
        </div>

        <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    @if($type === 'date')
                        <td>{{ $row->order_date ? \Carbon\Carbon::parse($row->order_date)->format('d-M-Y') : 'N/A' }}</td>
                        <td class="text-right">{{ $row->order_count ?? 0 }}</td>
                        <td class="text-right">{{ number_format($row->total_sales ?? 0, 2) }}</td>
                    @elseif($type === 'month')
                        <td>{{ $row->order_month ? \Carbon\Carbon::createFromFormat('Y-m', $row->order_month)->format('M Y') : 'N/A' }}</td>
                        <td class="text-right">{{ $row->order_count ?? 0 }}</td>
                        <td class="text-right">{{ number_format($row->total_sales ?? 0, 2) }}</td>
                    @elseif($type === 'year')
                        <td>{{ $row->order_year ?? 'N/A' }}</td>
                        <td class="text-right">{{ $row->order_count ?? 0 }}</td>
                        <td class="text-right">{{ number_format($row->total_sales ?? 0, 2) }}</td>
                    @elseif($type === 'customer')
                        @php
                            $customer = $row->customer ?? null;
                            $name = $customer ? trim(($customer->firstname ?? '') . ' ' . ($customer->lastname ?? '')) : 'N/A';
                        @endphp
                        <td>{{ $name ?: 'N/A' }}</td>
                        <td>{{ $customer->email ?? 'N/A' }}</td>
                        <td class="text-right">{{ $row->order_count ?? 0 }}</td>
                        <td class="text-right">{{ number_format($row->total_sales ?? 0, 2) }}</td>
                    @elseif($type === 'product')
                        <td>{{ $row->productcode ?? 'N/A' }}</td>
                        <td>{{ $row->title ?? 'N/A' }}</td>
                        <td class="text-right">{{ $row->total_qty ?? 0 }}</td>
                        <td class="text-right">{{ number_format($row->total_sales ?? 0, 2) }}</td>
                    @elseif($type === 'rating')
                        <td>{{ $row->productcode ?? 'N/A' }}</td>
                        <td>{{ $row->title ?? 'N/A' }}</td>
                        <td class="text-right">{{ number_format($row->avg_rate ?? 0, 2) }}</td>
                        <td class="text-right">{{ $row->total_ratings ?? 0 }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="text-center">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</body>
</html>

