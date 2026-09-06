<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Fills the storefront with a catalogue worth browsing.
 *
 * Unlike DemoDataSeeder this is additive and re-runnable: it matches on
 * inventory_items.code, so running it again refreshes prices, copy and photos
 * without touching customers, orders or the sales history. Blank detail
 * columns are filled; anything already written by hand in the admin is left
 * alone.
 */
class CatalogueSeeder extends Seeder
{
    /** Where the bundled packshots live. */
    private const ASSETS = 'seeders/assets/catalogue';

    /** Marks the stock rows this seeder owns, so a re-run replaces them. */
    private const STOCK_REMARK = 'Opening stock (catalogue seed)';

    /** The vendor name on the one purchase bill that carries opening stock. */
    private const OPENING_VENDOR = 'Shopora Opening Stock';

    private ?int $billId = null;

    public function run(): void
    {
        $now = Carbon::now();
        $categories = DB::table('categories')->pluck('id', 'title');

        foreach ($this->products() as $row) {
            $categoryId = $categories[$row['category']] ?? null;
            if (! $categoryId) {
                $this->command?->warn("Skipping {$row['code']}: no '{$row['category']}' category.");
                continue;
            }

            $existing = DB::table('inventory_items')->where('code', $row['code'])->first();
            $image = $this->installImage($row['code'], $existing);

            $core = [
                'title' => $row['title'],
                'unit' => $row['unit'],
                'category_id' => $categoryId,
                'price_per_unit' => $row['price'],
                'compare_at_price' => $row['compare_at'] ?? null,
                'image' => $image,
                'updated_at' => $now,
            ];

            // Detail copy is only written where the admin has left a blank, so
            // anything edited by hand in the panel survives a re-run.
            $detail = [
                'description' => $row['description'],
                'brand' => $row['brand'],
                'net_volume' => $row['net_volume'],
                'country_of_origin' => $row['origin'],
                'highlights' => json_encode($row['highlights']),
                'features' => json_encode($row['features']),
            ];

            if ($existing) {
                foreach ($detail as $column => $value) {
                    if ($this->isBlank($existing->{$column})) {
                        $core[$column] = $value;
                    }
                }
                DB::table('inventory_items')->where('id', $existing->id)->update($core);
                $itemId = $existing->id;
            } else {
                $itemId = DB::table('inventory_items')->insertGetId(
                    $core + $detail + ['code' => $row['code'], 'created_at' => $now]
                );
            }

            $this->installGallery($itemId, $row['code'], $now);
            $this->setStock($itemId, $row['stock'], $now);
        }

        $this->installCategoryImages($now);

        $this->command?->info('Catalogue seeded: ' . count($this->products()) . ' products.');
    }

    /**
     * A group shot per aisle, drawn from that category's own products, so the
     * "Shop by category" tiles show what is actually behind them.
     */
    private function installCategoryImages(Carbon $now): void
    {
        foreach (DB::table('categories')->get() as $category) {
            $source = database_path(self::ASSETS . '/category-' . $category->slug . '.jpg');
            if (! $category->slug || ! is_file($source)) {
                continue;
            }

            $filename = 'cat_aisle_' . str_replace('-', '_', $category->slug) . '.jpg';
            copy($source, $this->imageDir() . DIRECTORY_SEPARATOR . $filename);

            DB::table('categories')->where('id', $category->id)->update([
                'image' => $filename,
                'image_alt' => $category->title . ' at Shopora',
                'updated_at' => $now,
            ]);
        }
    }

    /** '', null and an empty JSON array all count as "nothing written yet". */
    private function isBlank($value): bool
    {
        return $value === null || $value === '' || $value === '[]' || $value === '{}';
    }

    /**
     * Copies the bundled packshot into public/image and returns its filename.
     *
     * Every product gets one, so the grid reads as a single set rather than a
     * mix of styles. A photo uploaded through the admin is not thrown away:
     * where one was worth keeping it is bundled here as a `_4` gallery asset
     * and comes back on the product's detail page.
     */
    private function installImage(string $code, ?object $existing): ?string
    {
        $source = database_path(self::ASSETS . '/' . $code . '.jpg');
        $current = $existing->image ?? null;

        if (! is_file($source)) {
            return $current;
        }

        $filename = 'cat_' . strtolower(str_replace('-', '_', $code)) . '.jpg';
        copy($source, $this->imageDir() . DIRECTORY_SEPARATOR . $filename);

        // The old GD placeholder is no longer referenced by anything.
        if ($current && str_starts_with($current, 'demo_')) {
            @unlink($this->imageDir() . DIRECTORY_SEPARATOR . $current);
        }

        return $filename;
    }

