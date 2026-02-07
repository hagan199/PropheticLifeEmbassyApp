# Code Review & Quality Checks

## 🎯 Overview

This project uses automated code quality tools to ensure production-grade code:

- **PHPStan** (Level 5) - Static analysis for type safety and Laravel-specific checks
- **Laravel Pint** (PSR-12) - Automatic code style formatting
- **Custom Security Checks** - Detects hardcoded passwords, SQL injection risks, and dangerous patterns

---

## 🚀 Quick Start

### Install Dependencies
```bash
cd backend
composer install
```

### Run Code Review
```bash
# Run all checks
php artisan code:review

# Auto-fix code style issues
php artisan code:review --fix

# Increase memory limit for PHPStan
php artisan code:review --memory=1G
```

---

## 📊 What Gets Checked

### 1. PHPStan (Static Analysis)
- ✅ Type safety
- ✅ Undefined variables and methods
- ✅ Laravel-specific patterns (Eloquent, Collections, Facades)
- ✅ Return type mismatches
- ✅ Missing use statements

**Configuration**: `backend/phpstan.neon`

### 2. Laravel Pint (Code Style)
- ✅ PSR-12 compliance
- ✅ Consistent indentation and spacing
- ✅ Import ordering
- ✅ Line length limits
- ✅ Trailing whitespace removal

**Auto-fix**: `php artisan code:review --fix`

### 3. Custom Security Checks
- ⚠️ Hardcoded passwords
- ⚠️ SQL injection risks (`DB::raw` with variables)
- ⚠️ Dangerous functions (`eval()`)
- ℹ️ TODO comments (informational)

---

## 🔄 CI/CD Integration

### GitHub Actions
Every pull request automatically runs:

1. **Code Quality Checks** (PHPStan + Pint)
2. **Security Audit** (Composer audit)
3. **Tests** (PHPUnit with PostgreSQL)

**Workflow**: `.github/workflows/code-review.yml`

### Pre-Commit Hook (Optional)

Create `.githooks/pre-commit`:

```bash
#!/bin/sh
echo "Running code review..."

cd backend

# Run Pint
vendor/bin/pint --test
if [ $? -ne 0 ]; then
    echo "❌ Code style issues found. Run 'php artisan code:review --fix'"
    exit 1
fi

# Run PHPStan
vendor/bin/phpstan analyse --no-progress
if [ $? -ne 0 ]; then
    echo "❌ PHPStan found issues. Please fix before committing."
    exit 1
fi

echo "✅ Code review passed!"
exit 0
```

Enable:
```bash
git config core.hooksPath .githooks
chmod +x .githooks/pre-commit
```

---

## 🛠️ Configuration

### PHPStan Configuration (`phpstan.neon`)

```yaml
parameters:
    level: 5  # 0-9 (5 is balanced for existing codebases)
    paths:
        - app
        - routes
        - database/seeders
        - database/factories
```

**Increasing Strictness:**
- Level 5: Current (recommended for teams)
- Level 6: Catches more edge cases
- Level 7-9: Very strict (for new projects)

### Pint Configuration (`.pint.json` - optional)

Laravel Pint uses PSR-12 by default. Create `.pint.json` to customize:

```json
{
    "preset": "laravel",
    "rules": {
        "line_ending": true,
        "method_chaining_indentation": true
    }
}
```

---

## 📈 Best Practices

### Before Committing
```bash
# 1. Auto-fix style
php artisan code:review --fix

# 2. Run full check
php artisan code:review

# 3. Stage fixed files
git add .

# 4. Commit
git commit -m "Your message"
```

### CI/CD Workflow
1. **Push to feature branch**
2. **Create pull request**
3. **GitHub Actions runs checks** ✅
4. **Fix any issues**
5. **Merge to main**

### Ignoring Files

Add to `phpstan.neon`:
```yaml
parameters:
    excludePaths:
        - app/Legacy/*.php
        - app/ThirdParty/*.php
```

---

## 🐛 Troubleshooting

### PHPStan Out of Memory

```bash
# Increase memory limit
php artisan code:review --memory=1G

# Or in phpstan.neon
parameters:
    tmpDir: storage/phpstan
```

### Pint: "Command not found"

```bash
# Ensure vendor binaries are in PATH
export PATH="./vendor/bin:$PATH"

# Or use full path
./vendor/bin/pint
```

### False Positives in PHPStan

Add to `phpstan.neon`:
```yaml
parameters:
    ignoreErrors:
        - '#Your specific error message#'
```

---

## 📚 Resources

- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [Larastan (PHPStan for Laravel)](https://github.com/larastan/larastan)
- [Laravel Pint Documentation](https://laravel.com/docs/pint)
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)

---

## 🎯 Goals

- ✅ **Zero PHPStan errors** before merging
- ✅ **100% PSR-12 compliance** (auto-fixable)
- ✅ **No security warnings** in custom checks
- ✅ **All tests passing** in CI/CD

**Current Status**: Production-ready with Level 5 PHPStan compliance
