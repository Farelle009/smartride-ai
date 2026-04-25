<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RideOrder;
use Illuminate\Support\Facades\Auth;

class SmartRideController extends Controller
{
    public function history()
{
    $orders = RideOrder::where('user_id', Auth::id())
        ->latest()
        ->get();

    return view('history', [
        'orders' => $orders,
    ]);
    }
    public function index()
    {
        return view('smart-ride', [
            'drivers' => $this->getDrivers(),
            'promos' => $this->getPromos(),
            'services' => $this->getServices(),
            'histories' => $this->getHistories(),
        ]);
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'service_type' => 'required|in:ride,car,food,send',
            'pickup' => 'required|string|max:500',
            'destination' => 'required|string|max:500',
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'destination_lat' => 'required|numeric',
            'destination_lng' => 'required|numeric',
            'distance' => 'required|numeric|min:0.1|max:100',
            'time_condition' => 'required|in:normal,busy,rain,night',
            'payment_method' => 'required|in:cash,ewallet,bank',
            'nego_price' => 'nullable|numeric|min:0',
            'promo_code' => 'nullable|string|max:30',
        ]);

        $serviceType = $validated['service_type'];
        $pickup = $validated['pickup'];
        $destination = $validated['destination'];
        $distance = (float) $validated['distance'];
        $timeCondition = $validated['time_condition'];
        $paymentMethod = $validated['payment_method'];
        $negoPrice = $validated['nego_price'] ?? null;
        $promoCode = strtoupper($validated['promo_code'] ?? '');

        $pickupLat = (float) $validated['pickup_lat'];
        $pickupLng = (float) $validated['pickup_lng'];
        $destinationLat = (float) $validated['destination_lat'];
        $destinationLng = (float) $validated['destination_lng'];

        $pricing = $this->calculateAiFairPricing($serviceType, $distance, $timeCondition);
        $aiPrice = $pricing['ai_price'];

        $promo = $this->applyPromo($aiPrice, $promoCode);
        $priceAfterPromo = $promo['price_after_promo'];

        $negotiation = $this->processNegotiation($priceAfterPromo, $negoPrice);

        $drivers = $this->getDrivers();
        $selectedDriver = $this->matchBestDriver(
            $drivers,
            $serviceType,
            $pickupLat,
            $pickupLng,
            $distance,
            $timeCondition
        );

        $orderNumber = 'SR-' . date('Ymd') . '-' . rand(1000, 9999);

        $qualityScore = $this->calculateQualityScore(
            $selectedDriver,
            $pricing['surge_percentage'],
            $negotiation['nego_status']
        );

        RideOrder::create([
    'user_id' => Auth::id(),
    'order_number' => $orderNumber,
    'service_type' => $serviceType,
    'pickup' => $pickup,
    'destination' => $destination,
    'pickup_lat' => $pickupLat,
    'pickup_lng' => $pickupLng,
    'destination_lat' => $destinationLat,
    'destination_lng' => $destinationLng,
    'distance' => $distance,
    'time_condition' => $timeCondition,
    'payment_method' => $paymentMethod,
    'base_price' => $pricing['base_price'],
    'price_per_km' => $pricing['price_per_km'],
    'normal_price' => $pricing['normal_price'],
    'ai_price' => $aiPrice,
    'surge_percentage' => $pricing['surge_percentage'],
    'discount' => $promo['discount'],
    'price_after_promo' => $priceAfterPromo,
    'nego_price' => $negoPrice,
    'final_price' => $negotiation['final_price'],
    'promo_code' => $promoCode,
    'promo_status' => $promo['promo_status'],
    'nego_status' => $negotiation['nego_status'],
    'fallback_status' => $negotiation['fallback_status'],
    'surge_status' => $pricing['surge_status'],
    'driver_name' => $selectedDriver['name'],
    'driver_vehicle' => $selectedDriver['vehicle'],
    'driver_plate' => $selectedDriver['plate'],
    'driver_distance_to_pickup' => $selectedDriver['distance_to_pickup'],
    'driver_matching_score' => $selectedDriver['matching_score'],
    'driver_reliability' => $selectedDriver['reliability'],
    'quality_average' => $qualityScore['average'],
    'status' => 'Selesai',
]);

        return view('smart-ride', [
            'drivers' => $drivers,
            'promos' => $this->getPromos(),
            'services' => $this->getServices(),
            'histories' => $this->getHistories(),

            'serviceType' => $serviceType,
            'pickup' => $pickup,
            'destination' => $destination,
            'pickupLat' => $pickupLat,
            'pickupLng' => $pickupLng,
            'destinationLat' => $destinationLat,
            'destinationLng' => $destinationLng,
            'distance' => $distance,
            'timeCondition' => $timeCondition,
            'paymentMethod' => $paymentMethod,
            'promoCode' => $promoCode,

            'basePrice' => $pricing['base_price'],
            'pricePerKm' => $pricing['price_per_km'],
            'normalPrice' => $pricing['normal_price'],
            'aiPrice' => $aiPrice,
            'surgePercentage' => $pricing['surge_percentage'],
            'surgeStatus' => $pricing['surge_status'],

            'discount' => $promo['discount'],
            'promoStatus' => $promo['promo_status'],
            'priceAfterPromo' => $priceAfterPromo,

            'negoPrice' => $negoPrice,
            'negoStatus' => $negotiation['nego_status'],
            'fallbackStatus' => $negotiation['fallback_status'],
            'finalPrice' => $negotiation['final_price'],

            'selectedDriver' => $selectedDriver,
            'orderNumber' => $orderNumber,
            'qualityScore' => $qualityScore,
        ]);
        
    }

    private function getServices(): array
    {
        return [
            [
                'code' => 'ride',
                'name' => 'SmartRide',
                'description' => 'Layanan ojek motor cepat dan hemat.',
                'icon' => '🏍️',
            ],
            [
                'code' => 'car',
                'name' => 'SmartCar',
                'description' => 'Layanan mobil nyaman untuk perjalanan jauh.',
                'icon' => '🚗',
            ],
            [
                'code' => 'food',
                'name' => 'SmartFood',
                'description' => 'Simulasi pesan makanan berbasis lokasi.',
                'icon' => '🍔',
            ],
            [
                'code' => 'send',
                'name' => 'SmartSend',
                'description' => 'Layanan kirim barang instan.',
                'icon' => '📦',
            ],
        ];
    }

    private function calculateAiFairPricing(string $serviceType, float $distance, string $timeCondition): array
    {
        $pricingMap = [
            'ride' => [
                'base_price' => 8000,
                'price_per_km' => 4500,
            ],
            'car' => [
                'base_price' => 7000,
                'price_per_km' => 5000,
            ],
            'food' => [
                'base_price' => 5000,
                'price_per_km' => 2200,
            ],
            'send' => [
                'base_price' => 6000,
                'price_per_km' => 3000,
            ],
        ];

        $basePrice = $pricingMap[$serviceType]['base_price'];
        $pricePerKm = $pricingMap[$serviceType]['price_per_km'];

        $normalPrice = $basePrice + ($distance * $pricePerKm);

        $surgePercentage = 0;
        $surgeStatus = 'Kondisi normal. Tidak ada kenaikan harga.';

        if ($timeCondition === 'busy') {
            $surgePercentage = 25;
            $surgeStatus = 'Fair Surge aktif: harga naik 25% karena jam sibuk.';
        } elseif ($timeCondition === 'rain') {
            $surgePercentage = 20;
            $surgeStatus = 'Fair Surge aktif: harga naik 20% karena kondisi hujan.';
        } elseif ($timeCondition === 'night') {
            $surgePercentage = 15;
            $surgeStatus = 'Fair Surge aktif: harga naik 15% karena perjalanan malam.';
        }

        $aiPrice = $normalPrice + ($normalPrice * $surgePercentage / 100);

        return [
            'base_price' => $basePrice,
            'price_per_km' => $pricePerKm,
            'normal_price' => round($normalPrice),
            'ai_price' => round($aiPrice),
            'surge_percentage' => $surgePercentage,
            'surge_status' => $surgeStatus,
        ];
    }

    private function applyPromo(float $aiPrice, ?string $promoCode): array
    {
        $discount = 0;
        $promoStatus = 'Tidak ada promo digunakan.';

        if ($promoCode === 'SMART10') {
            $discount = $aiPrice * 0.10;
            $promoStatus = 'Promo SMART10 berhasil digunakan. Diskon 10%.';
        } elseif ($promoCode === 'HEMAT5') {
            $discount = 5000;
            $promoStatus = 'Promo HEMAT5 berhasil digunakan. Diskon Rp 5.000.';
        } elseif ($promoCode !== '') {
            $promoStatus = 'Kode promo tidak valid.';
        }

        $priceAfterPromo = max(0, $aiPrice - $discount);

        return [
            'discount' => round($discount),
            'price_after_promo' => round($priceAfterPromo),
            'promo_status' => $promoStatus,
        ];
    }

    private function processNegotiation(float $priceAfterPromo, mixed $negoPrice): array
    {
        $finalPrice = $priceAfterPromo;
        $negoStatus = 'Pengguna tidak mengajukan harga negosiasi.';
        $fallbackStatus = 'Auto-Fallback tidak dijalankan.';

        if ($negoPrice !== null && $negoPrice > 0) {
            $minimumFairPrice = $priceAfterPromo * 0.85;
            $maximumFairPrice = $priceAfterPromo * 1.10;

            if ($negoPrice < $minimumFairPrice) {
                $negoStatus = 'Negosiasi ditolak karena harga terlalu rendah.';
                $fallbackStatus = 'Auto-Fallback aktif: sistem menyarankan harga minimal Rp '
                    . number_format($minimumFairPrice, 0, ',', '.') . '.';
            } elseif ($negoPrice > $maximumFairPrice) {
                $negoStatus = 'Harga negosiasi terlalu tinggi. Sistem memakai harga AI agar pengguna tidak dirugikan.';
                $fallbackStatus = 'Auto-Fallback menjaga harga tetap adil.';
            } else {
                $finalPrice = $negoPrice;
                $negoStatus = 'Negosiasi diterima karena masih dalam batas wajar.';
                $fallbackStatus = 'Auto-Fallback tidak dijalankan karena harga valid.';
            }
        }

        return [
            'final_price' => round($finalPrice),
            'nego_status' => $negoStatus,
            'fallback_status' => $fallbackStatus,
        ];
    }

    private function getDrivers(): array
    {
        return [
            [
                'name' => 'Budi Santoso',
                'service' => 'ride',
                'vehicle' => 'Honda Vario',
                'plate' => 'N 1234 AI',
                'rating' => 4.9,
                'reliability' => 96,
                'cancel_rate' => 2,
                'lat' => -7.9205,
                'lng' => 112.5968,
                'completed_orders' => 1240,
            ],
            [
                'name' => 'Andi Pratama',
                'service' => 'ride',
                'vehicle' => 'Yamaha NMAX',
                'plate' => 'N 5567 SR',
                'rating' => 4.7,
                'reliability' => 91,
                'cancel_rate' => 5,
                'lat' => -7.9448,
                'lng' => 112.6179,
                'completed_orders' => 980,
            ],
            [
                'name' => 'Rizky Maulana',
                'service' => 'car',
                'vehicle' => 'Toyota Avanza',
                'plate' => 'N 7788 CA',
                'rating' => 4.8,
                'reliability' => 94,
                'cancel_rate' => 3,
                'lat' => -7.9666,
                'lng' => 112.6326,
                'completed_orders' => 1120,
            ],
            [
                'name' => 'Dewi Lestari',
                'service' => 'car',
                'vehicle' => 'Honda Brio',
                'plate' => 'N 8899 CB',
                'rating' => 4.6,
                'reliability' => 88,
                'cancel_rate' => 7,
                'lat' => -7.9553,
                'lng' => 112.6081,
                'completed_orders' => 760,
            ],
            [
                'name' => 'Sinta Amelia',
                'service' => 'food',
                'vehicle' => 'Honda Beat',
                'plate' => 'N 3412 FD',
                'rating' => 4.8,
                'reliability' => 93,
                'cancel_rate' => 4,
                'lat' => -7.9345,
                'lng' => 112.6052,
                'completed_orders' => 870,
            ],
            [
                'name' => 'Fajar Nugroho',
                'service' => 'send',
                'vehicle' => 'Suzuki Carry Box',
                'plate' => 'N 9001 SD',
                'rating' => 4.7,
                'reliability' => 92,
                'cancel_rate' => 4,
                'lat' => -7.9784,
                'lng' => 112.6304,
                'completed_orders' => 690,
            ],
        ];
    }

    private function matchBestDriver(
        array $drivers,
        string $serviceType,
        float $pickupLat,
        float $pickupLng,
        float $tripDistance,
        string $timeCondition
    ): array {
        $filteredDrivers = array_filter($drivers, function ($driver) use ($serviceType) {
            return $driver['service'] === $serviceType;
        });

        if (empty($filteredDrivers)) {
            $filteredDrivers = $drivers;
        }

        foreach ($filteredDrivers as $key => $driver) {
            $driverDistance = $this->calculateDistance(
                $pickupLat,
                $pickupLng,
                $driver['lat'],
                $driver['lng']
            );

            $score = 0;
            $score += $driver['reliability'] * 0.40;
            $score += $driver['rating'] * 10 * 0.25;
            $score += (100 - $driver['cancel_rate']) * 0.15;
            $score += max(0, 100 - ($driverDistance * 18)) * 0.20;

            if ($timeCondition === 'rain' || $timeCondition === 'night') {
                $score += 3;
            }

            if ($tripDistance > 10 && $serviceType === 'car') {
                $score += 4;
            }

            $filteredDrivers[$key]['distance_to_pickup'] = round($driverDistance, 2);
            $filteredDrivers[$key]['matching_score'] = round($score, 2);
        }

        usort($filteredDrivers, function ($a, $b) {
            return $b['matching_score'] <=> $a['matching_score'];
        });

        return $filteredDrivers[0];
    }

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;

        $latDistance = deg2rad($lat2 - $lat1);
        $lngDistance = deg2rad($lng2 - $lng1);

        $a = sin($latDistance / 2) * sin($latDistance / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($lngDistance / 2) * sin($lngDistance / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function calculateQualityScore(array $selectedDriver, int $surgePercentage, string $negoStatus): array
    {
        $transparencyScore = 92;
        $reliabilityScore = $selectedDriver['reliability'];
        $pricingFairnessScore = 100 - $surgePercentage;

        if (str_contains($negoStatus, 'diterima')) {
            $pricingFairnessScore += 5;
        }

        $pricingFairnessScore = min(100, $pricingFairnessScore);

        $average = round(($transparencyScore + $reliabilityScore + $pricingFairnessScore) / 3, 2);

        return [
            'transparency' => $transparencyScore,
            'reliability' => $reliabilityScore,
            'pricing_fairness' => $pricingFairnessScore,
            'average' => $average,
        ];
    }

    private function getPromos(): array
    {
        return [
            [
                'code' => 'SMART10',
                'title' => 'Diskon 10%',
                'description' => 'Potongan 10% untuk semua layanan SmartRide AI.',
            ],
            [
                'code' => 'HEMAT5',
                'title' => 'Diskon Rp 5.000',
                'description' => 'Potongan langsung Rp 5.000 untuk perjalanan hemat.',
            ],
        ];
    }

    private function getHistories(): array
    {
        return [
            [
                'route' => 'Kampus UMM - Alun-Alun Malang',
                'service' => 'SmartRide',
                'price' => 25000,
                'status' => 'Selesai',
            ],
            [
                'route' => 'Dinoyo - Stasiun Malang',
                'service' => 'SmartCar',
                'price' => 42000,
                'status' => 'Selesai',
            ],
            [
                'route' => 'Lowokwaru - Sawojajar',
                'service' => 'SmartSend',
                'price' => 30000,
                'status' => 'Selesai',
            ],
        ];
    }
}