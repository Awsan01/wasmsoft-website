<?php

namespace App\Audit;

/**
 * Security-relevant and administrative events recorded in the audit log.
 */
enum AuditEvent: string
{
    case Login = 'auth.login';
    case LoginFailed = 'auth.login_failed';
    case Lockout = 'auth.lockout';
    case Logout = 'auth.logout';
    case PasswordReset = 'auth.password_reset';
    case PasswordChanged = 'auth.password_changed';

    case UserCreated = 'user.created';
    case UserUpdated = 'user.updated';
    case UserRoleChanged = 'user.role_changed';
    case UserActivated = 'user.activated';
    case UserDeactivated = 'user.deactivated';
    case UserDeleted = 'user.deleted';
    case InvitationSent = 'user.invitation_sent';
    case InvitationAccepted = 'user.invitation_accepted';

    case SettingsUpdated = 'settings.updated';
    case NavigationUpdated = 'navigation.updated';

    case ContentCreated = 'content.created';
    case ContentUpdated = 'content.updated';
    case ContentPublished = 'content.published';
    case ContentUnpublished = 'content.unpublished';
    case ContentArchived = 'content.archived';
    case ContentDeleted = 'content.deleted';

    case MediaUploaded = 'media.uploaded';
    case MediaUpdated = 'media.updated';
    case MediaDeleted = 'media.deleted';

    case InquiryStatusChanged = 'inquiry.status_changed';
    case InquiryDeleted = 'inquiry.deleted';

    public function label(): string
    {
        return __('admin.audit.events.'.str_replace('.', '_', $this->value));
    }

    /**
     * Authentication and access-control events, highlighted in the log.
     */
    public function isSecurityEvent(): bool
    {
        return str_starts_with($this->value, 'auth.') || str_starts_with($this->value, 'user.') || $this === self::SettingsUpdated;
    }
}
