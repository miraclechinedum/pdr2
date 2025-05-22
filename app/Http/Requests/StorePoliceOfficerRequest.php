<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePoliceOfficerRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('add police officer');
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users',
            'nin'          => 'nullable|string|max:20',
            'phone_number' => 'nullable|string|max:20',
            'lga_id'       => 'nullable|exists:lgas,id',
            'state_id'     => 'nullable|exists:states,id',
            'password'     => 'required|string|min:8|confirmed',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',

        ];
    }
}
