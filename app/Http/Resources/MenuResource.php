<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang =request()->header('accept-language') ?? 'en';

        return [
            'id' => $this->id,
            'name' => $lang == 'ar' ? $this->ar_name : $this->name,
            'title' => $this->title,
            'url' => $this->url,
            'target' => $this->target,
            'is_external' => (bool) $this->is_external,
        ];
    }
}
