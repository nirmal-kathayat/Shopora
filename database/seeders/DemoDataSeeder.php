<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed demo business data for all menus except Settings (admins/roles/permissions).
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('store_records')->truncate();
        DB::table('inventory_stocks')->truncate();
        DB::table('sales_payment_mode')->truncate();
        DB::table('sales_products')->truncate();
        DB::table('sales')->truncate();
        DB::table('purchase_inventory_items')->truncate();
        DB::table('purchase_inventory')->truncate();
        DB::table('inventory_items')->truncate();
        DB::table('customers')->truncate();
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = Carbon::now();

        // Categories
        $categoryIds = [];
        foreach (['Electronics', 'Grocery', 'Stationery', 'Hardware', 'Beverages', 'Personal Care'] as $title) {
            $categoryIds[] = DB::table('categories')->insertGetId([
                'title' => $title,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Inventory items
        $items = [
            ['title' => 'USB Cable Type-C', 'unit' => 'pcs', 'code' => 'EL-001', 'category_id' => $categoryIds[0], 'price_per_unit' => 450],
            ['title' => 'Wireless Mouse', 'unit' => 'pcs', 'code' => 'EL-002', 'category_id' => $categoryIds[0], 'price_per_unit' => 1200],
            ['title' => 'Laptop Charger 65W', 'unit' => 'pcs', 'code' => 'EL-003', 'category_id' => $categoryIds[0], 'price_per_unit' => 2800],
            ['title' => 'Basmati Rice 25kg', 'unit' => 'bag', 'code' => 'GR-001', 'category_id' => $categoryIds[1], 'price_per_unit' => 4200],
            ['title' => 'Cooking Oil 5L', 'unit' => 'jar', 'code' => 'GR-002', 'category_id' => $categoryIds[1], 'price_per_unit' => 1450],
            ['title' => 'Sugar 1kg', 'unit' => 'pkt', 'code' => 'GR-003', 'category_id' => $categoryIds[1], 'price_per_unit' => 140],
            ['title' => 'A4 Paper Ream', 'unit' => 'ream', 'code' => 'ST-001', 'category_id' => $categoryIds[2], 'price_per_unit' => 650],
            ['title' => 'Ball Pen Box (50)', 'unit' => 'box', 'code' => 'ST-002', 'category_id' => $categoryIds[2], 'price_per_unit' => 500],
            ['title' => 'Notebook Spiral', 'unit' => 'pcs', 'code' => 'ST-003', 'category_id' => $categoryIds[2], 'price_per_unit' => 180],
            ['title' => 'Screwdriver Set', 'unit' => 'set', 'code' => 'HW-001', 'category_id' => $categoryIds[3], 'price_per_unit' => 950],
            ['title' => 'LED Bulb 12W', 'unit' => 'pcs', 'code' => 'HW-002', 'category_id' => $categoryIds[3], 'price_per_unit' => 220],
            ['title' => 'Extension Cord 5m', 'unit' => 'pcs', 'code' => 'HW-003', 'category_id' => $categoryIds[3], 'price_per_unit' => 780],
            ['title' => 'Mineral Water 20L', 'unit' => 'jar', 'code' => 'BV-001', 'category_id' => $categoryIds[4], 'price_per_unit' => 100],
            ['title' => 'Soft Drink 1.5L', 'unit' => 'btl', 'code' => 'BV-002', 'category_id' => $categoryIds[4], 'price_per_unit' => 180],
            ['title' => 'Instant Coffee 200g', 'unit' => 'jar', 'code' => 'BV-003', 'category_id' => $categoryIds[4], 'price_per_unit' => 850],
            ['title' => 'Hand Wash 500ml', 'unit' => 'btl', 'code' => 'PC-001', 'category_id' => $categoryIds[5], 'price_per_unit' => 320],
            ['title' => 'Toothpaste 150g', 'unit' => 'pcs', 'code' => 'PC-002', 'category_id' => $categoryIds[5], 'price_per_unit' => 150],
            ['title' => 'Face Wash 100ml', 'unit' => 'pcs', 'code' => 'PC-003', 'category_id' => $categoryIds[5], 'price_per_unit' => 280],
        ];

        $itemIds = [];
        $this->clearDemoProductImages();
        foreach ($items as $item) {
            $image = $this->seedProductImage($item['code'], $item['title']);
            $itemIds[] = DB::table('inventory_items')->insertGetId(array_merge($item, [
                'image' => $image,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // Customers
        $customers = [
            ['name' => 'Ram Bahadur Thapa', 'address' => 'Kathmandu', 'ph_number' => '9841001001', 'pan_number' => '601234567'],
            ['name' => 'Sita Devi Shrestha', 'address' => 'Lalitpur', 'ph_number' => '9841001002', 'pan_number' => null],
            ['name' => 'Hari Krishna Magar', 'address' => 'Bhaktapur', 'ph_number' => '9841001003', 'pan_number' => '609876543'],
            ['name' => 'Anita Gurung', 'address' => 'Pokhara', 'ph_number' => '9856001122', 'pan_number' => null],
            ['name' => 'Bikash Tamang', 'address' => 'Chitwan', 'ph_number' => '9801112233', 'pan_number' => '601122334'],
            ['name' => 'Sunita Karki', 'address' => 'Biratnagar', 'ph_number' => '9812345678', 'pan_number' => null],
            ['name' => 'Prakash Adhikari', 'address' => 'Butwal', 'ph_number' => '9847012345', 'pan_number' => '605544332'],
            ['name' => 'Manisha Rai', 'address' => 'Dharan', 'ph_number' => '9808765432', 'pan_number' => null],
            ['name' => 'Nabin Basnet', 'address' => 'Hetauda', 'ph_number' => '9845123456', 'pan_number' => '607788990'],
            ['name' => 'Kabita Maharjan', 'address' => 'Patan', 'ph_number' => '9851034567', 'pan_number' => null],
            ['name' => 'Ganesh Trading Pvt Ltd', 'address' => 'New Road, KTM', 'ph_number' => '014411223', 'pan_number' => '600112233'],
            ['name' => 'Himalayan Mart', 'address' => 'Thamel', 'ph_number' => '014422334', 'pan_number' => '600445566'],
        ];

        $customerIds = [];
        foreach ($customers as $customer) {
            $customerIds[] = DB::table('customers')->insertGetId(array_merge($customer, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $paymentModes = DB::table('payment_modes')->pluck('id')->all();
        if (empty($paymentModes)) {
            $this->call(PaymentModeSeeder::class);
            $paymentModes = DB::table('payment_modes')->pluck('id')->all();
        }

        $itemStock = array_fill_keys($itemIds, 0);

        // Purchases (+ stock in)
        $vendors = [
            ['vendor' => 'Tech Hub Nepal', 'address' => 'Putalisadak', 'pan_number' => '301122334'],
            ['vendor' => 'Valley Traders', 'address' => 'Kalanki', 'pan_number' => '302233445'],
            ['vendor' => 'Himal Supply Co', 'address' => 'Balaju', 'pan_number' => '303344556'],
            ['vendor' => 'Everest Distributors', 'address' => 'Koteshwor', 'pan_number' => '304455667'],
            ['vendor' => 'City Wholesale', 'address' => 'Gongabu', 'pan_number' => '305566778'],
            ['vendor' => 'Sunrise Imports', 'address' => 'Baneshwor', 'pan_number' => '306677889'],
            ['vendor' => 'Nepal Mart Depot', 'address' => 'Satdobato', 'pan_number' => '307788990'],
            ['vendor' => 'Prime Stock Yard', 'address' => 'Gwarko', 'pan_number' => '308899001'],
        ];

        $purchaseIds = [];
        foreach ($vendors as $i => $vendor) {
            // spread purchases across the last ~45 days, ending today
            $pDaysAgo = (int) round(45 * (count($vendors) - 1 - $i) / max(count($vendors) - 1, 1));
            $billDate = Carbon::now()->subDays($pDaysAgo)->toDateString();
            $purchaseId = DB::table('purchase_inventory')->insertGetId([
                'vendor' => $vendor['vendor'],
                'bill_date' => $billDate,
                'address' => $vendor['address'],
                'pan_number' => $vendor['pan_number'],
                'vat_amount' => rand(500, 3500),
                'created_at' => $now->copy()->subDays($pDaysAgo),
                'updated_at' => $now,
            ]);
            $purchaseIds[] = $purchaseId;

            // 3-4 line items per purchase
            $picked = collect($itemIds)->shuffle()->take(rand(3, 5));
            foreach ($picked as $itemId) {
                $qty = rand(30, 120);
                $rate = DB::table('inventory_items')->where('id', $itemId)->value('price_per_unit');
                $purchaseRate = round($rate * 0.7, 2);

                DB::table('purchase_inventory_items')->insert([
                    'purchase_inventory_id' => $purchaseId,
                    'inventory_item_id' => $itemId,
                    'qty' => $qty,
                    'rate' => $purchaseRate,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('inventory_stocks')->insert([
                    'inventory_item_id' => $itemId,
                    'purchase_inventory_id' => $purchaseId,
                    'sales_id' => null,
                    'qty' => $qty,
                    'remarks' => 'Purchase from ' . $vendor['vendor'],
                    'created_at' => $now->copy()->subDays($pDaysAgo),
                    'updated_at' => $now,
                ]);

                DB::table('store_records')->insert([
                    'inventory_item_id' => $itemId,
                    'qty' => $qty,
                    'purchase_inventory_id' => $purchaseId,
                    'type' => 'purchase',
                    'created_at' => $now->copy()->subDays($pDaysAgo),
                    'updated_at' => $now,
                ]);

                $itemStock[$itemId] += $qty;
            }
        }

        // Sales (+ stock out) — feeds Sales, Invoice, Dashboard, Reports
        $orderBys = ['Walk-in', 'Phone Order', 'Counter', 'Online', 'Dealer'];

        // Spread ~50 sales across the last 35 days, guaranteeing recent activity incl. today.
        $saleOffsets = [0, 0, 0, 1, 1, 2, 2, 3, 4];
        for ($k = 0; $k < 41; $k++) {
            $saleOffsets[] = rand(0, 35);
        }
        sort($saleOffsets); // recent-first so today's sales get stock priority

        foreach ($saleOffsets as $s => $offset) {
            $saleDate = Carbon::now()->subDays($offset)->setTime(rand(9, 19), rand(0, 59), rand(0, 59));

            try {
                $nepaliDate = \NepaliDate\Facades\NepaliDate::create($saleDate)->toBS();
            } catch (\Throwable $e) {
                $nepaliDate = $saleDate->format('Y-m-d');
            }

            $salesId = DB::table('sales')->insertGetId([
                'order_by' => $orderBys[$s % count($orderBys)],
                'nepali_date' => $nepaliDate,
                'customer_id' => $customerIds[array_rand($customerIds)],
                'discount' => $s % 3 === 0 ? rand(0, 4) * 50 : 0,
                'created_at' => $saleDate,
                'updated_at' => $saleDate,
            ]);

            $availableItems = collect($itemIds)->filter(function ($itemId) use ($itemStock) {
                return ($itemStock[$itemId] ?? 0) > 0;
            });

            if ($availableItems->isEmpty()) {
                continue;
            }

            $lineItems = $availableItems->shuffle()->take(rand(2, min(5, $availableItems->count())));
            $totalAmount = 0;
            $firstPaymentMode = $paymentModes[array_rand($paymentModes)];

            foreach ($lineItems as $itemId) {
                $available = (int) ($itemStock[$itemId] ?? 0);
                if ($available <= 0) {
                    continue;
                }

                $price = (float) DB::table('inventory_items')->where('id', $itemId)->value('price_per_unit');
                $qty = min(rand(1, 8), $available);
                $lineTotal = $qty * $price;
                $totalAmount += $lineTotal;

                DB::table('sales_products')->insert([
                    'sales_id' => $salesId,
                    'product_id' => $itemId,
                    'qty' => $qty,
                    'price_per_unit' => $price,
                    'payment_mode' => (string) $firstPaymentMode,
                    'discount' => 0,
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                ]);

                DB::table('inventory_stocks')->insert([
                    'inventory_item_id' => $itemId,
                    'purchase_inventory_id' => null,
                    'sales_id' => $salesId,
                    'qty' => $qty,
                    'remarks' => 'Sale',
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                ]);

                $itemStock[$itemId] -= $qty;
            }

            if ($totalAmount <= 0) {
                DB::table('sales')->where('id', $salesId)->delete();

                continue;
            }

            DB::table('sales_payment_mode')->insert([
                'sales_id' => $salesId,
                'payment_mode_id' => $firstPaymentMode,
                'amount' => (int) round($totalAmount),
                'created_at' => $saleDate,
                'updated_at' => $saleDate,
            ]);
        }

        $this->command?->info('Demo data seeded: categories, inventory items (with images), customers, purchases, sales/invoices/stocks.');
    }

    /**
     * Copy bundled real product photo into public/image (no storage:link).
     */
    private function seedProductImage(string $code, string $title): ?string
    {
        $directory = public_path('image');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'demo_' . Str::lower(Str::slug($code, '_')) . '.jpg';
        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
        $assetPath = database_path('seeders/assets/demo-products/' . $filename);

        if (is_file($assetPath) && filesize($assetPath) > 0) {
            copy($assetPath, $fullPath);

            return $filename;
        }

        $downloaded = $this->downloadProductImage($code, $title, $fullPath);
        if ($downloaded) {
            return $filename;
        }

        $this->command?->warn("Image skip for {$code}: no bundled asset and download failed.");

        return null;
    }

    private function clearDemoProductImages(): void
    {
        $publicDir = public_path('image');
        foreach (glob($publicDir . DIRECTORY_SEPARATOR . 'demo_*.jpg') ?: [] as $oldFile) {
            @unlink($oldFile);
        }
    }

    private function downloadProductImage(string $code, string $title, string $fullPath): bool
    {
        $tagMap = [
            'EL-001' => 'usb,cable',
            'EL-002' => 'computer,mouse',
            'EL-003' => 'laptop,charger',
            'GR-001' => 'rice,bag',
            'GR-002' => 'cooking,oil',
            'GR-003' => 'sugar,packet',
            'ST-001' => 'paper,office',
            'ST-002' => 'pen,stationery',
            'ST-003' => 'notebook,spiral',
            'HW-001' => 'screwdriver,tools',
            'HW-002' => 'light,bulb',
            'HW-003' => 'power,cable',
            'BV-001' => 'water,bottle',
            'BV-002' => 'soda,drink',
            'BV-003' => 'coffee,jar',
            'PC-001' => 'soap,handwash',
            'PC-002' => 'toothpaste',
            'PC-003' => 'skincare,face',
        ];

        $tags = $tagMap[$code] ?? Str::slug($title, ',');
        $lock = preg_replace('/[^a-zA-Z0-9]/', '', $code);
        $url = "https://loremflickr.com/200/200/{$tags}?lock={$lock}";

        $context = stream_context_create([
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            'http' => ['timeout' => 30, 'header' => 'User-Agent: ShoporaDemoSeeder/1.0'],
        ]);

        $body = @file_get_contents($url, false, $context);
        if (!$body || strlen($body) < 2000) {
            return false;
        }

        return file_put_contents($fullPath, $body) !== false;
    }
}
