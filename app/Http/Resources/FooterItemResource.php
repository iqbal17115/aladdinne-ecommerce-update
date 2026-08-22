<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FooterItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = request()->header('accept-language') ?? 'en';
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $lang == 'ar' ? $this->ar_title : $this->title,
            'icon' => $this->icon,
            'url' => $this->url,
            'target' => $this->target,
        ];
    }
}
