<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SmartRide AI - Web Transportasi Online</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <link rel="stylesheet" href="{{ asset('css/smart-ride.css') }}">
</head>
<body>

<nav class="navbar">
    <div class="brand">
        <div class="brand-logo">SR</div>
        <div>
            <h1>SmartRide AI</h1>
            <span>Fair, Fast, and Transparent Ride</span>
        </div>
    </div>

    <div class="nav-menu">
        <a href="#layanan">Layanan</a>
        <a href="#booking">Pesan</a>
        <a href="#promo">Promo</a>
        <a href="#driver">Driver</a>
        <a href="{{ route('smart-ride.history') }}">Riwayat</a>

        @if (auth()->check())
            <div class="user-badge">
                <span class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
                <span class="user-name">
                    {{ auth()->user()->name }}
                </span>
            </div>
        @endif

        <form action="{{ route('logout') }}" method="POST" class="logout-form">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>
</nav>

<section class="hero">
    <div class="hero-text">
        <span class="badge">AI Transport Platform</span>
        <h2>Pesan perjalanan dengan maps, estimasi jarak otomatis, dan driver matching.</h2>
        <p>
            SmartRide AI adalah prototype aplikasi transportasi online berbasis web.
            Pengguna dapat menentukan titik jemput dan tujuan melalui maps, lalu sistem
            menghitung jarak, harga, kondisi perjalanan, dan rekomendasi driver.
        </p>

        <div class="hero-actions">
            <a href="#booking" class="btn-primary">Pesan Sekarang</a>
            <a href="#layanan" class="btn-secondary">Lihat Layanan</a>
        </div>
    </div>

    <div class="hero-card">
        <h3>Mode Maps Aktif</h3>
        <p>Klik tombol lokasi untuk titik jemput, lalu klik peta untuk memilih tujuan.</p>

        <div class="hero-stat">
            <div>
                <strong>GPS</strong>
                <span>Pickup</span>
            </div>
            <div>
                <strong>AI</strong>
                <span>Driver Match</span>
            </div>
        </div>
    </div>
</section>

