<!--
  Original project brief supplied by Wasmsoft on 2026-10-25.
  Kept verbatim as the source of truth for requirements; implementation
  decisions and deviations are recorded in docs/adr/.
-->

# Wasmsoft — Complete Website Design and Development Specification

## 1. Project Overview

Build a modern, professional, production-ready website for **Wasmsoft**, a technology company.

The website must have a polished, original visual identity inspired by the provided Wasmsoft logo and its purple-and-white color palette. It must include a public-facing website and a complete administrative dashboard for managing website content.

The project must be developed using strong software engineering principles, secure coding practices, clean architecture, reusable components, and maintainable code.

The objective is not merely to create an attractive UI. Build a scalable, functional, secure, and accessible application that can evolve as the company grows.

### Core requirements

- Original, professional Wasmsoft brand identity.
- Responsive public website.
- Fully functional administrative dashboard.
- Arabic, English, and Turkish language support.
- Complete right-to-left (RTL) support for Arabic.
- Reusable UI components and centralized design tokens.
- Secure authentication and authorization.
- Database-backed content management.
- Maintainable, testable, production-oriented architecture.
- No unnecessary duplication, mock functionality presented as real, or hard-coded business data.

## 2. Brand Identity and Design System

Use the provided Wasmsoft logo as the primary reference for the website's visual identity.

Preserve the original logo, proportions, geometric symbol, and appearance. Do not recreate it with approximate shapes, distort it, or introduce unrelated brand assets.

The goal is to establish a recognizable Wasmsoft visual identity while creating an original website rather than copying another company's design.

### Color palette

Use these colors as centralized design tokens.

| Token | Color | Intended use |
|---|---|---|
| Primary | `#481467` | Brand identity, prominent sections, navigation accents |
| Primary hover | `#38104F` | Hover and pressed states |
| Secondary | `#6D28A0` | Links, interactive elements, secondary actions |
| Light purple | `#F5F0FA` | Section backgrounds and subtle highlights |
| White | `#FFFFFF` | Main backgrounds and cards |
| Dark text | `#21152B` | Headings and body text |
| Border | `#E8E1ED` | Borders, dividers, and separators |
| Muted text | `#6F6578` | Secondary information and descriptions |
| Success | Accessible green | Successful actions and completed states |
| Warning | Accessible amber | Warnings and pending states |
| Error | Accessible red | Errors, destructive actions, and validation |

The primary purple is based on the supplied logo's background. Complementary colors are design recommendations and should be refined through contrast testing.

### Visual direction

Create a premium, contemporary technology-company aesthetic characterized by:

- Strong typography and clear visual hierarchy.
- Generous whitespace and balanced compositions.
- Consistent spacing, border radii, and subtle shadows.
- Clean cards, structured grids, and carefully aligned layouts.
- Restrained purple gradients for selected hero elements and decorative details.
- Subtle geometric motifs inspired by the angular character of the Wasmsoft logo.
- Smooth, restrained transitions and meaningful hover states.
- Clear calls to action and readable content.
- Consistent visual treatment across all public pages and administrative screens.

Avoid excessive gradients, oversized decorative elements, unnecessary animations, inconsistent shadows, and excessive use of dark backgrounds.

The public website should primarily use white and light lavender, with deep purple used strategically for brand emphasis. The dashboard should prioritize readability, efficient workflows, and information density.

### Design tokens and components

Centralize design decisions using CSS variables, theme configuration, or the equivalent supported by the selected stack.

Define reusable tokens for:

- Colors and semantic states.
- Typography and font weights.
- Spacing and layout.
- Border radii and borders.
- Shadows and elevation.
- Breakpoints.
- Transitions and animation durations.
- Focus indicators.
- Component dimensions.

Do not scatter hard-coded colors or arbitrary spacing values throughout the application.

Create reusable components for buttons, inputs, cards, navigation, dialogs, dropdowns, alerts, badges, tables, pagination, empty states, loading states, and confirmation dialogs.

## 3. Website Structure

Build the following public-facing pages.

### Home

Include:

- Professional navigation and language selector.
- Hero section communicating Wasmsoft's value proposition.
- Clear primary and secondary calls to action.
- Introduction to the company and its services.
- Services or solutions overview.
- Featured projects or portfolio items.
- Key differentiators and business benefits.
- Relevant metrics or impact indicators, when real data is available.
- Testimonials or client references, when verified content is available.
- Contact call to action.
- Comprehensive footer with navigation and contact information.

The hero should be distinctive and memorable without sacrificing readability or loading performance.

Do not invent clients, testimonials, achievements, statistics, or certifications. If company information has not been supplied, use clearly identified editable placeholder content in development.

### About Us

Include:

- Company introduction.
- Mission and vision.
- Values and working principles.
- Team information, if provided.
- Company history or milestones, if available.

### Services / Solutions

Present each service with:

- Title and description.
- Relevant benefits.
- Appropriate visual treatment.
- Optional detail page.
- A clear inquiry or contact action.

Services must be manageable through the dashboard.

### Projects / Portfolio

Include:

- Project listing and detail pages.
- Project images or media.
- Descriptions, categories, and technologies where relevant.
- Search or category filtering when useful.
- Responsive project cards.
- Optional featured-project functionality.

### News / Blog

Include:

- News listing.
- Individual article pages.
- Featured images.
- Publication dates and authors, where applicable.
- Categories and tags.
- Pagination.
- SEO-friendly URLs.
- Draft and published states managed through the dashboard.

### Contact

Include:

- Contact information.
- Contact form with server-side validation.
- Clear success and error messages.
- Spam protection appropriate to the application.
- Accessible form labels and keyboard navigation.
- Configurable social links and business contact details.

Only display contact details and integrations that have been supplied or configured.

### Shared layout

All public pages must share a consistent header, footer, navigation system, typography, design tokens, and language-selection behavior.

## 4. Three-Language Architecture: Arabic, English, and Turkish

Support these locales:

- Arabic: `ar`
- English: `en`
- Turkish: `tr`

### Localization requirements

- All public-facing content must be available in the supported languages.
- Dashboard navigation, validation messages, buttons, notifications, forms, and status labels must also be translated.
- Use a centralized translation system rather than scattering literal strings across templates.
- Avoid mixing languages within the same interface.
- Preserve the selected locale during navigation, pagination, filtering, and form submissions.
- Support translated page titles, descriptions, slugs where appropriate, and SEO metadata.
- Provide a clear language selector.
- Use the correct language-specific font and typography where necessary.
- Avoid hard-coded language-dependent layout assumptions.

### Arabic RTL requirements

Arabic must use a complete right-to-left layout, not merely right-aligned text.

Implement the correct document language and direction attributes, such as `lang="ar"` and `dir="rtl"`.

Use direction-aware CSS and logical properties, including `margin-inline`, `padding-inline`, `inset-inline`, and `text-align: start`, where appropriate.

Verify navigation, sidebars, dropdowns, breadcrumbs, tables, pagination, charts, forms, dialogs, icons, and responsive layouts in Arabic.

English and Turkish must use left-to-right layouts.

Switch the document direction and language consistently when changing locales.

Ensure numbers, dates, currency, and mixed Arabic-English text are displayed correctly according to the relevant locale and business requirements.

### Translation data model

Store translatable website content in a structured, maintainable format.

Choose a translation strategy appropriate to the selected framework and database. Keep fixed interface translations separate from database-backed content translations.

Ensure that missing translations have a defined fallback policy. Do not silently display a mixture of unrelated languages.

## 5. Administrative Dashboard

Build a protected dashboard that allows authorized staff to manage the public website without directly editing source code.

### Dashboard layout

Include:

- Responsive sidebar navigation.
- Top navigation bar.
- User profile menu.
- Language selector.
- Breadcrumbs.
- Main content area.
- Notifications and confirmation dialogs.
- Consistent loading, empty, success, and error states.

The dashboard must follow the Wasmsoft design system while prioritizing usability over decorative effects.

### Dashboard modules

**A. Dashboard overview**

- Summary of published content.
- Recent activity.
- Recent articles and projects.
- Relevant content-management metrics.
- Useful shortcuts to common actions.

Use real database-backed metrics. Do not display fabricated statistics.

**B. Page management**

- Create, edit, preview, and publish supported website pages.
- Manage page titles, content, metadata, and localized versions.
- Support drafts and publication status.
- Handle missing translations explicitly.

**C. Services management**

- Create, edit, reorder, publish, and archive services.
- Manage translated titles and descriptions.
- Manage service images and relevant metadata.

**D. Projects management**

- Create and edit projects.
- Manage project images and categories.
- Manage localized descriptions.
- Feature or unfeature projects.
- Control publication status.

**E. News and blog management**

- Create, edit, preview, publish, unpublish, and archive articles.
- Manage categories, tags, and featured images.
- Support drafts and publication dates.
- Manage translated content.
- Validate slugs and prevent conflicting URLs.

**F. Media library**

- Upload and manage images and supported documents.
- Validate file types, sizes, and content where appropriate.
- Generate safe filenames.
- Restrict upload access to authorized users.
- Store files using the framework's storage abstraction.
- Prevent executable uploads and unsafe file access.
- Support alternative text for accessibility.

**G. Navigation management**

- Manage navigation items and footer links.
- Configure ordering and visibility.
- Prevent invalid or unauthorized destinations where appropriate.

**H. Contact inquiries**

- View and manage submitted inquiries.
- Search and filter messages.
- Track inquiry status.
- Restrict access to personal information.
- Prevent unauthorized disclosure and mass data exposure.

**I. User and role management**

Support role-based access control.

Suggested roles:

- **Administrator:** Full administrative access.
- **Editor:** Manage website content and media.
- **Author:** Create and manage permitted content.
- **Viewer:** Read-only access to authorized dashboard information.

Adapt roles to actual business needs rather than creating unnecessary complexity.

Only administrators should be able to grant privileged roles or change security-sensitive settings.

**J. Website settings**

Manage authorized settings such as:

- Company information.
- Logo and favicon.
- Social media links.
- Contact details.
- Default locale and supported locales.
- SEO defaults.
- Footer information.
- Other appropriate site-wide settings.

Do not expose application secrets, environment variables, database credentials, or sensitive server configuration through the dashboard.

## 6. Software Engineering and Architecture

Use strong engineering fundamentals from the beginning.

Before implementation, inspect the existing repository, identify the framework and versions, understand its conventions, review dependencies, and identify existing components that can be reused.

Do not replace the existing technology stack or rewrite the application unnecessarily.

If starting a new project, choose a coherent, well-supported stack based on the requirements. If integrating into an existing project, follow the project's established architecture unless there is a documented reason to change it.

### Architecture principles

Apply:

- Separation of concerns.
- Single responsibility.
- Explicit dependencies.
- Reusable components and services.
- Clear boundaries between presentation, business logic, and data access.
- Consistent validation and error handling.
- Dependency injection where appropriate.
- Database transactions for operations that must succeed or fail together.
- Centralized configuration.
- Meaningful naming and consistent conventions.
- Small, focused functions and modules.
- Appropriate abstraction without unnecessary complexity.

Use design patterns only when they solve a genuine problem.

Avoid massive controllers, duplicated business logic, overly generic abstractions, unnecessary inheritance, and deeply coupled components.

### Backend organization

Keep responsibilities clear between:

- HTTP routing and middleware.
- Controllers or request handlers.
- Request validation.
- Business logic and application services.
- Data access and models.
- Authorization policies.
- Localization.
- File storage.
- Notifications and background jobs, where needed.

Use the framework's native conventions where possible.

Do not move every operation into a service or repository merely to follow a pattern. Introduce abstractions when they improve testability, maintainability, or complexity management.

### Database design

Design normalized, consistent database tables with appropriate relationships, constraints, indexes, and timestamps.

For localized content, choose a maintainable translation structure that avoids duplicated business records.

Use migrations to manage schema changes.

Consider pagination, query efficiency, N+1 queries, search performance, and data integrity.

Do not execute destructive production migrations without an explicit deployment plan and verified backup.

### Frontend organization

Organize the interface into clear, reusable components and layouts.

