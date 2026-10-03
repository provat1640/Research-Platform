# Co-Auth Quality, Security, and Productivity Playbook

This playbook turns recurring research-platform risks into repeatable tests and implementation actions. It covers authentication, ORCID identity, project isolation, manuscript integrity, AI/MCP integrations, external connectivity, and developer productivity.

## Verification Commands

Run these from the repository root:

```powershell
php artisan migrate:status
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
php artisan route:list --except-vendor
```

For a repeatable local data check:

```powershell
php artisan migrate --force
php artisan db:seed --force
```

Do not use `migrate:fresh` against shared or production databases.

## Test Case Matrix

| ID | Risk | Test | Expected result |
| --- | --- | --- | --- |
| AUTH-01 | Account identity | Register without ORCID | Validation error; no user created |
| AUTH-02 | Account identity | Register with malformed ORCID | Validation error on `orcid_id` |
| AUTH-03 | Account identity | Register with duplicate ORCID | Validation error; unique constraint remains intact |
| AUTH-04 | Account identity | Login user without ORCID | Login rejected and session is not retained |
| AUTH-05 | Session security | Login with valid credentials | Session regenerates and redirects to dashboard |
| AUTH-06 | Session security | Logout | Session invalidates and redirects to login |
| AUTH-07 | API security | Call `/api/v1/projects` as guest | HTTP 401 |
| AUTH-08 | Tenant isolation | Read another user's project | HTTP 403 or 404 according to the privacy policy |
| AUTH-09 | Tenant isolation | Update another project's task | HTTP 403; database unchanged |
| AUTH-10 | AI entitlement | Expired trial calls AI endpoint | HTTP 403; no provider request |
| AUTH-11 | AI entitlement | Active trial calls AI endpoint | Provider request is made with project context |
| DATA-01 | Manuscript integrity | Create paper with abstract under 100 chars | Validation error |
| DATA-02 | Manuscript integrity | Create duplicate abstract | HTTP 422 and no duplicate record |
| DATA-03 | Version integrity | Save document version | Version increments and author is stored |
| DATA-04 | Collaboration | Save version with a project member | Broadcast event is dispatched |
| DATA-05 | Collaboration | Subscribe as non-member | Private channel authorization fails |
| AI-01 | Provider safety | AI provider unavailable | HTTP 503; no stack trace or secret in response |
| AI-02 | Provider safety | Evaluate structured test data | Response contains model output only; no arbitrary code execution |
| INT-01 | Connectivity | AI health probe succeeds | Status is `ready`; credentials are absent |
| INT-02 | Connectivity | AI health probe times out | Status is `unreachable`; request completes within timeout |
| INT-03 | Connectivity | Supabase is not configured | Status is `not_configured`; no outbound request |
| INT-04 | Connectivity | Reverb settings exist | Status is `configured`; secret values are never returned |
| UI-01 | Usability | Project form receives 422 | Actual validation message is visible |
| UI-02 | Usability | Session expires during form submit | User sees a sign-in message instead of a generic failure |
| UI-03 | Usability | Dashboard API is unavailable | Empty/error state is visible and layout remains usable |
| PERF-01 | Productivity | Load project list with 100 projects | Eager-loaded owner/member counts; no per-card query loop |
| PERF-02 | Productivity | Open workspace | Project, members, latest version, tasks, and feedback load without avoidable N+1 queries |

## Implementation Patterns

### ORCID-required registration

Use server-side validation and a database uniqueness constraint. Client-side formatting is only a convenience.

```php
'orcid_id' => [
    'required',
    'regex:/^\\d{4}-\\d{4}-\\d{4}-[\\dX]{4}$/i',
    'unique:users,orcid_id',
],
```

Never log passwords, access tokens, service keys, or complete authorization headers.

### Project authorization

Every project-scoped read and write must verify that the authenticated user is the owner or a project member. Do not rely on a project ID supplied by the browser as proof of access.

```php
if ($project->owner_id !== $user->id
    && ! $project->members()->whereKey($user->id)->exists()) {
    abort(403, 'You are not a member of this research project.');
}
```

For sensitive multi-tenant systems, prefer `404` when revealing that another tenant's record exists would itself be sensitive.

### Safe AI calls

