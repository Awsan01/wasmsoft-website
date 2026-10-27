<?php

namespace App\Content;

use App\Models\Media;

/**
 * A small item shown in a section of the home or about page (highlight,
 * testimonial, team member, milestone), managed by Admin\BlockController.
 */
interface ContentBlock
{
    /** Name of the item in the dashboard list. */
    public function blockLabel(): ?string;

    /** A second line in the dashboard list, if useful. */
    public function blockDetail(): ?string;

    /** Image shown next to the item in the dashboard list. */
    public function blockImage(): ?Media;

    public function isVisible(): bool;

    /**
     * Languages in which the item has its main text.
     *
     * @return list<string>
     */
    public function translatedLocales(): array;
}
