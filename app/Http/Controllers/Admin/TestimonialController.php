<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentBlock;
use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

    protected function formComponent(): string
    {
        return 'testimonials/form';
    }

    protected function listQuery(Request $request): Builder
    {
        return Testimonial::query()->with('photo')->ordered();
    }

    protected function formProps(Model&ContentBlock $block): array
    {
        $testimonial = $block instanceof Testimonial ? $block->loadMissing('photo') : new Testimonial;

        return [
            'form' => [
                'quote' => $this->present->localized($testimonial->quote),
                'author_name' => (string) $testimonial->author_name,
                'company' => (string) $testimonial->company,
                'author_role' => $this->present->localized($testimonial->author_role),
                'has_consent' => $testimonial->has_consent,
                'is_visible' => $testimonial->is_visible,
                'photo' => $this->present->media($testimonial->photo),
            ],
        ];
    }
}
