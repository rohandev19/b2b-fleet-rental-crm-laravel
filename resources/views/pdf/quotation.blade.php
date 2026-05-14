<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $quotation->quotation_number }}</title>
    <style>
        @page {
            margin: 28px 32px;
        }

        body {
            color: #171717;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.45;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th {
            background: #f5f5f5;
            color: #525252;
            font-size: 9px;
            letter-spacing: 0;
            text-align: left;
            text-transform: uppercase;
        }

        th, td {
            border-bottom: 1px solid #e5e5e5;
            padding: 8px 7px;
            vertical-align: top;
        }

        .header {
            border-bottom: 2px solid #171717;
            margin-bottom: 22px;
            padding-bottom: 16px;
        }

        .brand {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .muted {
            color: #737373;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            text-align: right;
        }

        .grid {
            width: 100%;
        }

        .grid td {
            border: 0;
            padding: 0;
        }

        .panel {
            border: 1px solid #e5e5e5;
            padding: 12px;
        }

        .panel-title {
            color: #525252;
            font-size: 9px;
            font-weight: 700;
            margin-bottom: 7px;
            text-transform: uppercase;
        }

        .section {
            margin-top: 18px;
        }

        .right {
            text-align: right;
        }

        .total-row td {
            border-bottom: 0;
            padding: 5px 7px;
        }

        .grand-total td {
            border-top: 1px solid #171717;
            font-size: 13px;
            font-weight: 700;
            padding-top: 8px;
        }

        .signature {
            margin-top: 34px;
        }

        .signature-box {
            border-top: 1px solid #a3a3a3;
            padding-top: 8px;
            width: 180px;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="border: 0; padding: 0; width: 50%;">
                <div class="brand">HAN FLEET</div>
                <p class="muted">B2B Fleet Rental & Corporate Mobility</p>
            </td>
            <td style="border: 0; padding: 0; width: 50%;">
                <div class="title">Quotation</div>
                <p class="muted right">{{ $quotation->quotation_number }}</p>
            </td>
        </tr>
    </table>

    <table class="grid">
        <tr>
            <td style="width: 48%; padding-right: 12px;">
                <div class="panel">
                    <div class="panel-title">Customer</div>
                    <p><strong>{{ $quotation->prospect?->company_name }}</strong></p>
                    <p>{{ $quotation->prospect?->address }}</p>
                    <p>{{ $quotation->prospect?->city }}{{ $quotation->prospect?->province ? ', '.$quotation->prospect?->province : '' }}</p>
                    <p class="muted">PIC: {{ $quotation->contact?->name ?? '-' }}</p>
                    <p class="muted">Email: {{ $quotation->contact?->email ?? '-' }}</p>
                    <p class="muted">Phone: {{ $quotation->contact?->phone ?? '-' }}</p>
                </div>
            </td>
            <td style="width: 52%;">
                <div class="panel">
                    <div class="panel-title">Quotation Info</div>
                    <table>
                        <tr>
                            <td style="border: 0; padding: 2px 0;">Quotation Date</td>
                            <td class="right" style="border: 0; padding: 2px 0;">{{ $quotation->quotation_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td style="border: 0; padding: 2px 0;">Valid Until</td>
                            <td class="right" style="border: 0; padding: 2px 0;">{{ $quotation->valid_until->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td style="border: 0; padding: 2px 0;">Sales</td>
                            <td class="right" style="border: 0; padding: 2px 0;">{{ $quotation->sales?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border: 0; padding: 2px 0;">Approved By</td>
                            <td class="right" style="border: 0; padding: 2px 0;">{{ $quotation->approvedBy?->name ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>Vehicle</th>
                    <th>Package</th>
                    <th class="right">Qty</th>
                    <th class="right">Duration</th>
                    <th class="right">Monthly</th>
                    <th class="right">Disc.</th>
                    <th class="right">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->items as $item)
                    <tr>
                        <td>{{ $item->vehicle?->brand }} {{ $item->vehicle?->model }}</td>
                        <td>{{ $item->package?->name ?? '-' }}</td>
                        <td class="right">{{ $item->quantity }}</td>
                        <td class="right">{{ $item->duration_months }} months</td>
                        <td class="right">Rp{{ number_format((float) $item->monthly_price, 0, ',', '.') }}</td>
                        <td class="right">{{ number_format((float) $item->discount_percent, 2) }}%</td>
                        <td class="right">Rp{{ number_format((float) $item->line_total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table class="section">
        <tr>
            <td style="border: 0; padding: 0 18px 0 0; width: 58%;">
                <div class="panel-title">Terms & Conditions</div>
                <p>{{ $quotation->terms_and_conditions ?: '-' }}</p>
            </td>
            <td style="border: 0; padding: 0; width: 42%;">
                <table>
                    <tr class="total-row">
                        <td>Subtotal</td>
                        <td class="right">Rp{{ number_format((float) $quotation->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Discount</td>
                        <td class="right">Rp{{ number_format((float) $quotation->discount_amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Tax {{ number_format((float) $quotation->tax_percent, 2) }}%</td>
                        <td class="right">Rp{{ number_format((float) $quotation->tax_amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="total-row grand-total">
                        <td>Grand Total</td>
                        <td class="right">Rp{{ number_format((float) $quotation->grand_total, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="signature">
        <tr>
            <td style="border: 0; padding: 0; width: 50%;">
                <p class="muted">Prepared by</p>
                <div style="height: 54px;"></div>
                <div class="signature-box">{{ $quotation->sales?->name ?? 'HAN Fleet Sales' }}</div>
            </td>
            <td style="border: 0; padding: 0; width: 50%;">
                <p class="muted">Customer approval</p>
                <div style="height: 54px;"></div>
                <div class="signature-box">Name, signature, and stamp</div>
            </td>
        </tr>
    </table>
</body>
</html>
