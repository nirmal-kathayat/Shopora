<?php

namespace App\Http\Requests\Api;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * The unregistered walk-in row this signup would take over, if any.
     */
    private ?Customer $claimable = null;
    private bool $claimableResolved = false;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ph_number' => Customer::normalizePhone($this->input('ph_number')),
            'email' => is_string($this->input('email')) ? trim(strtolower($this->input('email'))) : $this->input('email'),
        ]);
    }

    public function rules(): array
    {
        $claimableId = $this->claimableCustomer()?->id;

        return [
            'name' => ['required', 'string', 'max:191'],
            'email' => [
                'required', 'string', 'email', 'max:191',
                Rule::unique('customers', 'email')->ignore($claimableId),
            ],
            'ph_number' => [
                'required', 'string', 'digits_between:7,10',
                // A phone already tied to a registered account is taken. One
                // that only exists as a counter record is not - that signup
                // claims the row, keeping the customer's purchase history.
                Rule::unique('customers', 'ph_number')->ignore($claimableId),
            ],
            'address' => ['nullable', 'string', 'max:191'],
            // bcrypt silently ignores anything past 72 bytes, so a longer
            // password would not be the password the customer thinks it is.
            'password' => ['required', 'confirmed', 'max:72', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'ph_number.unique' => 'This phone number already has an account. Try logging in instead.',
            'email.unique' => 'This email already has an account. Try logging in instead.',
        ];
    }

    /**
     * @return Customer|null the walk-in record to upgrade, or null for a fresh signup
     */
    public function claimableCustomer(): ?Customer
    {
        if (! $this->claimableResolved) {
            $existing = Customer::where('ph_number', $this->input('ph_number'))->first();

            $this->claimable = $existing && ! $existing->isRegistered() ? $existing : null;
            $this->claimableResolved = true;
        }

        return $this->claimable;
    }
}
