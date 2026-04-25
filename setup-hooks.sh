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

# 1. Resolve php binary path — hooks run in a minimal environment that may
#    not inherit the developer's full PATH (common on Windows / Git Bash).
PHP_BIN="$(command -v php 2>/dev/null || true)"
if [ -z "$PHP_BIN" ]; then
    echo "  ✖  php binary not found in PATH. Make sure PHP is installed and on your PATH."
    exit 1
fi
PHP_DIR="$(dirname "$PHP_BIN")"
echo "  ✔  php found at: $PHP_BIN"

# 2. Verify phpcs is installed globally
if ! command -v phpcs &>/dev/null; then
    echo "  Installing PHP_CodeSniffer globally..."
    composer global require squizlabs/php_codesniffer --no-interaction --quiet
    echo "  ✔  phpcs installed."
else
    echo "  ✔  phpcs already available: $(phpcs --version)"
fi

PHPCS_BIN="$(command -v phpcs)"
PHPCS_DIR="$(dirname "$PHPCS_BIN")"

# 3. Build a PATH line that includes both php and phpcs directories.
#    Written into each installed hook so they work in any shell environment.
HOOK_PATH_LINE="export PATH=\"$PHP_DIR:$PHPCS_DIR:\$PATH\""

install_hook() {
    local template="$1"
    local dest="$2"

    # Read template, inject PATH export as the second line (after the shebang)
    {
        head -1 "$template"                         # shebang
        echo "$HOOK_PATH_LINE"                      # injected PATH
        tail -n +2 "$template"                      # rest of the script
    } > "$dest"

    chmod +x "$dest"
}

# 4. Install pre-push hook
echo ""
echo "  Installing pre-push hook..."
install_hook "$TEMPLATES_DIR/pre-push.sh" "$HOOKS_DIR/pre-push"
echo "  ✔  .git/hooks/pre-push installed."

# 5. Install pre-merge-commit hook
echo ""
echo "  Installing pre-merge-commit hook..."
install_hook "$TEMPLATES_DIR/pre-merge-commit.sh" "$HOOKS_DIR/pre-merge-commit"
echo "  ✔  .git/hooks/pre-merge-commit installed."

# 6. Done
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
