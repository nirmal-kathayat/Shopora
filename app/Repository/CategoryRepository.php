<?php

namespace App\Repository;

use App\Models\Category;

class CategoryRepository
{
    private $query;

    public function __construct(Category $query)
    {
        $this->query = $query;
    }

    public function getCategory()
    {
        return $this->query->select('id', 'title')->get();
    }

    public function store(array $data)
    {
        return $this->query->create([
            'title' => $data['title']
        ]);
    }
}
