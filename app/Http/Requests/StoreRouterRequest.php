<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRouterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:routers,ip_address',
            'snmp_community' => 'required|string|max:255',
            'snmp_version' => 'required|in:1,2c,3',
            'snmp_port' => 'nullable|integer|min:1|max:65535',
            'snmp_v3_username' => 'required_if:snmp_version,3|nullable|string|max:255',
            'snmp_v3_auth_protocol' => 'nullable|in:MD5,SHA',
            'snmp_v3_auth_password' => 'nullable|string|max:255',
            'snmp_v3_priv_protocol' => 'nullable|in:DES,AES',
            'snmp_v3_priv_password' => 'nullable|string|max:255',
            'snmp_v3_security_level' => 'nullable|in:noAuthNoPriv,authNoPriv,authPriv',
            'category_id' => 'required|exists:router_categories,id',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
