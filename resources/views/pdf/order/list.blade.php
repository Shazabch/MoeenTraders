@extends('pdf.layouts.master2')

@section('content')


<table>
    <thead>
        <tr>
            <th>@lang('S.N.')</th>
            <th>@lang('Invoice')</th>
            <th>@lang('Date')</th>
            <th>@lang('Customer')</th>
            <th>@lang('Status')</th>
            <th>@lang('Total')</th>

        </tr>
    </thead>
    <tbody>
        @foreach ($sales as $sale)
        @php
        $statusClass = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'processing' => 'primary',
        'shipped' => 'dark',
        'delivered' => 'success',
        'cancelled' => 'danger'
        ];
        $class = $statusClass[$sale->status] ?? 'secondary';
        @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $sale->invoice_no }}</td>
            <td>{{ showDateTime($sale->sale_date, 'm/d/Y') }}</td>
            <td>{{ $sale->customer?->name }}</td>
            <td> <span class="badge badge--{{ $class }}">
                    {{ ucfirst($sale->status) }}
                </span></td>

            <td>
                @if ($sale->total_price < 0) - @endif
                    {{ showAmount(abs($sale->total_price)) }}
                    </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection