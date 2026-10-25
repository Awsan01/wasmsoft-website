<?php

// Dashboard interface strings. Website content itself is stored in the database.

return [
    'meta_title' => ':site dashboard',

    'nav' => [
        'label' => 'Dashboard navigation',
        'open' => 'Open navigation',
        'close' => 'Close navigation',
        'new_items' => 'new',
        'overview' => 'Overview',
        'pages' => 'Pages',
        'services' => 'Services',
        'projects' => 'Projects',
        'posts' => 'News',
        'highlights' => 'Highlights',
        'testimonials' => 'Testimonials',
        'team' => 'Team',
        'milestones' => 'Milestones',
        'media' => 'Media library',
        'inquiries' => 'Inquiries',
        'navigation' => 'Navigation',
        'settings' => 'Settings',
        'users' => 'Users',
        'audit' => 'Audit log',
        'groups' => [
            'content' => 'Content',
            'library' => 'Library',
            'inbox' => 'Inbox',
            'site' => 'Website',
            'administration' => 'Administration',
        ],
    ],

    'topbar' => [
        'view_site' => 'View website',
        'account' => 'Account menu',
        'profile' => 'Your profile',
        'sign_out' => 'Sign out',
        'language' => 'Dashboard language',
    ],

    'auth' => [
        'sign_in' => 'Sign in',
        'sign_in_title' => 'Sign in to the dashboard',
        'sign_in_intro' => 'Use the email address and password of your dashboard account.',
        'email' => 'Email address',
        'password' => 'Password',
        'remember' => 'Keep me signed in on this device',
        'forgot' => 'Forgot your password?',
        'forgot_title' => 'Reset your password',
        'forgot_intro' => 'Enter the email address of your account and we will send you a link to choose a new password.',
        'send_link' => 'Email me a reset link',
        'link_sent' => 'If an active account uses that address, a password reset link is on its way. Check your inbox in a few minutes.',
        'back_to_sign_in' => 'Back to sign in',
        'reset_title' => 'Choose a new password',
        'reset_intro' => 'Choose a strong password that you do not use anywhere else.',
        'new_password' => 'New password',
        'confirm_password' => 'Confirm new password',
        'password_rules' => 'At least 12 characters, including upper- and lower-case letters and a number.',
        'reset_submit' => 'Save new password',
        'signed_out' => 'You have been signed out.',
        'deactivated' => 'This account has been deactivated. Please contact an administrator.',
    ],

    'mail' => [
        'greeting' => 'Hello :name,',
        'expiry' => 'For your security, this link expires in :minutes minutes.',
        'reset' => [
            'subject' => 'Reset your :site dashboard password',
            'intro' => 'We received a request to reset the password of your :site dashboard account.',
            'action' => 'Choose a new password',
            'ignore' => 'If you did not ask for this, you can ignore this email: your password will not change.',
        ],
        'invitation' => [
            'subject' => 'Your :site dashboard account',
            'intro' => 'An administrator created a :site dashboard account for you. Choose a password to start using it.',
            'action' => 'Choose your password',
            'ignore' => 'If you were not expecting this invitation, you can ignore this email.',
        ],
    ],

    'roles' => [
        'administrator' => [
            'label' => 'Administrator',
            'description' => 'Full access, including users, roles and website settings.',
        ],
        'editor' => [
            'label' => 'Editor',
            'description' => 'Manages all website content, media, navigation and inquiries.',
        ],
        'author' => [
            'label' => 'Author',
            'description' => 'Writes news articles and uploads media. Editors review and publish the articles.',
        ],
        'viewer' => [
            'label' => 'Viewer',
            'description' => 'Read-only access to website content in the dashboard.',
        ],
    ],

    'overview' => [
        'welcome' => 'Welcome, :name',
        'intro' => 'Manage the website content in every language from one place.',
        'your_role' => 'Your role',
    ],

    'form' => [
        'errors_title' => 'Please correct the highlighted fields',
        'errors_count' => '{1} One field needs your attention.|[2,*] :count fields need your attention.',
    ],

    'audit' => [
        'events' => [
            'auth_login' => 'Signed in',
            'auth_login_failed' => 'Failed sign-in attempt',
            'auth_lockout' => 'Sign-in locked after repeated failures',
            'auth_logout' => 'Signed out',
            'auth_password_reset' => 'Reset password',
            'auth_password_changed' => 'Changed password',
            'user_created' => 'Created user',
            'user_updated' => 'Updated user',
            'user_role_changed' => 'Changed user role',
            'user_activated' => 'Activated user',
            'user_deactivated' => 'Deactivated user',
            'user_deleted' => 'Deleted user',
            'user_invitation_sent' => 'Sent invitation',
            'settings_updated' => 'Updated settings',
            'navigation_updated' => 'Updated navigation',
            'content_created' => 'Created content',
            'content_updated' => 'Updated content',
            'content_published' => 'Published content',
            'content_unpublished' => 'Unpublished content',
            'content_archived' => 'Archived content',
            'content_deleted' => 'Deleted content',
            'media_uploaded' => 'Uploaded media',
            'media_updated' => 'Updated media',
            'media_deleted' => 'Deleted media',
            'inquiry_status_changed' => 'Changed inquiry status',
            'inquiry_deleted' => 'Deleted inquiry',
        ],
    ],
];
