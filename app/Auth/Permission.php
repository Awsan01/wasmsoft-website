<?php

namespace App\Auth;

/**
 * Dashboard capabilities. Each case is registered as a gate ability (its
 * value), so routes can use `can:content.manage` and code can call
 * Gate::authorize(Permission::ManageContent).
 *
 * Model policies combine these with ownership and state rules (e.g. an
 * author may only edit their own unpublished posts).
 */
enum Permission: string
{
    case ViewDashboard = 'dashboard.view';
    case ViewContent = 'content.view';
    case ManageContent = 'content.manage';
    case WritePosts = 'posts.write';
    case PublishPosts = 'posts.publish';
    case UploadMedia = 'media.upload';
    case ManageMedia = 'media.manage';
    case ManageNavigation = 'navigation.manage';
    case HandleInquiries = 'inquiries.handle';
    case DeleteInquiries = 'inquiries.delete';
    case ManageUsers = 'users.manage';
    case ManageSettings = 'settings.manage';
    case ViewAuditLog = 'audit.view';
}
