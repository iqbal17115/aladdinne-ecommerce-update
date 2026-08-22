<?php

namespace App\Repositories;

use App\Http\Requests\BlogRequest;
use App\Models\Blog;
use App\Models\Tag;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class BlogRepository extends Repository
{
    public static function model()
    {
        return Blog::class;
    }

    public static function storeByRequest(BlogRequest $request): Blog
    {

        $description = ProductRepository::sanitizeUnicode($request->description);
        $description = mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8');
        $description = Purifier::clean($description);

        $title = ProductRepository::sanitizeUnicode($request->title);

        $descriptionAr = null;
        if ($request->filled('description_ar')) {
            $descriptionAr = ProductRepository::sanitizeUnicode($request->description_ar);
            $descriptionAr = mb_convert_encoding($descriptionAr, 'HTML-ENTITIES', 'UTF-8');
            $descriptionAr = Purifier::clean($descriptionAr);
        }

        $blog = self::create([
            'user_id' => auth()->id(),
            'title' => $title,
            'title_ar' => $request->title_ar,
            'category_id' => $request->category,
            'description' => $description,
            'description_ar' => $descriptionAr,
            'blog_thumbnail' => $request->thumbnail,
        ]);

        foreach ($request->tags ?? [] as $tag) {
            $tag = Tag::firstOrCreate([
                'name' => $tag,
                'slug' => Str::slug($tag),
            ]);

            $blog->tags()->attach($tag->id);
        }

        return $blog;
    }

    public static function updateByRequest(BlogRequest $request, Blog $blog): Blog
    {

        $description = ProductRepository::sanitizeUnicode($request->description);
        $description = mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8');

        $title = ProductRepository::sanitizeUnicode($request->title);

        $descriptionAr = $blog->description_ar;
        if ($request->filled('description_ar')) {
            $descriptionAr = ProductRepository::sanitizeUnicode($request->description_ar);
            $descriptionAr = mb_convert_encoding($descriptionAr, 'HTML-ENTITIES', 'UTF-8');
            $descriptionAr = Purifier::clean($descriptionAr);
        }

        $blog->update([
            'title' => $title,
            'title_ar' => $request->title_ar ?? $blog->title_ar,
            'category_id' => $request->category,
            'description' => $description,
            'description_ar' => $descriptionAr,
            'blog_thumbnail' => $request->thumbnail,
        ]);

        $blog->tags()->detach();

        foreach ($request->tags ?? [] as $tag) {
            $tag = Tag::firstOrCreate([
                'name' => $tag,
                'slug' => Str::slug($tag),
            ]);

            $blog->tags()->attach($tag->id);
        }

        return $blog;
    }

    private static function thumbnailUpdateOrCreate($media, $request)
    {
        $thumbnail = $media;
        if ($request->hasFile('thumbnail') && $media) {
            $thumbnail = MediaRepository::updateByRequest($request->thumbnail, 'blogs', 'image', $media);

            return $thumbnail;
        }

        if ($request->hasFile('thumbnail') && ! $media) {
            $thumbnail = MediaRepository::storeByRequest($request->thumbnail, 'blogs', 'image');
        }

        return $thumbnail;
    }
}
