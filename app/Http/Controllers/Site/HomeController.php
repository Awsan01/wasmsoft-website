<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Seo\SeoMeta;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('site.home', [
            'seo' => new SeoMeta(title: config('app.name'), canonical: route('site.home')),
        ]);
    }
}
