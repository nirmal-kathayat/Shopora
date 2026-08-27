<?php

namespace App\Repository;

use App\Models\Supplier;

class SupplierRepository
{
    private $supplier;
    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function getSupplier()
    {
        $supplier = $this->supplier;
        $supplier =    $supplier
            ->select('id', 'name', 'created_at')
            ->orderBy('name', 'asc')
            ->get();
        return $supplier;
    }
    public function getSuppliers()
    {
        return $this->supplier->get();
    }
    public function store(array $data)
    {
        $supplier = [
            'name' => $data['name'],
            'phone_number' => $data['phone_number'],
            'address' => $data['address'],
            'pan_number' => $data['pan_number']
        ];
        return $this->supplier->create($supplier);
    }
    public function find($id)
    {
        return $this->supplier->findOrFail($id);
    }
    public function update(array $data, int $id)
    {
        $supplier = [
            'name' => $data['name'],
            'phone_number' => $data['phone_number'],
            'address' => $data['address'],
            'pan_number' => $data['pan_number']
        ];
        return $this->supplier->where('id', $id)->update($supplier);
    }
    public function delete($id)
    {
        return $this->supplier->where('id', $id)->delete($id);
    }
}
