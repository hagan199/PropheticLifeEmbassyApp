## Description
<!-- Brief description of the changes in this PR -->


## Type of Change
<!-- Mark the relevant option with an [x] -->

- [ ] 🐛 Bug fix (non-breaking change that fixes an issue)
- [ ] ✨ New feature (non-breaking change that adds functionality)
- [ ] 💥 Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] 📚 Documentation update
- [ ] ♻️ Refactoring (no functional changes)
- [ ] 🧪 Test updates

## Related Issue
<!-- Link to the issue this PR addresses -->
Closes #

## Changes Made
<!-- List the main changes -->
-
-
-

## Screenshots (if applicable)
<!-- Add screenshots for UI changes -->


## Checklist
<!-- Ensure all items are checked before requesting review -->

### Code Quality
- [ ] Code follows the [Coding Standards](../CODING_STANDARDS.md)
- [ ] All validation uses Form Request classes (NO inline validation)
- [ ] Controllers are thin (business logic delegated to services)
- [ ] Proper error handling implemented
- [ ] No sensitive data exposed in API responses
- [ ] No hardcoded values (using env variables)
- [ ] No `console.log()` or `dd()` left in code
- [ ] Code is self-documenting with clear naming

### Backend (if applicable)
- [ ] All models use UUID primary keys with `HasUuids` trait
- [ ] Database migrations include proper indexes
- [ ] Foreign key constraints added
- [ ] API responses follow standard format
- [ ] Routes protected with `auth:sanctum` middleware
- [ ] PHPDoc comments added for public methods
- [ ] Using Eloquent/Query Builder (no raw SQL)

### Frontend (if applicable)
- [ ] Components follow Composition API pattern
- [ ] API calls through centralized API client
- [ ] Loading and error states handled
- [ ] Proper prop validation
- [ ] Styles are scoped
- [ ] Accessible (ARIA attributes where needed)

### Testing
- [ ] Added/updated unit tests
- [ ] Added/updated feature tests
- [ ] All tests passing locally (`php artisan test` / `npm run test`)
- [ ] Manual testing completed

### Documentation
- [ ] Code comments added for complex logic
- [ ] API documentation updated (if applicable)
- [ ] README updated (if needed)

### Database (if applicable)
- [ ] Migrations tested (up and down)
- [ ] Seeders updated (if needed)
- [ ] No data loss in migrations

## Testing Instructions
<!-- How should reviewers test this PR? -->

1.
2.
3.

## Deployment Notes
<!-- Any special deployment steps or environment variable changes? -->


## Reviewer Notes
<!-- Anything specific you want reviewers to focus on? -->


---

**Before submitting:**
1. Read the [Coding Standards](../CODING_STANDARDS.md)
2. Run `./vendor/bin/pint` (backend) and `npm run lint` (frontend)
3. Ensure all tests pass
4. Verify no merge conflicts with main branch
