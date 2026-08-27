<?php

namespace App\Repository;

use App\Models\Product;

class ProductRepository
{
    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function getProducts()
    {
        return $this->product
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'products.supplier_id')
            ->select('products.id', 'products.name', 'products.qty', 'products.price', 'products.code', 'products.unit', 'products.image', 'products.description', 'categories.title as category_title', 'suppliers.name as supplier_name');
    }

    public function store(array $data)
    {
        $destination = 'assets/images/products/';

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $imageName = time() . '_' . $data['image']->getClientOriginalName();
            $data['image']->move(public_path($destination), $imageName);
            $data['image'] = $imageName;
        } else {
            $data['image'] = null;
        }

        return $this->product->create([
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'supplier_id' => $data['supplier_id'] ?? null,
            'qty' => $data['qty'],
            'price' => $data['price'],
            'code' => $data['code'] ?? null,
            'image' => $data['image'] ?? null,
            'unit' => $data['unit'],
            'first_entry' => $data['qty'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function find($id)
    {
        return $this->product->findOrFail($id);
    }

    public function updateProduct(array $data, int $id)
    {
        $product = $this->find($id);
        $destination = 'assets/images/products/';
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $imageName = time() . '_' . $data['image']->getClientOriginalName();
            $data['image']->move(public_path($destination), $imageName);

            if ($product->image && file_exists(public_path($destination . $product->image))) {
                unlink(public_path($destination . $product->image));
            }

            $data['image'] = $imageName;
        } else {
            unset($data['image']);
        }

        $productData = [
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'supplier_id' => $data['supplier_id'],
            'qty' => $data['qty'],
            'price' => $data['price'],
            'code' => $data['code'],
            'unit' => $data['unit'],
            'description' => $data['description']
        ];
        if (isset($data['image'])) {
            $productData['image'] = $data['image'];
        }

        return $this->product->where('id', $id)->update($productData);
    }

    public function delete($id)
    {
        return $this->product->where('id', $id)->delete($id);
    }

}
