# Controller Quality Enforcement — Setup Guide

Every public method in `app/Http/Controllers/**` must satisfy three rules:

| Rule | What is checked |
|---|---|
| PHPDoc | A `/** ... */` block immediately above the method |
| try/catch | A `try` block inside the method body |
| ActivityLogger | A call to `ActivityLogger->log()` inside the method body |

Magic methods (`__construct`, `__destruct`, `__invoke`, etc.) are excluded.

---

## 1 · Developer Setup (run once after cloning)

```bash
bash setup-hooks.sh
```

This will:
- Install `phpcs` globally if not already present
- Install the `pre-push` and `pre-merge-commit` Git hooks

> **Windows users:** run this inside Git Bash or WSL.

---

## 2 · Run the Check Manually

```bash
phpcs --standard=phpcs.xml app/Http/Controllers
```

To auto-fix style issues only (not logic violations):

```bash
phpcbf --standard=phpcs.xml app/Http/Controllers
```

---

## 3 · Using ActivityLogger in a Controller

### Option A — app() helper

```php
use App\Services\ActivityLogger;

public function store(Request $request): JsonResponse
{
    /**
     * Store a new candidate.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    try {
        // ... your logic ...

        app(ActivityLogger::class)->log(
            action:      'candidate.store',
            description: 'Created a new candidate profile.',
            properties:  ['source' => 'manual'],
            models:      [Candidate::class]
        );

        return response()->json(['message' => 'Created.'], 201);
    } catch (\Throwable $e) {
        return response()->json(['message' => 'Error.'], 500);
    }
}
```

### Option B — Constructor injection

```php
use App\Services\ActivityLogger;

public function __construct(private readonly ActivityLogger $activityLogger) {}

public function destroy(int $id): JsonResponse
{
    /**
     * Delete a candidate.
     *
     * @param  int $id
     * @return JsonResponse
     */
    try {
        // ... your logic ...

        $this->activityLogger->log(
            action:  'candidate.destroy',
            models:  [Candidate::class]
        );

        return response()->json(status: 204);
    } catch (\Throwable $e) {
        return response()->json(['message' => 'Error.'], 500);
    }
}
```

---

## 4 · ActivityLogger::log() Signature

```php
ActivityLogger::log(
    string      $action,       // required — machine label, e.g. 'candidate.store'
    string      $description,  // optional — human readable
    array       $properties,   // optional — any extra key/value data
    array|null  $models,       // optional — model class names touched
)
```

**Fields stored automatically** (no need to pass them):

| Field | Source |
|---|---|
| `user_id` | `Auth::id()` |
| `user_name` | `Auth::user()->name` |
| `user_email` | `Auth::user()->email` |
| `user_role` | `Auth::user()->role` |
| `is_authenticated` | `Auth::check()` |
| `controller` | resolved via backtrace |
| `controller_method` | resolved via backtrace |
| `http_method` | `Request::method()` |
| `url` | `Request::fullUrl()` |
| `ip_address` | `Request::ip()` |
| `user_agent` | `Request::userAgent()` |
| `request_data` | `Request::all()` (passwords stripped) |

---

## 5 · Run the Migration

```bash
php artisan migrate
```

---

## 6 · GitHub Branch Protection (run once per repo)

Requires the [GitHub CLI](https://cli.github.com/) (`gh`).

```bash
# Authenticate if not already
gh auth login

# Replace ORG/REPO with your repository slug, e.g. acme/talentai
REPO="ORG/REPO"

# Protect main
gh api repos/$REPO/branches/main/protection \
  --method PUT \
  --header "Accept: application/vnd.github+json" \
  --field "required_status_checks[strict]=true" \
  --field "required_status_checks[contexts][]=PHPDoc · ActivityLogger · try/catch" \
  --field "enforce_admins=true" \
  --field "required_pull_request_reviews[required_approving_review_count]=1" \
  --field "restrictions=null"

# Protect dev
gh api repos/$REPO/branches/dev/protection \
  --method PUT \
  --header "Accept: application/vnd.github+json" \
  --field "required_status_checks[strict]=true" \
  --field "required_status_checks[contexts][]=PHPDoc · ActivityLogger · try/catch" \
  --field "enforce_admins=true" \
  --field "required_pull_request_reviews[required_approving_review_count]=1" \
  --field "restrictions=null"
```

> The `contexts` value must match the `name:` field in `.github/workflows/controller-quality.yml` exactly.

---

## 7 · Enforcement Summary

| Trigger | Hook / Workflow | Blocks |
|---|---|---|
| `git push` | `.git/hooks/pre-push` | Push |
| `git merge` into main/dev | `.git/hooks/pre-merge-commit` | Merge commit |
| Pull Request to main/dev | `.github/workflows/controller-quality.yml` | PR merge |
