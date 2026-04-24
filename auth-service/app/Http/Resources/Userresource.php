<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'avatar'            => $this->avatar,
            'status'            => $this->status,
            'email_verified_at' => $this->email_verified_at,

            // Only included when the relation was eager-loaded
            'roles'       => $this->whenLoaded('roles', fn () => $this->getRoleNames()),
            'permissions' => $this->whenLoaded('permissions', fn () => $this->getAllPermissions()->pluck('name')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
