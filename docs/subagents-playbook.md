# Subagents Playbook — Asclepius

A personalized guide for using a standing team of five specialized Claude Code subagents to move
faster without losing quality on Asclepius. The agents are defined in `.claude/agents/` and are
invokable by name (via the Agent/Task tool or `/agents`). This playbook says when to use each,
how they hand off, and gives ready-to-paste prompts for this repo.

It follows the project rules in `CLAUDE.md`: do not over-engineer, make the smallest safe change,
docs are the source of truth, run a real runtime smoke test before "done", and no emojis anywhere.

## The team

| Agent | Role | Edits code? | Model | Best for |
|-------|------|:-----------:|-------|----------|
| `auditor` | Code / architecture / correctness + over-engineering review | No (reports) | opus | Second pair of eyes on a multi-file change before it is called done |
| `security` | PHI-aware security: injection, RBAC, secrets, auth, leakage | No (reports) | opus | Before go-live; after auth/RBAC/DB or new-form changes |
| `qa` | Functional / acceptance testing per role (runs the app) | No (reports) | sonnet | Verifying a feature actually works for every role |
| `tester` | Scripted checks: lint, curl smoke, DB assertions, PDF | No (writes scratch scripts) | sonnet | Fast, repeatable proof a change did not break anything |
| `project-manager` | Plan + keep docs/roadmap/CHANGELOG in sync | Docs only | sonnet | Turning an ask into a plan and closing it out with doc updates |

**QA vs Tester:** QA judges *behavior/acceptance* per role against `docs/qa-checklist.md`
(does the feature do the right thing for a doctor / cashier / lab / reception / admin?). Tester
runs *mechanical* checks (php -l, curl 200/302/403, DB row assertions, PDF validity). Use both:
Tester proves it does not crash; QA proves it is correct.

## When to use a subagent (and when not)

Use one when the work benefits from parallel investigation or an independent perspective: a
multi-file audit, a security sweep, per-role QA, or a scripted regression. Do NOT spawn one for a
small localized change you can just make inline (per CLAUDE.md) - a single-function edit or an
obvious one-file fix does not need an agent. Quality over ceremony.

## The default workflow

For a non-trivial change (a new module, an RBAC change, a batch of fixes):

1. `project-manager` - turn the ask into a short ordered plan and a todo list; note what is
   explicitly deferred.
2. Implement (main session, or a focused agent) - smallest safe change; follow existing patterns.
3. `auditor` + `security` - run in parallel on the diff; each returns findings (severity +
   confidence + minimal fix). Fix the confirmed issues.
4. `tester` - lint every changed file, curl-smoke the pages (200 + sidebar present + no PHP
   warnings), assert new DB rows land.
5. `qa` - drive `docs/qa-checklist.md` per role; confirm sidebar visibility, redirects/403s,
   create-persists, and the PDF.
6. `project-manager` - update `CHANGELOG.md`, `docs/roadmap.md`, and any affected doc; record
   follow-ups. Only now is the task done.

Steps 3 and 4 can overlap. Keep the loop tight: reviewers report, the main session fixes.

## Ready-to-paste prompts (this repo)

- Auditor: "Audit the RBAC + shared-sidebar changes across the root module pages and
  `backend/auth/rbac.php`. Confirm every handler and page enforces `require_module_access`, UI
  gates use `can_access(.., 'write')`, and flag any correctness bug, dead code, or
  over-engineering. Report findings with file:line and the smallest fix."
- Security: "Security review of the rebuilt create forms (Prescription, Billing, Laboratory
  Result) and their `backend/api/*` handlers - prepared statements, access control on page load
  and API, error/PHI leakage. Map issues to docs/security.md S-numbers."
- Tester: "Start the app on localhost:8010, log in as admin, and smoke-test every module page:
  assert HTTP 200, sidebar markup present, and no PHP warnings. Then assert a created prescription
  row appears in `asclepius_db`. Report PASS/FAIL with commands."
- QA: "Run docs/qa-checklist.md sections 2-5 for the doctor and cashier roles using the seeded
  accounts. Confirm the doctor cannot see or open Lab Technicians/Cashiers/Receptionists/Billing,
  and that a cashier can create a bill that persists. Report Pass/Fail with the failing request."
- Project Manager: "The RBAC-in-UI, SweetAlert, and sidebar-scrollbar work has landed. Update
  CHANGELOG.md, docs/roadmap.md, docs/frontend.md, and docs/security.md accordingly, and list any
  deferred follow-ups."

## Guardrails (apply to every agent)

- Do not over-engineer; prefer the smallest safe change and preserve the existing architecture
  (refactor in place, no framework, no new dependencies without a strong reason).
- Reviewers (`auditor`, `security`) and testers report; they do not edit application code.
  `project-manager` edits docs only. Only the main session (or an explicit implementer) changes
  app code.
- Verify against the code and a running app - do not assume. `php -l` alone is not proof.
- No emojis in any code, docs, or notes.
