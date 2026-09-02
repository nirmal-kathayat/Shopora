<?php

namespace App\Repository;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerRepository
{
    private $query;
    public function __construct(Customer $query)
    {
        $this->query = $query;
    }
    public function getCustomers()
    {
        return Customer::query()
            ->select([
                'customers.id',
                'customers.name',
                'customers.email',
                'customers.address',
                'customers.ph_number',
                'customers.pan_number',
                'customers.created_at',
                // Whether they signed up on the storefront, without pulling the
                // password hash out of the database to find out.
                DB::raw('(customers.password IS NOT NULL) as is_registered'),
            ])
            ->orderBy('customers.id', 'desc');
    }

    public function storeCustomer(array $data)
    {
        return $this->query->create([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'ph_number' => $data['ph_number'] ?? null
        ]);
    }

    public function find($id)
    {
        return $this->query->findOrFail($id);
    }

    public function updateCustomer(array $data, int $id)
    {
        $query = [
            'name' => $data['name'],
            'address' => $data['address'],
            'ph_number' => $data['ph_number']
        ];
        return $this->query->where('id', $id)->update($query);
    }

    public function delete($id)
    {
        return $this->query->where('id', $id)->delete($id);
    }
}
