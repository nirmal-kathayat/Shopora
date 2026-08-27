<?php

namespace App\Repository;

use App\Models\Sales;
use App\Models\SalesProduct;
use App\Models\InventoryStock;
use Illuminate\Support\Facades\DB;
use NepaliDate\Facades\NepaliDate;

class SalesRepository
{
    private $sales;
    private $salesProduct;

    public function __construct(Sales $sales, SalesProduct $salesProduct)
    {
        $this->sales = $sales;
        $this->salesProduct = $salesProduct;
    }

    public function storeSalesProduct(array $data)
    {
        DB::beginTransaction();

        try {
            $currentNepaliDate = NepaliDate::create(now())->toBS();

            $salesOrder = $this->sales->create([
                'order_by' => $data['order_by'],
                'nepali_date' => $currentNepaliDate,
                'customer_id' => $data['customer_id'] ?? null,
                'discount' => $data['discount'] ?? 0
            ]);
            // Calculate total amount
            $totalAmount = 0;
            foreach ($data['products'] as $product) {
                $totalAmount += $product['qty'] * $product['price_per_unit'];
            }

            // payment mode 
            foreach ($data['products'] as $product) {
                $this->salesProduct->create([
                    'sales_id' => $salesOrder->id,
                    'product_id' => $product['product_id'],
                    'qty' => $product['qty'],
                    'price_per_unit' => $product['price_per_unit'],
                    'payment_mode' => $product['payment_mode'] ?? '',
                    'discount' => $product['discount'] ?? 0,
                ]);

                $salesRecords = [
                    'inventory_item_id' => $product['product_id'],
                    'qty' => $product['qty'],
                    'purchase_inventory_id' => $salesOrder->id,
                    'type' => 'sales',
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // DB::table('store_records')->insert($salesRecords);

                // Store in inventory_stocks table
                InventoryStock::create([
                    'inventory_item_id' => $product['product_id'],
                    'sales_id' => $salesOrder->id,
                    'qty' => $product['qty'],
                    'remarks' => 'Sale',
                ]);
            }

            // Store in sales_payment_mode table (handle both regular and split payments)
            if (isset($data['split_payments']) && is_array($data['split_payments'])) {
                // Handle split payments - multiple records for different payment modes
                foreach ($data['split_payments'] as $splitPayment) {
                    if ($splitPayment['amount'] > 0) {
                        DB::table('sales_payment_mode')->insert([
                            'sales_id' => $salesOrder->id,
                            'payment_mode_id' => $splitPayment['payment_mode_id'],
                            'amount' => $splitPayment['amount'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            } else {
                // Handle regular single payment
                $paymentModeId = $data['products'][0]['payment_mode'] ?? null;
                
                // If payment_mode is not an ID, try to find it by title
                if (!is_numeric($paymentModeId)) {
                    $paymentModeId = DB::table('payment_modes')
                        ->where('payment_title', $paymentModeId)
                        ->value('id');
                }
                
                // If still not found, default to Fonepay or first available payment mode
                if (!$paymentModeId) {
                    $paymentModeId = DB::table('payment_modes')
                        ->where('payment_title', 'Fonepay')
                        ->value('id') ?? DB::table('payment_modes')->value('id');
                }
                    
                DB::table('sales_payment_mode')->insert([
                    'sales_id' => $salesOrder->id,
                    'payment_mode_id' => $paymentModeId,
                    'amount' => $totalAmount,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return $salesOrder;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
