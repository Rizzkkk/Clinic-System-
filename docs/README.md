# Asclepius — Documentation

Asclepius is a web-based **medical & diagnostic clinic management system** (PHP + MySQL)
for Asclepius Medical & Diagnostic Group Inc. It manages patients, doctors, appointments,
medical records, laboratory results, prescriptions, and billing through a staff-facing web
app.

This `docs/` folder is the **source of truth** for the system. It was created as Phase 0 of
the production-readiness effort: organize the codebase and document it *before* refactoring
the working-but-spaghetti PHP pages into a clean `frontend/` + `backend/` structure.

> **Start here:** **[roadmap.md](roadmap.md)** — the live plan + codebase map (where we are,
> what's next, and where everything goes). The Phase 1 backend refactor is well underway: all 8
> module pages now run on the new `backend/` and are runtime-QA'd.

## How to navigate

| Doc | Purpose |
|-----|---------|
| [roadmap.md](roadmap.md) | **Live plan + codebase map** — status, the step checklist, where everything goes. **Read first.** |
| [developer-setup.md](developer-setup.md) | New-developer quick start: setup, env, schema/migrations, run, QA. |
| [qa-accounts.md](qa-accounts.md) | How to log in as each role (doctor / reception / lab / cashier / admin) for visual QA. |
| [qa-checklist.md](qa-checklist.md) | Systematic per-role / per-module QA test plan (auth, RBAC visibility, create-persists, stats, PDF). |
| [demo-walkthrough.md](demo-walkthrough.md) | Step-by-step script to demo the system end to end (roles, CRUD, PDF reports, X-ray upload) to a stakeholder. |
| [subagents-playbook.md](subagents-playbook.md) | How to use the 5 Claude Code subagents (auditor, security, qa, tester, project-manager) defined in `.claude/agents/`. |
| [overview.md](overview.md) | What Asclepius is, module list, glossary, SDLC process & environments. |
| [requirements.md](requirements.md) | Functional + non-functional requirements and user-role definitions. |
| [architecture.md](architecture.md) | System architecture, request flow, target frontend/backend split, framework decision record. |
| [backend-plan.md](backend-plan.md) | The refactor-in-place plan: target layout, conventions, migration sequence. |
| [api-reference.md](api-reference.md) | Per-module API contract (the `?api=` GET and POST `action` endpoints + JSON shapes). |
| [frontend.md](frontend.md) | Design system, page inventory, and feature audit (working vs stub). |
| [security.md](security.md) | Auth, the RBAC permission matrix, and the prioritized security issues + remediation. |
| [deployment.md](deployment.md) | Setup, environment variables, environments, and the go-live checklist. |
| [production-release.md](production-release.md) | Final pre-launch notes: what to delete, what to do, and planned work. |
| [database/database.md](database/database.md) | Canonical schema reference (tables, columns, constraints, indexes). |
| [database/erd.md](database/erd.md) | Entity-relationship diagram (Mermaid) + relationship narrative. |
| [database/migrations.md](database/migrations.md) | Resolves the 3-way schema divergence; specifies planned tables for the stub modules. |

## Reading order for a new developer

1. [overview.md](overview.md) → 2. [requirements.md](requirements.md) →
3. [architecture.md](architecture.md) → 4. [database/erd.md](database/erd.md) +
[database/database.md](database/database.md) → 5. [frontend.md](frontend.md) +
[api-reference.md](api-reference.md) → 6. [security.md](security.md) +
[backend-plan.md](backend-plan.md) → 7. [deployment.md](deployment.md).
