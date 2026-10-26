# 0004 — Four fixed roles with permissions defined in code

Status: accepted (2026-10-25)

## Context

The brief suggests four roles (Administrator, Editor, Author, Viewer) and asks for least privilege, server-side checks and no needless complexity. A team this size does not need roles edited in the database, and a roles package would add tables and a dependency.

## Decision

- `App\Auth\Permission` lists the capabilities (view content, manage content, write and publish articles, upload and manage media, manage navigation, handle and delete inquiries, manage users, manage settings, view the audit log). Each is registered as a gate.
- `App\Auth\Role` grants each role an explicit list of permissions; nothing is inherited from a role's rank. Users have exactly one role.
- Policies (`app/Policies`) combine permissions with ownership and state: authors edit only their own unpublished articles; nobody deletes or deactivates their own account; the last active administrator keeps the role; only administrators grant privileged roles.
- Every dashboard route requires an active, signed-in account; every action calls a policy or gate on the server. Hidden buttons are only a convenience. A test walks the whole route list to keep it that way.

## Consequences

- Permissions are reviewed in code review and covered by tests; they cannot drift in the database.
- A new role or permission needs a code change and a release, which is acceptable for a small, stable team.
- Deactivating a user signs them out of every session at once.
