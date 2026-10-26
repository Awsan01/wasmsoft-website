<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * @extends BlockController<Testimonial>
 */
class TestimonialController extends BlockController
{
    public function store(TestimonialRequest $request): RedirectResponse
    {
        return $this->persist(new Testimonial, $request->testimonialAttributes(), __('admin.content.created'));
    }

    public function update(TestimonialRequest $request, string $block): RedirectResponse
    {
        return $this->persist($this->find($block), $request->testimonialAttributes(), __('admin.content.saved'));
    }

    protected function model(): string
    {
        return Testimonial::class;
    }

    protected function routes(): string
    {
        return 'admin.testimonials';
    }

    protected function texts(): string
    {
        return 'admin.testimonials';
    }

    protected function formView(): string
    {
        return 'admin.testimonials.form';
    }

    protected function listQuery(Request $request): Builder
    {
        return Testimonial::query()->with('photo')->ordered();
    }
}