Separate:

- Shared visual components.
- Page-specific components.
- Dashboard components.
- Form validation.
- Data fetching and state management.
- Localization.
- API interactions.
- Design tokens.

Avoid unnecessary global state, oversized components, duplicated markup, and direct DOM manipulation where the framework provides a better approach.

## 7. Security Requirements

Treat security as a fundamental requirement, not a later enhancement.

Implement and verify the following according to the selected framework and architecture:

- Secure authentication using established framework capabilities.
- Server-side authorization for every protected operation.
- Role-based permissions and least privilege.
- CSRF protection where applicable.
- Validation of all user-controlled input on the server.
- Context-appropriate output escaping to mitigate XSS.
- Parameterized queries or ORM protections against SQL injection.
- Secure session configuration.
- Rate limiting for login, contact forms, and sensitive endpoints.
- Secure password hashing.
- Protection against mass assignment.
- Safe file uploads and downloads.
- Appropriate CORS configuration when applicable.
- Secure cookies and HTTPS in production.
- No secrets committed to version control.
- No credentials embedded in frontend code.
- No sensitive information in logs or error responses.
- Appropriate audit logging for security-sensitive administrative actions.
- Dependency vulnerability checks.
- A defined policy for backups and recovery.

Protect against insecure direct object references and unauthorized access to content, media, inquiries, and administrative resources.

Do not rely on hiding buttons or frontend routes as an authorization mechanism. Enforce permissions on the server.

Use secure defaults and fail closed when authorization or validation cannot be established.

## 8. Accessibility and User Experience

Aim for WCAG 2.2 AA accessibility.

Implement:

- Semantic HTML.
- Logical heading hierarchy.
- Keyboard navigation.
- Visible focus states.
- Accessible form labels and validation messages.
- Sufficient text and interface contrast.
- Appropriate alternative text for informative images.
- Accessible dialogs and dropdowns.
- Reduced-motion support.
- Responsive text sizing.
- Clear error, success, loading, and empty states.

Ensure accessibility in Arabic RTL, English LTR, and Turkish LTR.

Do not communicate status through color alone.

Avoid unnecessary animation, intrusive popups, confusing navigation, and controls without clear labels.

## 9. Performance and SEO

Build a fast, search-engine-friendly website.

### Performance

- Optimize and appropriately size images.
- Use modern image formats where supported.
- Lazy-load noncritical images.
- Minimize unnecessary JavaScript and CSS.
- Avoid redundant database queries.
- Use pagination for large content collections.
- Cache suitable public content where safe.
- Invalidate caches when relevant content changes.
- Avoid caching private or user-specific responses publicly.
- Use production asset builds and appropriate compression.
- Measure performance instead of assuming an optimization is beneficial.

### SEO

Implement:

- Unique page titles and meta descriptions.
- Semantic page structure.
- Canonical URLs where appropriate.
- Open Graph metadata.
- XML sitemap.
- Appropriate robots directives.
- Structured data only when supported by actual content.
- Localized metadata.
- Correct language and alternate-language metadata for translated pages.
- SEO-friendly slugs.
- Appropriate handling of missing pages and redirects.

Do not expose private dashboard pages or draft content to search engines.

## 10. Testing and Quality Assurance

Create a testing strategy appropriate to the application.

Include:

- Unit tests for business logic.
- Feature or integration tests for important application flows.
- Authentication and authorization tests.
- Validation and error-handling tests.
- CRUD tests for dashboard modules.
- Tests for localization and RTL/LTR behavior where practical.
- File upload security tests.
- Regression tests for critical functionality.
- Accessibility checks.
- Responsive layout verification.
- Performance checks for important pages.

Verify that unauthorized users cannot access dashboard functions through direct HTTP requests.

Test Arabic, English, and Turkish separately, including language switching and missing-translation behavior.

Do not claim that tests pass unless they have actually been executed successfully.

## 11. Maintainability and Developer Workflow

Use Git and maintain a clean, reviewable history.

