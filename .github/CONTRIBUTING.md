# Contributing to AI Code Reviewer

First off, thank you for considering contributing to AI Code Reviewer! It's people like you that make this package better for everyone.

## Code of Conduct

This project and everyone participating in it is governed by our [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues as you might find that you don't need to create one. When you are creating a bug report, please include as many details as possible:

* Use a clear and descriptive title
* Describe the exact steps to reproduce the problem
* Provide specific examples to demonstrate the steps
* Describe the behavior you observed after following the steps
* Explain which behavior you expected to see instead and why
* Include error messages and stack traces if any

### Suggesting Enhancements

If you have a suggestion for the package, we'd love to hear about it. Before creating enhancement suggestions, please check the existing issues and pull requests as you might find out that you don't need to create one.

When you are creating an enhancement suggestion, please include:

* A clear and descriptive title
* A detailed description of the proposed functionality
* An explanation of why this enhancement would be useful
* Possible implementation details if you have them

### Pull Requests

1. Fork the repository and create your branch from `main`
2. If you've added code that should be tested, add tests
3. Ensure the test suite passes
4. Update the documentation
5. Add a note to the CHANGELOG.md under "Unreleased" section
6. Issue the pull request

## Development Setup

1. Clone your fork of the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Run the tests:
   ```bash
   composer test
   ```

## Styleguides

### Git Commit Messages

* Use the present tense ("Add feature" not "Added feature")
* Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
* Limit the first line to 72 characters or less
* Reference issues and pull requests liberally after the first line

### PHP Styleguide

* Follow PSR-12 coding standards
* Write documentation for all public methods
* Add type hints where possible
* Keep methods focused and small

### Documentation Styleguide

* Use Markdown
* Reference code using backticks
* Include code examples when relevant
* Keep explanations clear and concise

## Additional Notes

### Issue and Pull Request Labels

* `bug` - Something isn't working
* `enhancement` - New feature or request
* `documentation` - Improvements or additions to documentation
* `help wanted` - Extra attention is needed
* `good first issue` - Good for newcomers

## Questions?

Don't hesitate to reach out if you have questions. You can:

* Open an issue
* Contact us at hello@refactorlab.dev
* Join our community discussions (if enabled) 