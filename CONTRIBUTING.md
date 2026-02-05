# Contributing to Prophetic Life Embassy CMS

Thank you for contributing to our church management system! This guide will help you get started.

## 📚 Required Reading

Before contributing, please read:

1. **[CODING_STANDARDS.md](CODING_STANDARDS.md)** - Mandatory coding guidelines (CTO approved)
2. **[QUICK_REFERENCE.md](QUICK_REFERENCE.md)** - Quick lookup for common patterns
3. **[README.md](README.md)** - Project overview and setup

## 🚀 Getting Started

### 1. Fork & Clone

```bash
# Fork the repository on GitHub, then:
git clone https://github.com/YOUR_USERNAME/PropheticLifeEmbassyApp.git
cd PropheticLifeEmbassyApp
```

### 2. Set Up Development Environment

#### Backend
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

#### Frontend
```bash
cd frontend
npm install
cp .env.example .env.local
npm run dev
```

#### Docker (Alternative)
```bash
docker-compose up -d
```

### 3. Create a Branch

```bash
# Feature branch
git checkout -b feature/your-feature-name

# Bug fix branch
git checkout -b bugfix/issue-description
```

## 📋 Development Workflow

### 1. **Before You Code**

- [ ] Check if an issue exists for your work
- [ ] If not, create an issue describing what you'll do
- [ ] Get approval from maintainers (for features)
- [ ] Read relevant sections of [CODING_STANDARDS.md](CODING_STANDARDS.md)

### 2. **While Coding**

#### Backend Changes

```bash
# Create Form Request (REQUIRED for validation)
php artisan make:request User/StoreUserRequest

# Create Controller
php artisan make:controller UserController

# Create Model with migration
php artisan make:model User -m

# Run migrations
php artisan migrate

# Run tests
php artisan test

# Format code (REQUIRED before commit)
./vendor/bin/pint
```

#### Frontend Changes

```bash
# Create component
touch frontend/src/components/UserCard.vue

# Create store
touch frontend/src/store/user.js

# Create API client
touch frontend/src/api/users.js

# Run linter
npm run lint

# Run tests
npm run test
```

### 3. **Code Review Checklist**

#### Must-Haves ✅

- [ ] All validation in Form Request classes (NO inline validation)
- [ ] Controllers are thin (<50 lines per method)
- [ ] All database queries use Eloquent/Query Builder
- [ ] UUID primary keys with `HasUuids` trait
- [ ] API responses follow standard format
- [ ] Routes protected with `auth:sanctum`
- [ ] Tests written and passing
- [ ] Code formatted (`./vendor/bin/pint` or `npm run lint`)
- [ ] No `console.log()` or `dd()` left behind

#### Nice-to-Haves 🌟

- [ ] PHPDoc/JSDoc comments
- [ ] Error handling with user-friendly messages
- [ ] Loading states in UI
- [ ] Accessibility attributes (ARIA)

### 4. **Commit Your Changes**

```bash
# Stage changes
git add .

# Commit with conventional format
git commit -m "feat: Add user profile editing"
git commit -m "fix: Resolve attendance duplicate issue"
git commit -m "refactor: Move validation to Form Requests"

# Push to your fork
git push origin feature/your-feature-name
```

### 5. **Create Pull Request**

1. Go to GitHub and create a Pull Request
2. Fill out the PR template completely
3. Link related issues
4. Request review from maintainers
5. Address review feedback

## 🧪 Testing Requirements

### Backend Tests

```php
// Feature Test Example
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class AttendanceTest extends TestCase
{
    public function test_usher_can_record_attendance()
    {
        $usher = User::factory()->create(['role' => 'usher']);

        $response = $this->actingAs($usher)->postJson('/api/attendance', [
            'member_id' => 'mem-001',
            'service_type' => 'sunday_service',
            'service_date' => '2024-01-15',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }
}
```

**Run tests:**
```bash
php artisan test                    # All tests
php artisan test --filter=Attendance  # Specific test
```

### Frontend Tests

