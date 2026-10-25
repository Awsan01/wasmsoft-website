<?php

namespace App\Content;

/**
 * Content with a draft/published/archived workflow (see Models\Concerns\Publishable).
 */
interface PublishableContent
{
    public function applyStatus(PublicationStatus $status): void;

    public function isPublished(): bool;
}