- Keep changes focused and meaningful.
- Avoid committing generated files or local-only configuration unless intentionally required.
- Exclude secrets, caches, logs, and environment-specific files.
- Document installation and configuration.
- Maintain an accurate environment-variable example file without real secrets.
- Document database migrations and required setup steps.
- Provide clear development, testing, and production instructions.
- Keep dependencies current within a compatible, tested version range.
- Avoid unnecessary packages.
- Document architectural decisions when they materially affect future maintenance.

Use consistent code formatting and linting. Add static analysis when supported by the chosen stack.

Do not silently change existing application behavior or remove functionality without justification.

## 12. Production Readiness

Prepare the application for reliable deployment.

Include:

- Separate development, testing, and production configuration.
- Environment-based secrets.
- Production asset compilation.
- Safe migration procedures.
- Logging and error monitoring.
- Backup and restoration procedures.
- Health checks appropriate to the architecture.
- Secure permissions for files and directories.
- A deployment checklist.
- Rollback instructions.
- Documentation of external service dependencies.

Never assume that a successful local build means the application is production-ready.

## 13. Implementation Workflow

Follow this order instead of generating the entire application in one uncontrolled pass.

### Phase 1 — Repository assessment and planning

1. Inspect the repository and existing framework.
2. Identify current versions, dependencies, entry points, routes, and architectural conventions.
3. Review existing authentication, authorization, localization, and database structure.
4. Identify existing reusable components and technical constraints.
5. Define the proposed architecture and project structure.
6. List assumptions and missing business information.
7. Produce a concise implementation plan.

Do not overwrite existing work or introduce breaking changes before understanding the repository.

### Phase 2 — Brand and UI foundation

1. Implement centralized design tokens.
2. Establish typography, spacing, color, and responsive rules.
3. Build reusable UI components.
4. Implement the shared public layout.
5. Implement the dashboard layout.
6. Verify the visual identity against the supplied Wasmsoft logo.

### Phase 3 — Localization

1. Configure Arabic, English, and Turkish.
2. Implement locale selection and persistence.
3. Implement RTL/LTR layout switching.
4. Translate interface elements.
5. Establish the database translation model.
6. Test language switching and missing translations.

### Phase 4 — Backend and content management

1. Design database tables and relationships.
2. Implement migrations and models.
3. Implement validation and authorization.
4. Build content-management modules.
5. Implement media management securely.
6. Implement user and role management.
7. Connect the public website to real content.

### Phase 5 — Quality and hardening

1. Execute tests.
2. Resolve security issues.
3. Verify localization and accessibility.
4. Check responsive behavior.
5. Measure and improve performance.
6. Verify SEO and error handling.

### Phase 6 — Production preparation

1. Document environment configuration.
2. Prepare deployment instructions.
3. Verify backup and rollback procedures.
4. Perform final acceptance testing.
5. Document remaining limitations and operational requirements.

## 14. Required Deliverables

At completion, provide:

- A functional public-facing Wasmsoft website.
- A protected administrative dashboard.
- Arabic, English, and Turkish localization.
- Correct Arabic RTL and English/Turkish LTR layouts.
- A reusable design system inspired by the Wasmsoft logo.
- Database-backed content management.
- Secure authentication and authorization.
- Migrations and configuration examples.
- Automated tests and their actual execution results.
- Installation and development documentation.
- Production deployment instructions.
- Backup and rollback guidance.
- A concise summary of implemented features, architectural decisions, known limitations, and outstanding work.

## 15. Final Instructions to the Coding Agent

Work incrementally and inspect the actual project before implementing changes.

Prioritize correctness, maintainability, security, accessibility, and performance over unnecessary visual complexity or speed of code generation.

Use the existing framework and project conventions when appropriate. Do not invent APIs, configuration options, dependencies, or successful test results.

Do not expose secrets or weaken security controls to make features easier to implement.

Do not leave buttons, forms, dashboard modules, or administrative actions appearing functional when they are not connected to real behavior. Clearly identify any incomplete feature.

After each phase, summarize the changes, the files affected, the tests executed, and any remaining issues before proceeding to the next phase.

**The final result must be an original, professional Wasmsoft website that reflects the brand identity of the supplied logo and is engineered for long-term scalability, maintainability, security, and growth.**