```javascript
// Component Test Example
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import UserCard from '@/components/UserCard.vue'

describe('UserCard', () => {
  it('displays user information', () => {
    const user = { name: 'John Doe', role: 'Admin' }
    const wrapper = mount(UserCard, { props: { user } })

    expect(wrapper.text()).toContain('John Doe')
    expect(wrapper.text()).toContain('Admin')
  })
})
```

**Run tests:**
```bash
npm run test              # All tests
npm run test:watch        # Watch mode
```

## 📝 Documentation

### When to Update Documentation

Update docs when you:
- Add a new feature
- Change API endpoints
- Modify environment variables
- Change deployment process
- Add new dependencies

### Where to Document

- **API Changes**: Update [technical-documentation.md](doc/technical-documentation.md)
- **User Features**: Update [user-stories.md](user-stories.md)
- **Database Changes**: Update [database-schema.md](database-schema.md)
- **Code Examples**: Add to [QUICK_REFERENCE.md](QUICK_REFERENCE.md)

## ❌ What NOT to Do

### Backend

```php
// ❌ DON'T: Inline validation
public function store(Request $request) {
    $request->validate([...]);
}

// ❌ DON'T: Raw SQL with user input
DB::select("SELECT * FROM users WHERE phone = '$phone'");

// ❌ DON'T: Fat controllers
public function store(Request $request) {
    // 200 lines of business logic...
}

// ❌ DON'T: Expose sensitive data
return User::all(); // Returns passwords, tokens, etc.
```

### Frontend

```javascript
// ❌ DON'T: Hardcode API URLs
axios.get('http://localhost:8000/api/users')

// ❌ DON'T: Direct axios calls
axios.get('/users') // Use API client instead

// ❌ DON'T: Ignore error handling
const data = await api.getUsers() // What if it fails?

// ❌ DON'T: Leave debug code
console.log('user data:', user)
```

### Git

```bash
# ❌ DON'T: Vague commit messages
git commit -m "updates"
git commit -m "fix"

# ❌ DON'T: Commit to main directly
git checkout main
git commit -m "quick fix"

# ❌ DON'T: Push untested code
git push origin feature/broken-code
```

## 🐛 Reporting Bugs

1. Check if the bug is already reported
2. Create a new issue using the [Bug Report template](.github/ISSUE_TEMPLATE/bug_report.md)
3. Include:
   - Clear description
   - Steps to reproduce
   - Expected vs actual behavior
   - Screenshots/error messages
   - Environment details

## 💡 Suggesting Features

1. Check if the feature is already requested
2. Create a new issue using the [Feature Request template](.github/ISSUE_TEMPLATE/feature_request.md)
3. Include:
   - Problem it solves
   - Proposed solution
   - User story
   - Acceptance criteria

## 📞 Getting Help

- **Questions**: Create a GitHub issue with `question` label
- **Chat**: Join our Slack channel (ask for invite)
- **Code Review**: Tag `@maintainers` in PR comments
- **Urgent Issues**: Contact CTO directly

## 🏆 Recognition

Contributors are recognized in:
- README.md contributors section
- Monthly team meetings
- GitHub contributor graph

## 📜 Code of Conduct

### Our Standards

- ✅ Be respectful and inclusive
- ✅ Accept constructive criticism
- ✅ Focus on what's best for the project
- ✅ Show empathy towards others

### Unacceptable Behavior

- ❌ Harassment or discriminatory language
- ❌ Personal attacks
- ❌ Publishing others' private information
- ❌ Unprofessional conduct

## 🔍 Review Process

1. **Automated Checks** (GitHub Actions)
   - Code formatting
   - Tests
   - Linting

2. **Code Review** (1+ approvals required)
   - Follows coding standards
   - Tests included
   - Documentation updated
   - No breaking changes (or approved)

3. **Merge**
   - Squash and merge (preferred)
   - Update related issues
   - Deploy to staging

## 📅 Release Cycle

- **Hotfixes**: As needed (critical bugs)
- **Minor Releases**: Every 2 weeks
- **Major Releases**: Quarterly

## ⚖️ License

By contributing, you agree that your contributions will be licensed under the same license as the project.

---

**Questions?** Check [CODING_STANDARDS.md](CODING_STANDARDS.md) or create an issue.

**Thank you for contributing!** 🙏