- Keep provider URLs, model names, and credentials in environment configuration.
- Use short connect and request timeouts.
- Catch connection and provider errors.
- Send only the project context required for the requested task.
- Tell the model not to invent sources, findings, or citations.
- Treat model output as untrusted text; never execute it as PHP, SQL, shell, or JavaScript.
- Use structured test data for model evaluation.

### Safe connectivity health

Health responses may include `ready`, `configured`, `not_configured`, or `unreachable`. They must never include:

- API keys
- Supabase service keys
- MCP bearer tokens
- Stripe secrets
- Authorization headers
- Raw provider exception messages

### External data boundaries

Supabase is an optional remote system. The local database remains the application source of truth until synchronization conflict rules are implemented. Do not silently overwrite local research data from a remote response.

## Automatic Suggestion Rules

Run these checks during review or CI and create a remediation task when a rule matches.

### Security suggestions

1. **Unauthenticated state-changing route**
   - Match: `POST`, `PATCH`, `PUT`, or `DELETE` route without `auth` middleware.
   - Suggestion: add authentication and project membership authorization.

2. **Project ID without membership check**
   - Match: controller accepts `ResearchProject $project` and writes data without a policy/middleware check.
   - Suggestion: apply `project.member` or a policy before loading project data.

3. **Secret in source or response**
   - Match: `API_KEY`, `SECRET`, `TOKEN`, `password`, or `service_key` in logs, JSON responses, Blade output, or committed config.
   - Suggestion: move the value to `.env`, redact it, and add a regression test asserting it is absent.

4. **Arbitrary execution path**
   - Match: user/model output passed to `eval`, shell execution, raw SQL, filesystem paths, or dynamic JavaScript.
   - Suggestion: remove execution; use an allow-listed parser or structured evaluator.

5. **External request without timeout**
   - Match: `Http::get`, `Http::post`, or MCP transport without connect/request timeout.
   - Suggestion: add `connectTimeout` and `timeout`, then test timeout behavior.

6. **Raw provider error exposed**
   - Match: exception message returned directly from an AI, Supabase, Stripe, or MCP integration.
   - Suggestion: log internal details securely and return a stable generic client message.

### Productivity suggestions

1. **Hard-coded UI state**
   - Match: activity, counters, project names, or feedback rendered as fixed text in a data-driven page.
   - Suggestion: expose a small API payload and render an empty/loading/error state.

2. **Generic frontend error**
   - Match: every failed request displays the same message.
   - Suggestion: distinguish validation, expired session, rate limit, provider unavailable, and network failure.

3. **Duplicate navigation markup**
   - Match: more than one primary navigation block in the same page.
   - Suggestion: extract one shared navigation partial/component.

4. **Repeated query inside a loop**
   - Match: relationship access inside a collection loop without eager loading.
   - Suggestion: use `with`, `withCount`, or an explicit aggregate query and add an N+1 regression test.

5. **Non-idempotent seed data**
   - Match: fixed emails/titles inserted with `create()` in a seeder.
   - Suggestion: use `updateOrCreate()` and `syncWithoutDetaching()`.

6. **Long-running synchronous work**
   - Match: provider calls, document processing, exports, or large synchronization inside a web request.
   - Suggestion: dispatch a queued job with retry/backoff and expose progress to the UI.

## Review Checklist

- [ ] Every new route has an authentication decision.
- [ ] Every project-scoped route has a membership decision.
- [ ] ORCID is required and unique for newly created accounts.
- [ ] Tests cover guest, outsider, invalid input, valid input, and provider failure paths.
- [ ] AI output cannot execute application code.
- [ ] External calls have timeouts and safe error responses.
- [ ] Health endpoints redact credentials.
- [ ] Database migrations are applied and reversible.
- [ ] Seeders can run more than once.
- [ ] Dashboard and workspace show loading, empty, error, and success states.
- [ ] `vendor/bin/pint --dirty --format agent` passes.
- [ ] `php artisan test --compact` passes.
- [ ] `npm run build` passes.

## Suggested CI Gate

```yaml
- name: PHP style
  run: vendor/bin/pint --dirty --format agent

- name: Laravel tests
  run: php artisan test --compact

- name: Frontend build
  run: npm run build
```

If any gate fails, stop deployment and create a focused remediation task from the matching rule above. Do not bypass security or authorization failures to ship a feature.
