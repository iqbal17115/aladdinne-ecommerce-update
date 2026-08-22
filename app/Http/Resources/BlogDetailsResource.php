<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $daysSinceCreated = $this->created_at->diffInDays(now());

        $isAr = (request()->header('accept-language') ?? 'en') !== 'en';

        return [
            'id' => $this->id,
            'title' => ($isAr && !empty($this->title_ar)) ? $this->title_ar : $this->title,
            'slug' => $this->slug,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'post_by' => [
                'name' => $this->user?->fullName ?? 'Admin',
                'profile_photo' => $this->user?->thumbnail ?? asset('default/default.jpg'),
            ],
            'thumbnail' => $this->thumbnail,
            'total_views' => $this->views->count(),
            'description' => ($isAr && !empty($this->description_ar)) ? $this->description_ar : $this->description,
            'created_at' => $this->created_at->format('d M, Y'),
            'is_new' => $daysSinceCreated < 5 ? true : false,
            'tags' => TagResource::collection($this->tags),
        ];
    }
}
