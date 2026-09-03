<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerAddressResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'recipient_name' => $this->recipient_name,
            'ph_number' => $this->ph_number,
            'city' => $this->city,
            'area' => $this->area,
            'street' => $this->street,
            'landmark' => $this->landmark,
            'is_default' => (bool) $this->is_default,
            // The same address as one line, so the storefront never has to
            // decide how to join the pieces.
            'single_line' => $this->single_line,
        ];
    }
}
