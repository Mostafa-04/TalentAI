#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# setup-hooks.sh
#
# One-time setup script every developer runs after cloning the repo.
# Works on Linux, macOS, WSL, and Git Bash (Windows).
#
# Usage:
#   bash setup-hooks.sh
# ---------------------------------------------------------------------------

set -e

REPO_ROOT="$(git rev-parse --show-toplevel)"
HOOKS_DIR="$REPO_ROOT/.git/hooks"
TEMPLATES_DIR="$REPO_ROOT/hooks"

echo ""
echo "┌─────────────────────────────────────────────┐"
echo "│   TalentAI · Developer Hook Setup           │"
echo "└─────────────────────────────────────────────┘"
echo ""

# 1. Verify phpcs is installed globally
if ! command -v phpcs &>/dev/null; then
    echo "  Installing PHP_CodeSniffer globally..."
    composer global require squizlabs/php_codesniffer --no-interaction --quiet
    echo "  ✔  phpcs installed."
else
    echo "  ✔  phpcs already available: $(phpcs --version)"
fi

# 2. Install pre-push hook
echo ""
echo "  Installing pre-push hook..."
cp "$TEMPLATES_DIR/pre-push.sh" "$HOOKS_DIR/pre-push"
chmod +x "$HOOKS_DIR/pre-push"
echo "  ✔  .git/hooks/pre-push installed."

# 3. Install pre-merge-commit hook
echo ""
echo "  Installing pre-merge-commit hook..."
cp "$TEMPLATES_DIR/pre-merge-commit.sh" "$HOOKS_DIR/pre-merge-commit"
chmod +x "$HOOKS_DIR/pre-merge-commit"
echo "  ✔  .git/hooks/pre-merge-commit installed."

# 4. Done
echo ""
echo "┌─────────────────────────────────────────────────────────────────┐"
echo "│  Setup complete.                                                │"
echo "│                                                                 │"
echo "│  Every git push and CLI merge into main/dev will now           │"
echo "│  be blocked if any controller violates the quality rules.      │"
echo "│                                                                 │"
echo "│  To run the check manually:                                     │"
echo "│    phpcs --standard=phpcs.xml app/Http/Controllers             │"
echo "└─────────────────────────────────────────────────────────────────┘"
echo ""
