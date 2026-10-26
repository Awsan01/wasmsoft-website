<?php

namespace App\Policies;

use App\Auth\Permission;
use App\Models\Inquiry;
use App\Models\User;

/**
 * Inquiries contain personal data: only users who handle them may read or
 * update them, and only administrators may delete them.
 */
class InquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::HandleInquiries);
    }

    public function view(User $user, Inquiry $inquiry): bool
    {
        return $user->hasPermission(Permission::HandleInquiries);
    }

    public function update(User $user, Inquiry $inquiry): bool
    {
        return $user->hasPermission(Permission::HandleInquiries);
    }

    public function delete(User $user, Inquiry $inquiry): bool
    {
        return $user->hasPermission(Permission::DeleteInquiries);
    }
}
