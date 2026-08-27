<?php

namespace App\Repository;

use App\Models\Customer;

class CustomerRepository
{
    private $query;
    public function __construct(Customer $query)
    {
        $this->query = $query;
    }
    public function getCustomers()
    {
        return Customer::query()->orderBy('customers.id', 'desc');
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
