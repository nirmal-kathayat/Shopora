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
        return $this->baseListing()->orderBy('customers.id', 'desc');
    }

    /**
     * The customer list, filtered and sorted for the table.
     *
     * The address count is a subquery rather than a join, so a customer with
     * three saved addresses is still one row.
     */
    public function getCustomersForListing(array $options = [])
    {
        $query = $this->baseListing();

        // Header-row filters: one box per column, each its own LIKE.
        foreach (['name', 'email', 'ph_number'] as $column) {
            $value = trim((string) ($options[$column] ?? ''));
            if ($value !== '') {
                $query->where('customers.' . $column, 'like', '%' . $value . '%');
            }
        }

        // One box over the whole row - a phone number is how most people are
        // looked up at the counter, a name or an email everywhere else.
        $search = trim((string) ($options['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where('customers.name', 'like', $like)
                    ->orWhere('customers.email', 'like', $like)
                    ->orWhere('customers.ph_number', 'like', $like)
                    ->orWhere('customers.address', 'like', $like)
                    ->orWhere('customers.pan_number', 'like', $like);
            });
        }

        // Storefront sign-ups only, or counter walk-ins only.
        $account = trim((string) ($options['is_registered'] ?? ''));
        if ($account === '1') {
            $query->whereNotNull('customers.password');
        } elseif ($account === '0') {
            $query->whereNull('customers.password');
        }

        return $this->sortListing($query, $options['sort_field'] ?? null, $options['sort_direction'] ?? null);
    }

    /** The select every customer listing shares. */
    private function baseListing()
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
            // A subquery, so the customer row is not multiplied by addresses.
            ->selectSub(
                DB::table('customer_addresses')
                    ->selectRaw('count(*)')
                    ->whereColumn('customer_addresses.customer_id', 'customers.id'),
                'address_count'
            );
    }

    /**
     * Order the listing. By column name only - the field arrives in a query
     * string, and a column name is not something to take on trust.
     */
    private function sortListing($query, $field, $direction)
    {
        $sortable = [
            'name' => 'customers.name',
            'email' => 'customers.email',
            'ph_number' => 'customers.ph_number',
            'address' => 'customers.address',
            'pan_number' => 'customers.pan_number',
            'created_at' => 'customers.created_at',
            'is_registered' => 'is_registered',
            'address_count' => 'address_count',
        ];

        $column = $sortable[$field] ?? null;
        if (! $column) {
            return $query->orderBy('customers.id', 'desc');
        }

        return $query->orderBy($column, strtolower((string) $direction) === 'asc' ? 'asc' : 'desc');
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
