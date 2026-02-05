# Git Hooks

This directory contains Git hooks to help enforce coding standards automatically.

## What are Git Hooks?

Git hooks are scripts that run automatically at certain points in the Git workflow (e.g., before committing, before pushing). They help catch issues early before code review.

## Available Hooks

### pre-commit

Runs before each commit to check:
- ✅ Code formatting (Laravel Pint)
- ✅ No debug statements (dd(), dump(), console.log)
- ✅ No inline validation in controllers
- ✅ Tests pass
- ✅ No hardcoded API URLs
- ✅ Linting passes
- ✅ Commit message format

## Installation

### One-time Setup

Run this command from the project root:

```bash
# Linux/Mac
cp .githooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit

# Windows (Git Bash)
cp .githooks/pre-commit .git/hooks/pre-commit
```

### Automatic Setup (Recommended)

Add to `backend/composer.json`:

```json
{
    "scripts": {
        "post-install-cmd": [
            "@php -r \"copy('.githooks/pre-commit', '.git/hooks/pre-commit');\"",
            "@php -r \"chmod('.git/hooks/pre-commit', 0755);\""
        ]
    }
}
```

## Usage

Once installed, the hooks run automatically:

```bash
# Make changes
git add .

# Commit (hooks run automatically)
git commit -m "feat: Add user profile"

# If checks fail, fix issues and try again
./vendor/bin/pint        # Fix formatting
php artisan test         # Run tests
git commit -m "feat: Add user profile"
```

## Bypassing Hooks (Not Recommended)

In rare cases where you need to skip hooks:

```bash
git commit --no-verify -m "emergency hotfix"
```

**⚠️ WARNING**: Only use `--no-verify` for genuine emergencies. Your code will still be checked during code review.

## Customizing Hooks

To modify the pre-commit checks:

1. Edit `.githooks/pre-commit`
2. Test your changes: `.githooks/pre-commit`
3. Reinstall: `cp .githooks/pre-commit .git/hooks/pre-commit`

## Troubleshooting

### Hook not running

```bash
# Check if hook is installed
ls -la .git/hooks/pre-commit

# Reinstall
cp .githooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

### Hook failing unexpectedly

```bash
# Run manually to see detailed errors
.githooks/pre-commit

# Check permissions (Linux/Mac)
chmod +x .git/hooks/pre-commit
```

### False positives

If the hook incorrectly flags your code:

1. Check the specific check that's failing
2. Verify your code follows [CODING_STANDARDS.md](../CODING_STANDARDS.md)
3. If it's a genuine false positive, update the hook script

## What Each Check Does

### Code Formatting
- Runs Laravel Pint to ensure consistent code style
- Fix: `./vendor/bin/pint`

### Debug Statements
- Searches for `dd()`, `dump()`, `console.log()` in staged files
- Fix: Remove debug statements

### Inline Validation
- Checks controllers for `$request->validate()`
- Fix: Create Form Request classes

### Tests
- Runs PHPUnit and Vitest
- Fix: Fix failing tests

### Hardcoded URLs
- Checks for localhost URLs in code
- Fix: Use environment variables

### Linting
- Runs ESLint on frontend code
- Fix: `npm run lint:fix`

## Benefits

- ✅ Catch issues before code review
- ✅ Maintain consistent code quality
- ✅ Reduce review time
- ✅ Learn coding standards through feedback
- ✅ Prevent common mistakes

## See Also

- [CODING_STANDARDS.md](../CODING_STANDARDS.md) - Full coding guidelines
- [CONTRIBUTING.md](../CONTRIBUTING.md) - Contribution guide
- [Git Hooks Documentation](https://git-scm.com/book/en/v2/Customizing-Git-Git-Hooks)
