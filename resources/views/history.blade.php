<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pemesanan - SmartRide AI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            margin: 0;
            background: #f4f7fb;
            color: #0f172a;
        }

        .navbar {
            background: white;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.08);
        }

        .navbar a {
            text-decoration: none;
            color: #16a34a;
            font-weight: 800;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 36px auto;
        }

        .card {
            background: white;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        h1 {
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        th,
        td {
            text-align: left;
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            color: #334155;
        }

        .status {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 7px 12px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 13px;
        }

        .price {
            color: #166534;
            font-weight: 900;
        }

        .empty {
            text-align: center;
            color: #64748b;
            padding: 40px;
        }

        .table-wrapper {
            overflow-x: auto;
        }
    </style>
</head>
<body>

<div class="navbar">
    <strong>SmartRide AI</strong>
    <a href="{{ route('smart-ride.index') }}">Kembali ke Beranda</a>
</div>

<div class="container">
    <div class="card">
        <h1>Riwayat Pemesanan</h1>
        <p>Berikut adalah seluruh interaksi pemesanan yang pernah dilakukan oleh akun ini.</p>

        @if ($orders->count() > 0)
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>No Pesanan</th>
                            <th>Layanan</th>
                            <th>Rute</th>
                            <th>Jarak</th>
                            <th>Driver</th>
                            <th>Total</th>
                            <th>Kualitas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ strtoupper($order->service_type) }}</td>
                                <td>
                                    <strong>Jemput:</strong> {{ $order->pickup }}<br>
                                    <strong>Tujuan:</strong> {{ $order->destination }}
                                </td>
                                <td>{{ $order->distance }} KM</td>
                                <td>
                                    {{ $order->driver_name }}<br>
                                    {{ $order->driver_vehicle }} - {{ $order->driver_plate }}<br>
                                    Reliability: {{ $order->driver_reliability }}/100
                                </td>
                                <td class="price">Rp {{ number_format($order->final_price, 0, ',', '.') }}</td>
                                <td>{{ $order->quality_average }}/100</td>
                                <td><span class="status">{{ $order->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty">
                Belum ada riwayat pemesanan.
            </div>
        @endif
    </div>
</div>

</body>
</html>