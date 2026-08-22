<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use App\Models\Page;

class LegalPageController extends Controller
{
    /**
     * Get a legal page by its slug.
     *
     * @param  string  $slug  The slug of the legal page
     */
    public function index($slug)
    {
        $page = Page::where('slug', $slug)->first();
        $isAr = (request()->header('accept-language') ?? 'en') !== 'en';

        return $this->json('Legal Page', [
            'content' => [
                'title' => ($isAr && !empty($page?->title_ar)) ? $page->title_ar : $page?->title,
                'description' => ($isAr && !empty($page?->description_ar)) ? $page->description_ar : $page?->description,
            ],
        ]);
    }

    /**
     * get contact us page.
     */
    public function contactUs()
    {
        $contact = ContactUs::first();

        return $this->json('Contact Us', [
            'phone'     => $contact?->phone,
            'email'     => $contact?->email,
            'whatsapp'  => $contact?->whatsapp,
            'messenger' => $contact?->messenger,
            'address'   => $contact?->address,
        ]);
    }
}