    /**
     * Rebuilds the product's gallery from the bundled `_2`, `_3` (and any `_4`)
     * assets: an angled view, a close detail, and a real photograph where the
     * shop has one. The seeder owns this table for the products it knows, so a
     * re-run replaces the set instead of appending a second copy of it.
     */
    private function installGallery(int $itemId, string $code, Carbon $now): void
    {
        DB::table('product_images')->where('inventory_item_id', $itemId)->delete();

        $slug = strtolower(str_replace('-', '_', $code));
        $order = 0;

        foreach (['_2' => 'jpg', '_3' => 'jpg', '_4' => 'png'] as $suffix => $ext) {
            $source = database_path(self::ASSETS . '/' . $code . $suffix . '.' . $ext);
            if (! is_file($source)) {
                continue;
            }

            $filename = 'cat_' . $slug . $suffix . '.' . $ext;
            copy($source, $this->imageDir() . DIRECTORY_SEPARATOR . $filename);

            DB::table('product_images')->insert([
                'inventory_item_id' => $itemId,
                'image' => $filename,
                'sort_order' => ++$order,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function imageDir(): string
    {
        $directory = public_path('image');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $directory;
    }

    /**
     * Books opening stock for a product that has none yet.
     *
     * A product already carrying purchases and sales is left alone: its net
     * figure is real history, and re-running the seeder must not inflate it.
     * The row is hung off a purchase bill because net stock counts only rows
     * with a purchase_inventory_id on the way in - a row with neither id
     * cancels to zero and would look like the stock never arrived.
     */
    private function setStock(int $itemId, int $qty, Carbon $now): void
    {
        $hasHistory = DB::table('inventory_stocks')
            ->where('inventory_item_id', $itemId)
            ->where(function ($q) {
                $q->whereNull('remarks')->orWhere('remarks', '!=', self::STOCK_REMARK);
            })
            ->exists();

        if ($hasHistory) {
            return;
        }

        DB::table('inventory_stocks')
            ->where('inventory_item_id', $itemId)
            ->where('remarks', self::STOCK_REMARK)
            ->delete();

        DB::table('purchase_inventory_items')
            ->where('purchase_inventory_id', $this->openingBill($now))
            ->where('inventory_item_id', $itemId)
            ->delete();

        if ($qty <= 0) {
            return;
        }

        $rate = round((float) DB::table('inventory_items')->where('id', $itemId)->value('price_per_unit') * 0.72, 2);

        DB::table('purchase_inventory_items')->insert([
            'purchase_inventory_id' => $this->openingBill($now),
            'inventory_item_id' => $itemId,
            'qty' => $qty,
            'rate' => $rate,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('inventory_stocks')->insert([
            'inventory_item_id' => $itemId,
            'purchase_inventory_id' => $this->openingBill($now),
            'sales_id' => null,
            'qty' => $qty,
            'remarks' => self::STOCK_REMARK,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /** The single purchase bill every opening-stock row hangs off. */
    private function openingBill(Carbon $now): int
    {
        if ($this->billId !== null) {
            return $this->billId;
        }

        $existing = DB::table('purchase_inventory')->where('vendor', self::OPENING_VENDOR)->value('id');
        if ($existing) {
            return $this->billId = (int) $existing;
        }

        return $this->billId = (int) DB::table('purchase_inventory')->insertGetId([
            'vendor' => self::OPENING_VENDOR,
            'bill_date' => $now->toDateString(),
            'address' => 'Kalimati, Kathmandu',
            'pan_number' => '309900112',
            'vat_amount' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * The shop's catalogue. Prices are NPR, stock is the opening figure a
     * brand-new product starts life with (0 and the 1-5 band are deliberate -
     * the storefront's availability filter needs all three states to show).
     *
     * @return array<int,array<string,mixed>>
     */
    private function products(): array
    {
        return [
            // ---------------------------------------------------------- Electronics
            [
                'code' => 'EL-001', 'title' => 'USB Cable Type-C', 'unit' => 'pcs',
                'category' => 'Electronics', 'price' => 450, 'compare_at' => 550, 'stock' => 60,
                'brand' => 'Voltra', 'net_volume' => '1 m', 'origin' => 'China',
                'description' => "A braided USB-C to USB-C cable rated for 60W, so it charges a phone at full speed and still has enough headroom for a tablet or a small laptop.\n\nThe nylon sleeve and moulded strain relief are there for the way cables actually die - yanked out of a socket, wound round a charger, shut in a bag. Data runs at 480Mbps, enough for backups and file transfers.",
                'highlights' => [
                    ['icon' => 'zap', 'title' => '60W Fast Charge', 'subtitle' => 'Phone, tablet or a light laptop'],
                    ['icon' => 'shield', 'title' => 'Braided Sleeve', 'subtitle' => 'Survives 10,000 bends'],
                    ['icon' => 'check', 'title' => '6-Month Warranty', 'subtitle' => 'Replaced if it fails'],
                ],
                'features' => ['USB-C to USB-C, 1 metre', '60W power delivery', '480Mbps data transfer', 'Nylon braided jacket'],
            ],
            [
                'code' => 'EL-002', 'title' => 'Wireless Mouse', 'unit' => 'pcs',
                'category' => 'Electronics', 'price' => 1200, 'compare_at' => null, 'stock' => 0,
                'brand' => 'Voltra', 'net_volume' => '95 g', 'origin' => 'China',
                'description' => "A quiet 2.4GHz mouse that pairs through a nano receiver and stays out of the way. One AA cell runs it for about twelve months.\n\nThe switches are the silent kind, which matters in a shared room or a night shift. 1600 DPI is right for everyday office work on any desk that is not glass.",
                'highlights' => [
                    ['icon' => 'feather', 'title' => 'Silent Clicks', 'subtitle' => 'Barely audible switches'],
                    ['icon' => 'zap', 'title' => '12-Month Battery', 'subtitle' => 'On a single AA cell'],
                    ['icon' => 'check', 'title' => 'Plug and Play', 'subtitle' => 'No driver to install'],
                ],
                'features' => ['2.4GHz nano receiver', '1600 DPI optical sensor', 'Silent left and right switch', 'Runs on 1x AA'],
            ],
            [
                'code' => 'EL-003', 'title' => 'Laptop Charger 65W', 'unit' => 'pcs',
                'category' => 'Electronics', 'price' => 1000, 'compare_at' => 1100, 'stock' => 40,
                'brand' => 'Voltra', 'net_volume' => '180 g', 'origin' => 'China',
                'description' => "A compact 65W USB-C adapter that replaces the brick most laptops ship with, at about half the size.",
                'highlights' => [
                    ['icon' => 'zap', 'title' => 'Fast 65W Charging', 'subtitle' => '0 to 60% in about 45 minutes'],
                    ['icon' => 'shield', 'title' => 'Safe by Design', 'subtitle' => 'Overheat and surge protection'],
                    ['icon' => 'feather', 'title' => 'Travel Light', 'subtitle' => 'Half the size of a stock charger'],
                ],
                'features' => ['65W USB-C Power Delivery', '100-240V for travel', 'Fire-resistant PC casing', 'Works with most USB-C laptops'],
            ],
            [
                'code' => 'EL-004', 'title' => 'Bluetooth Speaker 10W', 'unit' => 'pcs',
                'category' => 'Electronics', 'price' => 2450, 'compare_at' => 2900, 'stock' => 26,
                'brand' => 'Voltra', 'net_volume' => '340 g', 'origin' => 'China',
                'description' => "A 10W portable speaker with enough bass to fill a room and an IPX5 shell that shrugs off a splash - a kitchen, a rooftop, a bathroom shelf.\n\nBluetooth 5.3 holds a connection across about ten metres, and playback runs roughly twelve hours at a sensible volume. There is a 3.5mm socket for anything that will not pair.",
                'highlights' => [
                    ['icon' => 'zap', 'title' => '12-Hour Playback', 'subtitle' => 'On one charge'],
                    ['icon' => 'droplets', 'title' => 'IPX5 Splashproof', 'subtitle' => 'Rain and kitchen safe'],
                    ['icon' => 'star', 'title' => 'Bluetooth 5.3', 'subtitle' => 'Stable up to 10 metres'],
                ],
                'features' => ['10W driver with passive radiator', 'Bluetooth 5.3 and 3.5mm aux', 'USB-C charging', 'Built-in mic for calls'],
            ],
            [
                'code' => 'EL-005', 'title' => 'Power Bank 20000mAh', 'unit' => 'pcs',
                'category' => 'Electronics', 'price' => 3200, 'compare_at' => null, 'stock' => 18,
                'brand' => 'Voltra', 'net_volume' => '420 g', 'origin' => 'China',
                'description' => "20000mAh is roughly four full phone charges, or a day and a half away from a socket - useful in a city that still loses power without warning.\n\nThe USB-C port does 22.5W in both directions, so the bank itself refills in about four hours instead of overnight. Two devices can draw at once.",
                'highlights' => [
                    ['icon' => 'zap', 'title' => '22.5W Two-Way', 'subtitle' => 'Refills in about 4 hours'],
                    ['icon' => 'check', 'title' => '4 Phone Charges', 'subtitle' => 'From one full bank'],
                    ['icon' => 'shield', 'title' => 'Cell Protection', 'subtitle' => 'Overcharge and short-circuit safe'],
                ],
                'features' => ['20000mAh lithium polymer', 'USB-C PD in and out', 'Two outputs at once', 'Four-LED charge display'],
            ],
            [
                'code' => 'EL-006', 'title' => 'HDMI Cable 2m', 'unit' => 'pcs',
                'category' => 'Electronics', 'price' => 550, 'compare_at' => null, 'stock' => 44,
                'brand' => 'Voltra', 'net_volume' => '2 m', 'origin' => 'China',
                'description' => "A 2-metre HDMI 2.0 lead that carries 4K at 60Hz - long enough to reach a wall-mounted screen from a table without hanging taut.\n\nGold-plated contacts and a shielded core keep the picture clean on a run this length, which is where cheap cables usually start dropping frames.",
                'highlights' => [
                    ['icon' => 'star', 'title' => '4K at 60Hz', 'subtitle' => 'HDMI 2.0, 18Gbps'],
                    ['icon' => 'shield', 'title' => 'Shielded Core', 'subtitle' => 'No flicker or dropout'],
                    ['icon' => 'check', 'title' => 'Universal Fit', 'subtitle' => 'TV, laptop, console, projector'],
                ],
                'features' => ['HDMI 2.0, 18Gbps bandwidth', '4K 60Hz and HDR', 'Gold-plated connectors', '2 metre length'],
            ],
            [
                'code' => 'EL-007', 'title' => 'Wireless Earbuds', 'unit' => 'pair',
                'category' => 'Electronics', 'price' => 2800, 'compare_at' => 3500, 'stock' => 3,
                'brand' => 'Voltra', 'net_volume' => '45 g', 'origin' => 'China',
                'description' => "In-ear buds with a charging case that carries about thirty hours between wall charges - five in the buds, the rest in the case.\n\nTouch controls handle calls and tracks, and the case takes USB-C, so it shares a cable with everything else on the desk.",
                'highlights' => [
                    ['icon' => 'zap', 'title' => '30 Hours Total', 'subtitle' => 'Buds plus charging case'],
                    ['icon' => 'droplet', 'title' => 'Sweat Resistant', 'subtitle' => 'IPX4 rated'],
                    ['icon' => 'check', 'title' => 'One-Tap Pairing', 'subtitle' => 'Reconnects on open'],
                ],
                'features' => ['Bluetooth 5.3', '5 hours per charge, 30 with case', 'Touch controls', 'USB-C charging case'],
            ],

            // -------------------------------------------------------------- Grocery
            [
                'code' => 'GR-001', 'title' => 'Basmati Rice 25kg', 'unit' => 'bag',
                'category' => 'Grocery', 'price' => 4200, 'compare_at' => 4600, 'stock' => 0,
                'brand' => 'Annapurna', 'net_volume' => '25 kg', 'origin' => 'India',
                'description' => "Long-grain basmati aged a full year before milling, which is what lets the grain stretch and stay separate instead of clumping.\n\nA 25kg sack is the size a household buys once a season, or a small kitchen goes through in a month. Store it dry and off the floor.",
                'highlights' => [
                    ['icon' => 'award', 'title' => 'Aged 12 Months', 'subtitle' => 'Longer grain, less starch'],
                    ['icon' => 'leaf', 'title' => 'Hand Sorted', 'subtitle' => 'Cleaned and stone-free'],
                    ['icon' => 'check', 'title' => 'Sealed Sack', 'subtitle' => 'Moisture and pest resistant'],
                ],
                'features' => ['25kg woven sack', 'Extra-long aged grain', 'No added polish or colour', 'Best within 12 months'],
            ],
            [
                'code' => 'GR-002', 'title' => 'Cooking Oil 5L', 'unit' => 'jar',
                'category' => 'Grocery', 'price' => 1450, 'compare_at' => null, 'stock' => 35,
                'brand' => 'Sunfield', 'net_volume' => '5 L', 'origin' => 'Nepal',
                'description' => "Refined sunflower oil in a 5-litre jar - light enough for everyday frying and neutral enough that it does not sit on top of what you cook.\n\nThe jar has a moulded handle and a screw cap, so it decants without a funnel and without the slow drip a pouch always leaves behind.",
                'highlights' => [
                    ['icon' => 'heart', 'title' => 'Light on the Stomach', 'subtitle' => 'Low in saturated fat'],
                    ['icon' => 'sun', 'title' => 'Vitamin A and D', 'subtitle' => 'Fortified as standard'],
                    ['icon' => 'check', 'title' => 'Sealed Jar', 'subtitle' => 'Tamper-evident cap'],
                ],
                'features' => ['5 litre food-grade jar', 'Refined sunflower oil', 'Fortified with vitamin A and D', 'High smoke point'],
            ],
            [
                'code' => 'GR-003', 'title' => 'Sugar 1kg', 'unit' => 'pkt',
                'category' => 'Grocery', 'price' => 140, 'compare_at' => null, 'stock' => 90,
                'brand' => 'Annapurna', 'net_volume' => '1 kg', 'origin' => 'Nepal',
                'description' => "Fine white sugar, sieved and sealed in a 1kg pack - the size that fits a kitchen jar without a leftover half-packet going hard in the cupboard.",
                'highlights' => [
                    ['icon' => 'sparkles', 'title' => 'Fine Crystals', 'subtitle' => 'Dissolves quickly in tea'],
                    ['icon' => 'check', 'title' => 'Sealed 1kg Pack', 'subtitle' => 'Stays dry and free-flowing'],
                    ['icon' => 'leaf', 'title' => 'Sulphur Free', 'subtitle' => 'No bleaching residue'],
                ],
                'features' => ['1kg sealed pack', 'Fine grain white sugar', 'Sulphur-free refining', 'Store in a dry place'],
            ],
            [
                'code' => 'GR-004', 'title' => 'Red Lentils 5kg', 'unit' => 'bag',
                'category' => 'Grocery', 'price' => 1150, 'compare_at' => null, 'stock' => 48,
                'brand' => 'Annapurna', 'net_volume' => '5 kg', 'origin' => 'Nepal',
                'description' => "Masuro dal, machine-cleaned and polished only enough to sort out grit and split husk - it cooks down in about fifteen minutes without soaking.\n\nFive kilos is a month of daily dal for a family of four.",
                'highlights' => [
                    ['icon' => 'leaf', 'title' => 'Stone Free', 'subtitle' => 'Cleaned in three passes'],
                    ['icon' => 'zap', 'title' => 'Cooks in 15 Minutes', 'subtitle' => 'No soaking needed'],
                    ['icon' => 'heart', 'title' => 'High in Protein', 'subtitle' => '24g per 100g'],
                ],
                'features' => ['5kg resealable bag', 'Split red lentil (masuro)', 'Triple cleaned', 'No added polish'],
            ],
            [
                'code' => 'GR-005', 'title' => 'Iodised Salt 1kg', 'unit' => 'pkt',
                'category' => 'Grocery', 'price' => 40, 'compare_at' => null, 'stock' => 150,
                'brand' => 'Sunfield', 'net_volume' => '1 kg', 'origin' => 'India',
                'description' => "Free-flowing iodised salt in a 1kg pack. Iodine is added to the level the government sets, and the pack is laminated so the kitchen humidity does not turn it into a block.",
                'highlights' => [
                    ['icon' => 'shield', 'title' => 'Iodine Fortified', 'subtitle' => 'Meets national standard'],
                    ['icon' => 'droplet', 'title' => 'Free Flowing', 'subtitle' => 'Will not cake up'],
                    ['icon' => 'check', 'title' => 'Laminated Pack', 'subtitle' => 'Keeps moisture out'],
                ],
                'features' => ['1kg laminated pack', 'Iodised to national standard', 'Free-flowing crystals', 'Vacuum evaporated'],
            ],
            [
                'code' => 'GR-006', 'title' => 'Wheat Flour 10kg', 'unit' => 'bag',
                'category' => 'Grocery', 'price' => 900, 'compare_at' => 980, 'stock' => 32,
                'brand' => 'Annapurna', 'net_volume' => '10 kg', 'origin' => 'Nepal',
                'description' => "Chakki-ground atta with the bran left in, which is why the dough stays soft an hour after kneading and the roti does not go leathery.\n\nGround in small batches and packed the same week, so it reaches the kitchen before the oils in the germ start to turn.",
                'highlights' => [
                    ['icon' => 'leaf', 'title' => 'Whole Wheat', 'subtitle' => 'Bran and germ retained'],
                    ['icon' => 'sun', 'title' => 'Stone Ground', 'subtitle' => 'Traditional chakki milling'],
                    ['icon' => 'heart', 'title' => 'High in Fibre', 'subtitle' => 'Softer roti, slower release'],
                ],
                'features' => ['10kg woven sack', 'Chakki stone ground', '100% whole wheat', 'Milled in small batches'],
            ],
            [
                'code' => 'GR-007', 'title' => 'Black Tea 500g', 'unit' => 'pkt',
                'category' => 'Grocery', 'price' => 420, 'compare_at' => null, 'stock' => 5,
                'brand' => 'Ilam Gold', 'net_volume' => '500 g', 'origin' => 'Nepal',
                'description' => "CTC black tea from Ilam, graded for strength rather than delicacy - this is the leaf that stands up to milk, sugar and a hard boil.\n\nHalf a kilo is around two hundred cups. The pack reseals, which matters more than most people think: tea goes flat in an open bag within a fortnight.",
                'highlights' => [
                    ['icon' => 'leaf', 'title' => 'Grown in Ilam', 'subtitle' => 'Eastern Nepal hill gardens'],
                    ['icon' => 'zap', 'title' => 'Strong CTC Grade', 'subtitle' => 'Built for milk tea'],
                    ['icon' => 'check', 'title' => 'Resealable Pack', 'subtitle' => 'Keeps the aroma in'],
                ],
                'features' => ['500g resealable pouch', 'CTC processed black tea', 'Single-origin Ilam', 'About 200 cups'],
            ],
            [
                'code' => 'GR-008', 'title' => 'Pure Ghee 1L', 'unit' => 'jar',
                'category' => 'Grocery', 'price' => 1650, 'compare_at' => 1800, 'stock' => 22,
                'brand' => 'Himal Dairy', 'net_volume' => '1 L', 'origin' => 'Nepal',
                'description' => "Cow ghee clarified the slow way, so it sets grainy rather than smooth - the texture people actually look for when they buy ghee by hand.\n\nIt keeps a year unopened at room temperature, and the smoke point is high enough to temper spices without burning them.",
                'highlights' => [
                    ['icon' => 'award', 'title' => 'Slow Clarified', 'subtitle' => 'Grainy set, full aroma'],
                    ['icon' => 'sun', 'title' => 'High Smoke Point', 'subtitle' => 'Safe for tempering'],
                    ['icon' => 'check', 'title' => 'No Vanaspati', 'subtitle' => '100% cow milk fat'],
                ],
                'features' => ['1 litre glass jar', 'Pure cow ghee', 'No hydrogenated fat', '12-month shelf life'],
            ],

            // ----------------------------------------------------------- Stationery
            [
                'code' => 'ST-001', 'title' => 'A4 Paper Ream', 'unit' => 'ream',
                'category' => 'Stationery', 'price' => 650, 'compare_at' => null, 'stock' => 0,
                'brand' => 'Copyline', 'net_volume' => '500 sheets', 'origin' => 'Indonesia',
                'description' => "70 GSM A4 in a 500-sheet ream, cut square and packed tight so it feeds a printer without a jam every twenty pages.\n\nBright enough for double-sided printing, which is what most offices actually need it for.",
                'highlights' => [
                    ['icon' => 'star', 'title' => '70 GSM', 'subtitle' => 'Prints both sides cleanly'],
                    ['icon' => 'check', 'title' => 'Jam Free', 'subtitle' => 'Square-cut, dust-free edge'],
                    ['icon' => 'leaf', 'title' => 'Sustainably Sourced', 'subtitle' => 'Managed plantation pulp'],
                ],
                'features' => ['500 sheets per ream', '70 GSM, 102 brightness', 'A4 (210 x 297mm)', 'Laser and inkjet safe'],
            ],
            [
                'code' => 'ST-002', 'title' => 'Ball Pen Box (50)', 'unit' => 'box',
                'category' => 'Stationery', 'price' => 500, 'compare_at' => null, 'stock' => 30,
                'brand' => 'Copyline', 'net_volume' => '50 pcs', 'origin' => 'India',
                'description' => "Fifty 0.7mm blue ball pens in a display box - the size a shop counter, a school or an office store cupboard runs through in a term.",
                'highlights' => [
                    ['icon' => 'droplet', 'title' => 'Smudge Free Ink', 'subtitle' => 'Dries as you write'],
                    ['icon' => 'check', 'title' => '50 in a Box', 'subtitle' => 'Counter display ready'],
                    ['icon' => 'star', 'title' => '1500m Write-out', 'subtitle' => 'Per pen'],
                ],
                'features' => ['Box of 50 pens', '0.7mm tip, blue ink', 'Transparent barrel', 'Low-viscosity quick-dry ink'],
            ],
            [
                'code' => 'ST-003', 'title' => 'Notebook Spiral', 'unit' => 'pcs',
                'category' => 'Stationery', 'price' => 180, 'compare_at' => null, 'stock' => 62,
                'brand' => 'Copyline', 'net_volume' => '160 pages', 'origin' => 'Nepal',
                'description' => "A 160-page A5 spiral notebook that opens flat and folds all the way back, which is the whole point of a spiral binding.\n\nThe paper takes gel and fountain ink without showing through, and the cover is laminated board rather than card.",
                'highlights' => [
                    ['icon' => 'check', 'title' => 'Opens Flat', 'subtitle' => 'Folds fully back on itself'],
                    ['icon' => 'droplet', 'title' => 'No Bleed Through', 'subtitle' => '80 GSM ruled paper'],
                    ['icon' => 'shield', 'title' => 'Laminated Cover', 'subtitle' => 'Survives a bag'],
                ],
                'features' => ['160 ruled pages', 'A5 size', 'Wire spiral binding', '80 GSM paper'],
            ],
            [
                'code' => 'ST-004', 'title' => 'Sticky Notes Pack', 'unit' => 'pack',
                'category' => 'Stationery', 'price' => 210, 'compare_at' => null, 'stock' => 55,
                'brand' => 'Copyline', 'net_volume' => '5 pads',  'origin' => 'China',
                'description' => "Five 100-sheet pads in five colours. The adhesive lifts and re-sticks a few times over without tearing the page underneath - the thing cheap pads get wrong.",
                'highlights' => [
                    ['icon' => 'sparkles', 'title' => '500 Sheets', 'subtitle' => 'Five colours, five pads'],
                    ['icon' => 'check', 'title' => 'Re-stickable', 'subtitle' => 'Lifts without tearing'],
                    ['icon' => 'star', 'title' => 'Takes Any Pen', 'subtitle' => 'Ball, gel or pencil'],
                ],
                'features' => ['5 pads of 100 sheets', '76 x 76mm', 'Five assorted colours', 'Repositionable adhesive'],
            ],
            [
                'code' => 'ST-005', 'title' => 'Whiteboard Marker Set', 'unit' => 'set',
                'category' => 'Stationery', 'price' => 340, 'compare_at' => null, 'stock' => 2,
                'brand' => 'Copyline', 'net_volume' => '4 pcs', 'origin' => 'China',
                'description' => "Four markers - black, blue, red, green - with a chisel tip that gives a thin line on its edge and a broad one flat.\n\nThe ink is low-odour and wipes off a dry board weeks later, provided the board itself is not scratched.",
                'highlights' => [
                    ['icon' => 'check', 'title' => 'Wipes Clean', 'subtitle' => 'Even weeks later'],
                    ['icon' => 'leaf', 'title' => 'Low Odour', 'subtitle' => 'Safe in a closed room'],
                    ['icon' => 'star', 'title' => 'Chisel Tip', 'subtitle' => 'Thin and broad in one pen'],
                ],
                'features' => ['Set of 4: black, blue, red, green', 'Chisel tip', 'Low-odour alcohol ink', 'Refillable barrel'],
            ],
            [
                'code' => 'ST-006', 'title' => 'Stapler Medium', 'unit' => 'pcs',
                'category' => 'Stationery', 'price' => 260, 'compare_at' => null, 'stock' => 40,
                'brand' => 'Copyline', 'net_volume' => '220 g', 'origin' => 'India',
                'description' => "A metal-bodied half-strip stapler that takes 24/6 pins and drives through about twenty sheets without needing a second push.\n\nIt flips open for pinning to a board, and the base rotates for a temporary staple that pulls out clean.",
                'highlights' => [
                    ['icon' => 'shield', 'title' => 'Metal Body', 'subtitle' => 'Not a plastic shell'],
                    ['icon' => 'check', 'title' => '20 Sheets', 'subtitle' => 'In one press'],
                    ['icon' => 'star', 'title' => 'Pin and Tack', 'subtitle' => 'Rotating base'],
                ],
                'features' => ['Takes 24/6 and 26/6 pins', '20-sheet capacity', 'Half-strip, 100 pins', 'Rotating anvil'],
            ],
            [
                'code' => 'ST-007', 'title' => 'File Folder (10 pack)', 'unit' => 'pack',
                'category' => 'Stationery', 'price' => 380, 'compare_at' => null, 'stock' => 26,
                'brand' => 'Copyline', 'net_volume' => '10 pcs', 'origin' => 'Nepal',
                'description' => "Ten A4 board folders with a printed label panel on the spine - enough to file a year of paperwork in one go.",
                'highlights' => [
                    ['icon' => 'check', 'title' => 'Ten in a Pack', 'subtitle' => 'A year of filing'],
                    ['icon' => 'shield', 'title' => '300 GSM Board', 'subtitle' => 'Holds its shape when full'],
                    ['icon' => 'star', 'title' => 'Labelled Spine', 'subtitle' => 'Readable on a shelf'],
                ],
                'features' => ['Pack of 10', 'A4 size', '300 GSM board', 'Printed spine label panel'],
            ],

            // ------------------------------------------------------------- Hardware
            [
                'code' => 'HW-001', 'title' => 'Screwdriver Set', 'unit' => 'set',
                'category' => 'Hardware', 'price' => 950, 'compare_at' => 1100, 'stock' => 45,
                'brand' => 'Sherpa Tools', 'net_volume' => '6 pcs', 'origin' => 'India',
                'description' => "Six chrome-vanadium drivers - three flat, three Phillips - with magnetic tips and a moulded grip that does not turn in a wet hand.\n\nThe shafts are hardened past the tip, which is where a cheap driver rounds off first.",
                'highlights' => [
                    ['icon' => 'shield', 'title' => 'Chrome Vanadium', 'subtitle' => 'Will not round off'],
                    ['icon' => 'zap', 'title' => 'Magnetic Tips', 'subtitle' => 'Holds the screw for you'],
                    ['icon' => 'check', 'title' => 'Six Sizes', 'subtitle' => 'Flat and Phillips'],
                ],
                'features' => ['6-piece set', 'Chrome vanadium steel', 'Magnetic tips', 'Anti-slip moulded grip'],
            ],
            [
                'code' => 'HW-002', 'title' => 'LED Bulb 12W', 'unit' => 'pcs',
                'category' => 'Hardware', 'price' => 220, 'compare_at' => null, 'stock' => 120,
                'brand' => 'Sherpa Tools', 'net_volume' => '12 W', 'origin' => 'China',
                'description' => "A 12W B22 bulb that puts out about the same light as an old 100W filament for an eighth of the power - the swap pays for itself inside a few months.\n\nIt runs on anything from 140V up, which matters where the supply sags in the evening.",
                'highlights' => [
                    ['icon' => 'sun', 'title' => '1080 Lumens', 'subtitle' => 'Replaces a 100W bulb'],
                    ['icon' => 'zap', 'title' => 'Wide Voltage', 'subtitle' => 'Stable from 140V'],
                    ['icon' => 'check', 'title' => '2-Year Warranty', 'subtitle' => '25,000 hour life'],
                ],
                'features' => ['12W, 1080 lumens', 'B22 bayonet cap', '6500K cool daylight', '140-260V input'],
            ],
            [
                'code' => 'HW-003', 'title' => 'Extension Cord 5m', 'unit' => 'pcs',
                'category' => 'Hardware', 'price' => 780, 'compare_at' => null, 'stock' => 52,
                'brand' => 'Sherpa Tools', 'net_volume' => '5 m', 'origin' => 'Nepal',
                'description' => "Four universal sockets and two USB ports on a five-metre lead, with a master switch and a resettable breaker rather than a fuse you have to find a replacement for.\n\nThe cable is 1.0mm copper, which is what actually lets it carry a full load without warming up.",
                'highlights' => [
                    ['icon' => 'shield', 'title' => 'Resettable Breaker', 'subtitle' => 'No fuse to replace'],
                    ['icon' => 'zap', 'title' => '4 Sockets + 2 USB', 'subtitle' => 'Charges without an adapter'],
                    ['icon' => 'check', 'title' => 'Pure Copper', 'subtitle' => '1.0mm, 5 metre lead'],
                ],
                'features' => ['5 metre 1.0mm copper cable', '4 universal sockets', '2 USB-A charging ports', '10A overload breaker'],
            ],
            [
                'code' => 'HW-004', 'title' => 'Claw Hammer 500g', 'unit' => 'pcs',
                'category' => 'Hardware', 'price' => 640, 'compare_at' => null, 'stock' => 34,
                'brand' => 'Sherpa Tools', 'net_volume' => '500 g', 'origin' => 'India',
                'description' => "A 500g drop-forged head on a fibreglass shaft - heavy enough for framing, light enough to hang a shelf without wearing your wrist out.\n\nThe claw is ground thin so it gets under a sunk nail head instead of skating over it.",
                'highlights' => [
                    ['icon' => 'shield', 'title' => 'Drop Forged Head', 'subtitle' => 'Hardened striking face'],
                    ['icon' => 'feather', 'title' => 'Fibreglass Shaft', 'subtitle' => 'Absorbs the shock'],
                    ['icon' => 'check', 'title' => 'Ground Claw', 'subtitle' => 'Lifts sunk nails'],
                ],
                'features' => ['500g forged steel head', 'Fibreglass handle', 'Anti-slip rubber grip', 'Polished claw'],
            ],
            [
                'code' => 'HW-005', 'title' => 'Measuring Tape 5m', 'unit' => 'pcs',
                'category' => 'Hardware', 'price' => 350, 'compare_at' => null, 'stock' => 68,
                'brand' => 'Sherpa Tools', 'net_volume' => '5 m', 'origin' => 'China',
                'description' => "Five metres of 19mm nylon-coated blade that stands out about two metres before it folds - enough to measure a wall on your own.\n\nMetric on one edge, inches on the other, with a thumb lock and a belt clip.",
                'highlights' => [
                    ['icon' => 'star', 'title' => '2m Standout', 'subtitle' => 'Measure single-handed'],
                    ['icon' => 'shield', 'title' => 'Nylon Coated', 'subtitle' => 'Markings do not wear off'],
                    ['icon' => 'check', 'title' => 'Metric and Imperial', 'subtitle' => 'Both edges printed'],
                ],
                'features' => ['5 metre x 19mm blade', 'Nylon-coated steel', 'Thumb lock and belt clip', 'Magnetic hook end'],
            ],
            [
                'code' => 'HW-006', 'title' => 'Adjustable Wrench 10in', 'unit' => 'pcs',
                'category' => 'Hardware', 'price' => 720, 'compare_at' => null, 'stock' => 29,
                'brand' => 'Sherpa Tools', 'net_volume' => '10 in', 'origin' => 'India',
                'description' => "A 10-inch adjustable that opens to 30mm, which covers most plumbing and furniture work without carrying a full spanner set.\n\nThe worm screw is machined tight, so the jaw does not creep open under load - the failure that ruins a nut.",
                'highlights' => [
                    ['icon' => 'shield', 'title' => 'Jaw Holds Firm', 'subtitle' => 'Tight-machined worm screw'],
                    ['icon' => 'check', 'title' => 'Opens to 30mm', 'subtitle' => 'Covers most plumbing'],
                    ['icon' => 'star', 'title' => 'Scale Marked Jaw', 'subtitle' => 'Set the size by eye'],
                ],
                'features' => ['10 inch (250mm) body', '30mm maximum jaw', 'Drop-forged carbon steel', 'Dipped anti-slip grip'],
            ],
            [
                'code' => 'HW-007', 'title' => 'Padlock 50mm', 'unit' => 'pcs',
                'category' => 'Hardware', 'price' => 480, 'compare_at' => null, 'stock' => 0,
                'brand' => 'Sherpa Tools', 'net_volume' => '50 mm', 'origin' => 'India',
                'description' => "A 50mm solid brass padlock with a hardened steel shackle and a five-pin cylinder - the size for a shutter, a gate or a store room.\n\nBrass does not rust, which is the reason it is still the default on anything left outdoors through a monsoon.",
                'highlights' => [
                    ['icon' => 'shield', 'title' => 'Hardened Shackle', 'subtitle' => 'Resists a bolt cutter'],
                    ['icon' => 'droplets', 'title' => 'Rust Proof Body', 'subtitle' => 'Solid brass, monsoon safe'],
                    ['icon' => 'check', 'title' => '3 Keys Included', 'subtitle' => 'Five-pin cylinder'],
                ],
                'features' => ['50mm solid brass body', 'Hardened steel shackle', '5-pin cylinder', 'Three keys supplied'],
            ],

            // ------------------------------------------------------------ Beverages
            [
                'code' => 'BV-001', 'title' => 'Mineral Water 20L', 'unit' => 'jar',
                'category' => 'Beverages', 'price' => 100, 'compare_at' => null, 'stock' => 200,
                'brand' => 'Himal Springs', 'net_volume' => '20 L', 'origin' => 'Nepal',
                'description' => "A 20-litre dispenser jar of treated mineral water - the standard household and office refill.\n\nEvery jar is washed, ozonised and sealed at the plant, and the seal is the thing to check before you accept a delivery.",
                'highlights' => [
                    ['icon' => 'droplets', 'title' => 'Multi-Stage Treated', 'subtitle' => 'RO, UV and ozone'],
                    ['icon' => 'shield', 'title' => 'Sealed at the Plant', 'subtitle' => 'Tamper-evident cap'],
                    ['icon' => 'check', 'title' => 'Returnable Jar', 'subtitle' => 'Swapped on refill'],
                ],
                'features' => ['20 litre dispenser jar', 'RO + UV + ozone treated', 'Food-grade PC jar', 'Returnable and reusable'],
            ],
            [
                'code' => 'BV-002', 'title' => 'Soft Drink 1.5L', 'unit' => 'btl',
                'category' => 'Beverages', 'price' => 180, 'compare_at' => null, 'stock' => 96,
                'brand' => 'Fizzo', 'net_volume' => '1.5 L', 'origin' => 'Nepal',
                'description' => "The 1.5-litre share bottle - a meal for four, or an afternoon for two. Resealable cap, so the second half is still drinkable the next day.",
                'highlights' => [
                    ['icon' => 'sparkles', 'title' => 'Shares Four Ways', 'subtitle' => '1.5 litre bottle'],
                    ['icon' => 'check', 'title' => 'Resealable Cap', 'subtitle' => 'Holds the fizz overnight'],
                    ['icon' => 'star', 'title' => 'Best Served Cold', 'subtitle' => 'Chill for 3 hours'],
                ],
                'features' => ['1.5 litre PET bottle', 'Carbonated soft drink', 'Resealable screw cap', 'Best before 6 months'],
            ],
            [
                'code' => 'BV-003', 'title' => 'Instant Coffee 200g', 'unit' => 'jar',
                'category' => 'Beverages', 'price' => 850, 'compare_at' => 950, 'stock' => 41,
                'brand' => 'Ilam Gold', 'net_volume' => '200 g', 'origin' => 'Nepal',
                'description' => "Freeze-dried granules rather than spray-dried powder, which is why it dissolves without a skin and does not taste burnt.\n\nA 200g jar is roughly a hundred cups. The glass jar keeps the aroma far better than a refill pouch.",
                'highlights' => [
                    ['icon' => 'award', 'title' => 'Freeze Dried', 'subtitle' => 'Keeps the aroma'],
                    ['icon' => 'zap', 'title' => 'Dissolves Instantly', 'subtitle' => 'Hot or cold water'],
                    ['icon' => 'check', 'title' => 'About 100 Cups', 'subtitle' => 'From one jar'],
                ],
                'features' => ['200g glass jar', 'Freeze-dried granules', '100% arabica blend', 'Airtight resealable lid'],
            ],
            [
                'code' => 'BV-004', 'title' => 'Orange Juice 1L', 'unit' => 'btl',
                'category' => 'Beverages', 'price' => 260, 'compare_at' => null, 'stock' => 4,
                'brand' => 'Fizzo', 'net_volume' => '1 L', 'origin' => 'Nepal',
                'description' => "Not-from-concentrate orange juice with no added sugar - which does mean it tastes of the fruit rather than of squash.\n\nKeep it cold and finish it within three days of opening.",
                'highlights' => [
                    ['icon' => 'leaf', 'title' => 'No Added Sugar', 'subtitle' => 'Fruit sugar only'],
                    ['icon' => 'sun', 'title' => 'Rich in Vitamin C', 'subtitle' => 'One glass covers a day'],
                    ['icon' => 'droplet', 'title' => 'Not From Concentrate', 'subtitle' => 'Pressed and pasteurised'],
                ],
                'features' => ['1 litre bottle', 'No added sugar or colour', 'Not from concentrate', 'Refrigerate after opening'],
            ],
            [
                'code' => 'BV-005', 'title' => 'Green Tea Bags (100)', 'unit' => 'box',
                'category' => 'Beverages', 'price' => 540, 'compare_at' => null, 'stock' => 37,
                'brand' => 'Ilam Gold', 'net_volume' => '100 bags', 'origin' => 'Nepal',
                'description' => "A hundred individually foil-wrapped green tea bags from Ilam. The foil is not packaging theatre - green tea loses its aroma to open air within days.\n\nBrew at just under boiling for two minutes; longer and it turns bitter.",
                'highlights' => [
                    ['icon' => 'leaf', 'title' => 'Ilam Grown', 'subtitle' => 'Single-origin hill garden'],
                    ['icon' => 'shield', 'title' => 'Foil Wrapped', 'subtitle' => 'Each bag sealed'],
                    ['icon' => 'heart', 'title' => 'Naturally Caffeine Light', 'subtitle' => 'Good through the day'],
                ],
                'features' => ['100 foil-wrapped bags', 'Single-origin Ilam green tea', 'Brew 2 minutes at 80C', 'Staple-free bags'],
            ],
            [
                'code' => 'BV-006', 'title' => 'Energy Drink 250ml', 'unit' => 'can',
                'category' => 'Beverages', 'price' => 150, 'compare_at' => null, 'stock' => 110,
                'brand' => 'Fizzo', 'net_volume' => '250 ml', 'origin' => 'Nepal',
                'description' => "A 250ml can with caffeine, taurine and B vitamins - the standard late-shift or long-drive can. Chill it; warm it is unpleasant.",
                'highlights' => [
                    ['icon' => 'zap', 'title' => 'Caffeine and Taurine', 'subtitle' => 'For a long shift'],
                    ['icon' => 'sun', 'title' => 'B-Vitamin Blend', 'subtitle' => 'B3, B6 and B12'],
                    ['icon' => 'check', 'title' => 'Single 250ml Can', 'subtitle' => 'One sitting'],
                ],
                'features' => ['250ml aluminium can', 'Caffeine 80mg', 'Taurine and B vitamins', 'Serve chilled'],
            ],
            [
                'code' => 'BV-007', 'title' => 'Milk 1L', 'unit' => 'pkt',
                'category' => 'Beverages', 'price' => 110, 'compare_at' => null, 'stock' => 85,
                'brand' => 'Himal Dairy', 'net_volume' => '1 L', 'origin' => 'Nepal',
                'description' => "Pasteurised full-cream milk in a one-litre pouch, delivered cold. It keeps two days in a fridge once opened and should not be left out of one at all.",
                'highlights' => [
                    ['icon' => 'droplet', 'title' => 'Full Cream', 'subtitle' => '4.5% milk fat'],
                    ['icon' => 'shield', 'title' => 'Pasteurised', 'subtitle' => 'Bottled cold at the dairy'],
                    ['icon' => 'check', 'title' => 'Delivered Chilled', 'subtitle' => 'Cold chain kept'],
                ],
                'features' => ['1 litre pouch', 'Pasteurised full-cream', '4.5% fat', 'Keep refrigerated'],
            ],

            // --------------------------------------------------------- Personal Care
            [
                'code' => 'PC-001', 'title' => 'Hand Wash 500ml', 'unit' => 'btl',
                'category' => 'Personal Care', 'price' => 320, 'compare_at' => 380, 'stock' => 64,
                'brand' => 'Everest Care', 'net_volume' => '500 ml', 'origin' => 'Nepal',
                'description' => "A 500ml pump of glycerine hand wash that cleans without stripping - the difference you notice by the end of a winter week, not the first day.\n\nThe pump meters roughly one gram a press, so a bottle lasts a family about two months.",
                'highlights' => [
                    ['icon' => 'droplets', 'title' => 'Glycerine Rich', 'subtitle' => 'Will not dry the skin'],
                    ['icon' => 'shield', 'title' => 'Kills 99.9% Germs', 'subtitle' => 'In a 20-second wash'],
                    ['icon' => 'check', 'title' => 'Metered Pump', 'subtitle' => 'About 2 months per bottle'],
                ],
                'features' => ['500ml pump bottle', 'Glycerine and aloe', 'pH balanced', 'Refill pouch available'],
            ],
            [
                'code' => 'PC-002', 'title' => 'Toothpaste 150g', 'unit' => 'pcs',
                'category' => 'Personal Care', 'price' => 150, 'compare_at' => null, 'stock' => 0,
                'brand' => 'Everest Care', 'net_volume' => '150 g', 'origin' => 'India',
                'description' => "Fluoride toothpaste in the family 150g tube. Nothing exotic - the thing that matters in a toothpaste is the fluoride level, and this one meets it.",
                'highlights' => [
                    ['icon' => 'shield', 'title' => '1450ppm Fluoride', 'subtitle' => 'Full cavity protection'],
                    ['icon' => 'sparkles', 'title' => 'Removes Stains', 'subtitle' => 'Gentle polishing agents'],
                    ['icon' => 'check', 'title' => 'Family Size', 'subtitle' => '150g tube'],
                ],
                'features' => ['150g tube', '1450ppm sodium fluoride', 'Mint flavour', 'Suitable from age 6'],
            ],
            [
                'code' => 'PC-003', 'title' => 'Face Wash 100ml', 'unit' => 'pcs',
                'category' => 'Personal Care', 'price' => 280, 'compare_at' => null, 'stock' => 58,
                'brand' => 'Everest Care', 'net_volume' => '100 ml', 'origin' => 'Nepal',
                'description' => "A soap-free gel cleanser for daily use. It lifts dust and city grime without the tight, squeaky feeling that means a wash has taken the skin's own oil with it.",
                'highlights' => [
                    ['icon' => 'leaf', 'title' => 'Soap Free', 'subtitle' => 'No tight, squeaky feel'],
                    ['icon' => 'droplet', 'title' => 'For Daily Use', 'subtitle' => 'Morning and night'],
                    ['icon' => 'heart', 'title' => 'All Skin Types', 'subtitle' => 'Including sensitive'],
                ],
                'features' => ['100ml tube', 'Soap-free gel formula', 'pH 5.5', 'No parabens'],
            ],
            [
                'code' => 'PC-004', 'title' => 'Shampoo 400ml', 'unit' => 'btl',
                'category' => 'Personal Care', 'price' => 590, 'compare_at' => 680, 'stock' => 47,
                'brand' => 'Everest Care', 'net_volume' => '400 ml', 'origin' => 'India',
                'description' => "A 400ml sulphate-free shampoo for hair that is washed often - it lathers less than a detergent shampoo, which is the point.\n\nSafe on coloured hair, and it rinses clean in hard water, where a richer formula tends to leave a film.",
                'highlights' => [
                    ['icon' => 'leaf', 'title' => 'Sulphate Free', 'subtitle' => 'Gentle on daily washing'],
                    ['icon' => 'droplets', 'title' => 'Rinses Clean', 'subtitle' => 'Even in hard water'],
                    ['icon' => 'sparkles', 'title' => 'Colour Safe', 'subtitle' => 'Will not strip dye'],
                ],
                'features' => ['400ml bottle', 'Sulphate and paraben free', 'Argan and coconut oil', 'Colour-treated safe'],
            ],
            [
                'code' => 'PC-005', 'title' => 'Bath Soap (4 pack)', 'unit' => 'pack',
                'category' => 'Personal Care', 'price' => 300, 'compare_at' => null, 'stock' => 72,
                'brand' => 'Everest Care', 'net_volume' => '4 x 100 g', 'origin' => 'Nepal',
                'description' => "Four 100g milled bars in a shrink pack. Milled soap is harder than cast, so a bar lasts noticeably longer in a wet soap dish.",
                'highlights' => [
                    ['icon' => 'award', 'title' => 'Triple Milled', 'subtitle' => 'Lasts longer, less mush'],
                    ['icon' => 'droplet', 'title' => 'Glycerine Enriched', 'subtitle' => 'Softer after a wash'],
                    ['icon' => 'check', 'title' => 'Four Bars', 'subtitle' => '100g each'],
                ],
                'features' => ['4 x 100g bars', 'Triple-milled', 'Glycerine enriched', 'Mild floral fragrance'],
            ],
            [
                'code' => 'PC-006', 'title' => 'Body Lotion 200ml', 'unit' => 'btl',
                'category' => 'Personal Care', 'price' => 450, 'compare_at' => null, 'stock' => 39,
                'brand' => 'Everest Care', 'net_volume' => '200 ml', 'origin' => 'India',
                'description' => "A non-greasy 200ml lotion that soaks in fast enough to dress straight after. Built for a dry Kathmandu winter rather than a humid one.",
                'highlights' => [
                    ['icon' => 'sun', 'title' => '24-Hour Moisture', 'subtitle' => 'One application a day'],
                    ['icon' => 'feather', 'title' => 'Absorbs Fast', 'subtitle' => 'No greasy film'],
                    ['icon' => 'leaf', 'title' => 'Shea and Almond', 'subtitle' => 'For very dry skin'],
                ],
                'features' => ['200ml pump bottle', 'Shea butter and almond oil', 'Non-greasy formula', 'Dermatologically tested'],
            ],
            [
                'code' => 'PC-007', 'title' => 'Deodorant 150ml', 'unit' => 'pcs',
                'category' => 'Personal Care', 'price' => 390, 'compare_at' => null, 'stock' => 1,
                'brand' => 'Everest Care', 'net_volume' => '150 ml', 'origin' => 'India',
                'description' => "A 150ml alcohol-free body spray that holds through a working day without the sting on freshly shaved skin.",
                'highlights' => [
                    ['icon' => 'zap', 'title' => '48-Hour Protection', 'subtitle' => 'Through a long day'],
                    ['icon' => 'leaf', 'title' => 'Alcohol Free', 'subtitle' => 'No sting after shaving'],
                    ['icon' => 'check', 'title' => 'No White Marks', 'subtitle' => 'Dries clear on clothes'],
                ],
                'features' => ['150ml aerosol', 'Alcohol-free formula', '48-hour odour protection', 'No stains on fabric'],
            ],
        ];
    }
}