<main class="container">

    <section class="section-title" id="layanan">
        <h2>Pilih Layanan SmartRide AI</h2>
        <p>Layanan dibuat seperti aplikasi transportasi online, tetapi ditambah sistem AI agar lebih transparan.</p>
    </section>

    <section class="service-grid">
        @foreach ($services as $service)
            <div class="service-card">
                <div class="service-icon">{{ $service['icon'] }}</div>
                <h3>{{ $service['name'] }}</h3>
                <p>{{ $service['description'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="map-panel" id="booking">
    <div class="map-header">
        <div>
            <h2>Pilih Lokasi Melalui Maps</h2>
            <p>
                Cari lokasi jemput dan tujuan melalui kolom pencarian, atau gunakan lokasi saat ini
                dan klik titik tujuan pada peta.
            </p>
        </div>

        <button type="button" class="small-button" onclick="useMyLocation()">
            Gunakan Lokasi Saya
        </button>
    </div>

    <div class="search-map-grid">
        <div class="search-box">
            <label>Cari Lokasi Jemput</label>
            <div class="search-row">
                <input
                    type="text"
                    id="pickupSearch"
                    placeholder="Contoh: Universitas Muhammadiyah Malang"
                >
                <button type="button" onclick="searchLocation('pickup')">Cari Jemput</button>
            </div>
        </div>

        <div class="search-box">
            <label>Cari Lokasi Tujuan</label>
            <div class="search-row">
                <input
                    type="text"
                    id="destinationSearch"
                    placeholder="Contoh: Malang Town Square"
                >
                <button type="button" onclick="searchLocation('destination')">Cari Tujuan</button>
            </div>
        </div>
    </div>

    <div class="map-help">
        <strong>Petunjuk:</strong>
        gunakan tombol lokasi untuk titik jemput, cari tempat melalui kolom pencarian,
        atau klik langsung pada peta untuk menentukan tujuan.
    </div>

    <div id="map"></div>

    <div class="map-info-grid">
        <div>
            <span>Titik Jemput</span>
            <strong id="pickupText">Belum dipilih</strong>
        </div>
        <div>
            <span>Tujuan</span>
            <strong id="destinationText">Belum dipilih</strong>
        </div>
        <div>
            <span>Jarak Otomatis</span>
            <strong id="distanceText">0 KM</strong>
        </div>
        <div>
            <span>Kondisi Otomatis</span>
            <strong id="conditionText">Normal</strong>
        </div>
    </div>
    </section>

    <section class="layout-grid">
        <div class="panel booking-panel">
            <h2>Form Pemesanan</h2>
            <p class="panel-subtitle">Data lokasi, jarak, dan kondisi perjalanan akan diisi otomatis dari maps.</p>

            @if ($errors->any())
                <div class="error-box">
                    <strong>Input belum valid:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('smart-ride.calculate') }}" method="POST">
                @csrf

                <label>Jenis Layanan</label>
                <select name="service_type">
                    <option value="ride" {{ old('service_type', $serviceType ?? '') === 'ride' ? 'selected' : '' }}>SmartRide - Motor</option>
                    <option value="car" {{ old('service_type', $serviceType ?? '') === 'car' ? 'selected' : '' }}>SmartCar - Mobil</option>
                    <option value="food" {{ old('service_type', $serviceType ?? '') === 'food' ? 'selected' : '' }}>SmartFood - Pesan Makanan</option>
                    <option value="send" {{ old('service_type', $serviceType ?? '') === 'send' ? 'selected' : '' }}>SmartSend - Kirim Barang</option>
                </select>

                <label>Lokasi Jemput</label>
                <input
                    id="pickupInput"
                    type="text"
                    name="pickup"
                    value="{{ old('pickup', $pickup ?? '') }}"
                    placeholder="Klik tombol Gunakan Lokasi Saya"
                    readonly
                >

                <label>Lokasi Tujuan</label>
                <input
                    id="destinationInput"
                    type="text"
                    name="destination"
                    value="{{ old('destination', $destination ?? '') }}"
                    placeholder="Klik titik tujuan di maps"
                    readonly
                >

                <input type="hidden" id="pickupLat" name="pickup_lat" value="{{ old('pickup_lat', $pickupLat ?? '') }}">
                <input type="hidden" id="pickupLng" name="pickup_lng" value="{{ old('pickup_lng', $pickupLng ?? '') }}">
                <input type="hidden" id="destinationLat" name="destination_lat" value="{{ old('destination_lat', $destinationLat ?? '') }}">
                <input type="hidden" id="destinationLng" name="destination_lng" value="{{ old('destination_lng', $destinationLng ?? '') }}">

                <label>Jarak Perjalanan Otomatis (KM)</label>
                <input
                    id="distanceInput"
                    type="number"
                    step="0.01"
                    name="distance"
                    value="{{ old('distance', $distance ?? '') }}"
                    readonly
                >

                <label>Kondisi Perjalanan Otomatis</label>
                <select id="timeConditionInput" name="time_condition">
                    <option value="normal" {{ old('time_condition', $timeCondition ?? '') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="busy" {{ old('time_condition', $timeCondition ?? '') === 'busy' ? 'selected' : '' }}>Jam Sibuk</option>
                    <option value="rain" {{ old('time_condition', $timeCondition ?? '') === 'rain' ? 'selected' : '' }}>Hujan</option>
                    <option value="night" {{ old('time_condition', $timeCondition ?? '') === 'night' ? 'selected' : '' }}>Malam Hari</option>
                </select>

                <label>Metode Pembayaran</label>
                <select name="payment_method">
                    <option value="cash" {{ old('payment_method', $paymentMethod ?? '') === 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="ewallet" {{ old('payment_method', $paymentMethod ?? '') === 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                    <option value="bank" {{ old('payment_method', $paymentMethod ?? '') === 'bank' ? 'selected' : '' }}>Transfer Bank</option>
                </select>

                <label>Kode Promo</label>
                <input type="text" name="promo_code" value="{{ old('promo_code', $promoCode ?? '') }}" placeholder="Coba: SMART10 atau HEMAT5">

                <label>Harga Negosiasi</label>
                <input type="number" name="nego_price" value="{{ old('nego_price', $negoPrice ?? '') }}" placeholder="Opsional, contoh: 23000">

                <button type="submit">Hitung Harga dan Cari Driver</button>
            </form>
        </div>

        <div class="panel result-panel">
            <h2>Hasil Pemesanan</h2>

            @isset($finalPrice)
                <div class="order-status">
                    <span>Nomor Pesanan</span>
                    <strong>{{ $orderNumber }}</strong>
                </div>

                <div class="price-highlight">
                    <span>Total Bayar</span>
                    <strong>Rp {{ number_format($finalPrice, 0, ',', '.') }}</strong>
                </div>

                <div class="result-list">
                    <p><b>Layanan:</b>
                        @if ($serviceType === 'ride')
                            SmartRide - Motor
                        @elseif ($serviceType === 'car')
                            SmartCar - Mobil
                        @elseif ($serviceType === 'food')
                            SmartFood - Pesan Makanan
                        @else
                            SmartSend - Kirim Barang
                        @endif
                    </p>
                    <p><b>Lokasi Jemput:</b> {{ $pickup }}</p>
                    <p><b>Tujuan:</b> {{ $destination }}</p>
                    <p><b>Jarak:</b> {{ $distance }} KM</p>
                    <p><b>Pembayaran:</b>
                        @if ($paymentMethod === 'cash')
                            Tunai
                        @elseif ($paymentMethod === 'ewallet')
                            E-Wallet
                        @else
                            Transfer Bank
                        @endif
                    </p>
                </div>
            @else
                <div class="empty-state">
                    <div>🗺️</div>
                    <h3>Belum Ada Pesanan</h3>
                    <p>Pilih titik jemput dan tujuan di maps, lalu hitung harga.</p>
                </div>
            @endisset
        </div>
    </section>

    <section class="layout-grid">
        <div class="panel">
            <h2>AI Fair Pricing</h2>

            @isset($aiPrice)
                <div class="pricing-row">
                    <span>Tarif Dasar</span>
                    <strong>Rp {{ number_format($basePrice, 0, ',', '.') }}</strong>
                </div>
                <div class="pricing-row">
                    <span>Tarif per KM</span>
                    <strong>Rp {{ number_format($pricePerKm, 0, ',', '.') }}</strong>
                </div>
                <div class="pricing-row">
                    <span>Harga Normal</span>
                    <strong>Rp {{ number_format($normalPrice, 0, ',', '.') }}</strong>
                </div>
                <div class="pricing-row">
                    <span>Harga AI</span>
                    <strong>Rp {{ number_format($aiPrice, 0, ',', '.') }}</strong>
                </div>

                <div class="notice">
                    <b>Fair Surge:</b> {{ $surgeStatus }}
                </div>
            @else
                <p class="muted">Harga akan dihitung setelah pengguna memilih lokasi pada maps.</p>
            @endisset
        </div>

        <div class="panel">
            <h2>Hybrid Pricing</h2>

            @isset($finalPrice)
                <div class="pricing-row">
                    <span>Diskon Promo</span>
                    <strong>Rp {{ number_format($discount, 0, ',', '.') }}</strong>
                </div>
                <div class="pricing-row">
                    <span>Harga Setelah Promo</span>
                    <strong>Rp {{ number_format($priceAfterPromo, 0, ',', '.') }}</strong>
                </div>

                <div class="notice">
                    <b>Promo:</b> {{ $promoStatus }}
                </div>

                <div class="notice">
                    <b>Negosiasi:</b> {{ $negoStatus }}
                </div>

                <div class="notice warning-light">
                    <b>Auto-Fallback:</b> {{ $fallbackStatus }}
                </div>
            @else
                <p class="muted">Status negosiasi dan Auto-Fallback akan muncul setelah simulasi.</p>
            @endisset
        </div>
    </section>

    <section class="layout-grid" id="driver">
        <div class="panel">
            <h2>Smart Driver Matching</h2>

            @isset($selectedDriver)
                <div class="driver-profile">
                    <div class="driver-avatar">{{ strtoupper(substr($selectedDriver['name'], 0, 1)) }}</div>
                    <div>
                        <h3>{{ $selectedDriver['name'] }}</h3>
                        <p>{{ $selectedDriver['vehicle'] }} • {{ $selectedDriver['plate'] }}</p>
                    </div>
                </div>

                <div class="driver-stats">
                    <div>
                        <span>Rating</span>
                        <strong>{{ $selectedDriver['rating'] }}</strong>
                    </div>
                    <div>
                        <span>Reliability</span>
                        <strong>{{ $selectedDriver['reliability'] }}/100</strong>
                    </div>
                    <div>
                        <span>Jarak ke Jemput</span>
                        <strong>{{ $selectedDriver['distance_to_pickup'] }} KM</strong>
                    </div>
                    <div>
                        <span>Matching</span>
                        <strong>{{ $selectedDriver['matching_score'] }}</strong>
                    </div>
                </div>
            @else
                <p class="muted">Driver terbaik akan dipilih berdasarkan jarak ke titik jemput, reliability score, rating, dan cancel rate.</p>
            @endisset
        </div>

        <div class="panel">
            <h2>Skor Kualitas Sistem</h2>

            @isset($qualityScore)
                <div class="quality-list">
                    <div>
                        <span>Transparency</span>
                        <strong>{{ $qualityScore['transparency'] }}/100</strong>
                    </div>
                    <div>
                        <span>Reliability</span>
                        <strong>{{ $qualityScore['reliability'] }}/100</strong>
                    </div>
                    <div>
                        <span>Pricing Fairness</span>
                        <strong>{{ $qualityScore['pricing_fairness'] }}/100</strong>
                    </div>
                    <div class="average">
                        <span>Average Score</span>
                        <strong>{{ $qualityScore['average'] }}/100</strong>
                    </div>
                </div>
            @else
                <p class="muted">Skor kualitas akan muncul setelah pemesanan dilakukan.</p>
            @endisset
        </div>
    </section>

    <section class="section-title" id="promo">
        <h2>Promo SmartRide AI</h2>
        <p>Gunakan kode promo berikut pada form pemesanan.</p>
    </section>

    <section class="promo-grid">
        @foreach ($promos as $promo)
            <div class="promo-card">
                <span>{{ $promo['code'] }}</span>
                <h3>{{ $promo['title'] }}</h3>
                <p>{{ $promo['description'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="panel full-panel">
        <h2>Riwayat Perjalanan Simulasi</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Rute</th>
                        <th>Layanan</th>
                        <th>Harga</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($histories as $history)
                        <tr>
                            <td>{{ $history['route'] }}</td>
                            <td>{{ $history['service'] }}</td>
                            <td>Rp {{ number_format($history['price'], 0, ',', '.') }}</td>
                            <td><span class="status-done">{{ $history['status'] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

</main>

<footer>
    <p>SmartRide AI © 2026 | Laravel Web Prototype</p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map;
    let pickupMarker = null;
    let destinationMarker = null;
    let routeLine = null;

    let pickupPosition = null;
    let destinationPosition = null;

    const defaultPosition = [-7.9666, 112.6326];

    document.addEventListener('DOMContentLoaded', function () {
        initMap();
        restoreOldMapData();
        setAutomaticCondition();
    });

    function initMap() {
        map = L.map('map').setView(defaultPosition, 13);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        map.on('click', function (event) {
            setDestination(
                event.latlng.lat,
                event.latlng.lng,
                'Titik Tujuan: ' + event.latlng.lat.toFixed(5) + ', ' + event.latlng.lng.toFixed(5)
            );
        });
    }

    function restoreOldMapData() {
        const oldPickupLat = document.getElementById('pickupLat').value;
        const oldPickupLng = document.getElementById('pickupLng').value;
        const oldDestinationLat = document.getElementById('destinationLat').value;
        const oldDestinationLng = document.getElementById('destinationLng').value;

        const oldPickupName = document.getElementById('pickupInput').value;
        const oldDestinationName = document.getElementById('destinationInput').value;

        if (oldPickupLat && oldPickupLng) {
            setPickup(
                parseFloat(oldPickupLat),
                parseFloat(oldPickupLng),
                oldPickupName || 'Titik Jemput',
                false
            );
        }

        if (oldDestinationLat && oldDestinationLng) {
            setDestination(
                parseFloat(oldDestinationLat),
                parseFloat(oldDestinationLng),
                oldDestinationName || 'Titik Tujuan',
                false
            );
        }

        if (oldPickupLat && oldPickupLng && oldDestinationLat && oldDestinationLng) {
            map.fitBounds([
                [parseFloat(oldPickupLat), parseFloat(oldPickupLng)],
                [parseFloat(oldDestinationLat), parseFloat(oldDestinationLng)]
            ], { padding: [40, 40] });
        }
    }

    function useMyLocation() {
        if (!navigator.geolocation) {
            alert('Browser tidak mendukung geolocation.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                setPickup(
                    lat,
                    lng,
                    'Lokasi Saya: ' + lat.toFixed(5) + ', ' + lng.toFixed(5),
                    true
                );
            },
            function () {
                alert('Lokasi tidak dapat diakses. Izinkan akses lokasi pada browser.');
            }
        );
    }

    async function searchLocation(type) {
        const inputId = type === 'pickup' ? 'pickupSearch' : 'destinationSearch';
        const keyword = document.getElementById(inputId).value.trim();

        if (!keyword) {
            alert('Masukkan nama lokasi terlebih dahulu.');
            return;
        }

        const query = keyword + ', Malang, Indonesia';
        const url = 'https://nominatim.openstreetmap.org/search?format=json&q='
            + encodeURIComponent(query)
            + '&limit=5';

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!data || data.length === 0) {
                alert('Lokasi tidak ditemukan. Coba kata kunci lain.');
                return;
            }

            const selectedLocation = data[0];
            const lat = parseFloat(selectedLocation.lat);
            const lng = parseFloat(selectedLocation.lon);
            const displayName = selectedLocation.display_name;

            if (type === 'pickup') {
                setPickup(lat, lng, displayName, true);
            } else {
                if (!pickupPosition) {
                    alert('Tentukan lokasi jemput terlebih dahulu.');
                    return;
                }

                setDestination(lat, lng, displayName, true);
            }
        } catch (error) {
            alert('Terjadi kesalahan saat mencari lokasi.');
        }
    }

    function setPickup(lat, lng, label = 'Titik Jemput', moveMap = true) {
        pickupPosition = { lat: lat, lng: lng };

        if (pickupMarker) {
            map.removeLayer(pickupMarker);
        }

        pickupMarker = L.marker([lat, lng])
            .addTo(map)
            .bindPopup('Titik Jemput<br>' + label)
            .openPopup();

        document.getElementById('pickupLat').value = lat;
        document.getElementById('pickupLng').value = lng;
        document.getElementById('pickupInput').value = label;
        document.getElementById('pickupText').innerText = shortText(label, 45);

        if (moveMap) {
            map.setView([lat, lng], 15);
        }

        updateDistanceAndRoute();
    }

    function setDestination(lat, lng, label = 'Titik Tujuan', moveMap = false) {
        if (!pickupPosition) {
            alert('Tentukan titik jemput terlebih dahulu.');
            return;
        }

        destinationPosition = { lat: lat, lng: lng };

        if (destinationMarker) {
            map.removeLayer(destinationMarker);
        }

        destinationMarker = L.marker([lat, lng])
            .addTo(map)
            .bindPopup('Titik Tujuan<br>' + label)
            .openPopup();

        document.getElementById('destinationLat').value = lat;
        document.getElementById('destinationLng').value = lng;
        document.getElementById('destinationInput').value = label;
        document.getElementById('destinationText').innerText = shortText(label, 45);

        if (moveMap) {
            map.setView([lat, lng], 15);
        }

        updateDistanceAndRoute();
    }

    function updateDistanceAndRoute() {
        if (!pickupPosition || !destinationPosition) {
            return;
        }

        const distance = calculateDistance(
            pickupPosition.lat,
            pickupPosition.lng,
            destinationPosition.lat,
            destinationPosition.lng
        );

        document.getElementById('distanceInput').value = distance.toFixed(2);
        document.getElementById('distanceText').innerText = distance.toFixed(2) + ' KM';

        if (routeLine) {
            map.removeLayer(routeLine);
        }

        routeLine = L.polyline([
            [pickupPosition.lat, pickupPosition.lng],
            [destinationPosition.lat, destinationPosition.lng]
        ], {
            weight: 5
        }).addTo(map);

        map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });

        setAutomaticCondition();
    }

    function calculateDistance(lat1, lng1, lat2, lng2) {
        const earthRadius = 6371;

        const dLat = toRadians(lat2 - lat1);
        const dLng = toRadians(lng2 - lng1);

        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);

        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return earthRadius * c;
    }

    function toRadians(value) {
        return value * Math.PI / 180;
    }

    function setAutomaticCondition() {
        const hour = new Date().getHours();
        const conditionInput = document.getElementById('timeConditionInput');
        const conditionText = document.getElementById('conditionText');
        const distance = parseFloat(document.getElementById('distanceInput').value || 0);

        let condition = 'normal';
        let label = 'Normal';

        if ((hour >= 6 && hour <= 8) || (hour >= 16 && hour <= 18)) {
            condition = 'busy';
            label = 'Jam Sibuk';
        } else if (hour >= 21 || hour <= 4) {
            condition = 'night';
            label = 'Malam Hari';
        } else if (distance >= 15) {
            condition = 'busy';
            label = 'Padat karena jarak jauh';
        }

        conditionInput.value = condition;
        conditionText.innerText = label;
    }

    function shortText(text, maxLength) {
        if (!text) {
            return '-';
        }

        if (text.length <= maxLength) {
            return text;
        }

        return text.substring(0, maxLength) + '...';
    }
</script>

</body>
</html>