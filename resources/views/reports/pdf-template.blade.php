<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Report' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 16px; margin: 0; }
        .header h2 { font-size: 12px; margin: 4px 0 0; color: #666; }
        .header .date { font-size: 9px; color: #999; margin-top: 4px; }
        .summary { display: flex; margin-bottom: 15px; }
        .summary-item { display: inline-block; text-align: center; margin-right: 20px; padding: 8px 12px; background: #f5f5f5; border-radius: 4px; }
        .summary-label { font-size: 8px; color: #999; text-transform: uppercase; }
        .summary-value { font-size: 14px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th { background: #f8f8f8; border: 1px solid #ddd; padding: 6px 8px; font-size: 9px; text-transform: uppercase; color: #555; }
        tbody td { border: 1px solid #eee; padding: 5px 8px; }
        tbody tr:nth-child(even) { background: #fafafa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .footer { margin-top: 20px; border-top: 1px solid #ddd; padding-top: 8px; font-size: 8px; color: #999; text-align: center; }
        tfoot td { border-top: 2px solid #333; font-weight: bold; padding: 6px 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PAR Hotel — Property Management System</h1>
        <h2>{{ $title ?? 'Report' }}</h2>
        @if(isset($subtitle))
        <div class="date">{{ $subtitle }}</div>
        @endif
        <div class="date">Generated: {{ now()->format('d M Y H:i') }}</div>
    </div>

    @if(isset($summary) && count($summary) > 0)
    <div style="margin-bottom: 15px;">
        <table style="width: auto;">
            <tr>
                @foreach($summary as $item)
                <td style="border: none; text-align: center; padding: 8px 16px; background: #f5f5f5; margin-right: 8px;">
                    <div style="font-size: 8px; color: #999; text-transform: uppercase;">{{ $item['label'] }}</div>
                    <div style="font-size: 13px; font-weight: bold;">{{ $item['value'] }}</div>
                </td>
                @endforeach
            </tr>
        </table>
    </div>
    @endif

    @if(isset($headers) && isset($rows))
    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                <th class="{{ $header['align'] ?? '' }}">{{ $header['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                @foreach($row as $cell)
                <td class="{{ $cell['align'] ?? '' }}">{{ $cell['value'] ?? '' }}</td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($headers) }}" class="text-center" style="color: #999; padding: 20px;">No data available</td>
            </tr>
            @endforelse
        </tbody>
        @if(isset($totals))
        <tfoot>
            <tr>
                @foreach($totals as $total)
                <td class="{{ $total['align'] ?? '' }}">{{ $total['value'] ?? '' }}</td>
                @endforeach
            </tr>
        </tfoot>
        @endif
    </table>
    @endif

    <div class="footer">
        PAR Hotel PMS — Report generated automatically. This document is for learning purposes only.
    </div>
</body>
</html>
