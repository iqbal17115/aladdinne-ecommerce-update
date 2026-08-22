<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('accept-language') ?? 'en';

        return [
            'id' => $this->id,
            'icon' => $this->icon_url,
            'title' => $lang == 'ar' && $this->ar_title ? $this->ar_title : $this->title,
            'description' => $lang == 'ar' && $this->ar_description ? $this->ar_description : $this->description,
        ];
    }
}
