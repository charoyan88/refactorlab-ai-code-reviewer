# AI Code Reviewer v1.0.0

[![Latest Version on Packagist](https://img.shields.io/packagist/v/refactorlab/ai-code-reviewer.svg?style=flat-square)](https://packagist.org/packages/refactorlab/ai-code-reviewer)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/charoyan88/refactorlab-ai-code-reviewer/tests?label=tests)](https://github.com/charoyan88/refactorlab-ai-code-reviewer/actions?query=workflow%3Atests+branch%3Amain)
[![License](https://img.shields.io/github/license/charoyan88/refactorlab-ai-code-reviewer?style=flat-square)](LICENSE.md)

## Introduction

AI Code Reviewer is a powerful Laravel package that automatically analyzes GitHub pull requests using OpenAI's GPT-4. It helps improve code quality by identifying potential issues, bugs, and suggesting improvements - all without manual intervention.

The package listens for GitHub webhook events when pull requests are opened or updated, extracts the code changes, sends them to OpenAI for analysis, and then posts the AI-generated review comments directly back to the pull request on GitHub.

This intelligent code review assistant saves developer time, helps maintain coding standards, and can catch issues that might be missed during human review.

## Features

- 🤖 Listens to GitHub pull request webhooks
- 🔍 Sends code diffs to OpenAI for analysis
- 💬 Posts AI-generated review comments back to the pull request
- ⚙️ Configurable file filters and settings
- 🔒 Secure webhook handling with signature verification
- 🔄 Batch processing for large pull requests
- 🧠 Customizable AI prompts and response handling
- 🛠️ Extensive configuration options

## Installation

You can install the package via composer:

```bash
composer require refactorlab/ai-code-reviewer:^1.0
```

The package will automatically register its service provider with Laravel's auto-discovery.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=aicode-config
```

This will create a `config/aicode.php` file where you can configure the package.

### Required Environment Variables

Add the following to your `.env` file:

```
OPENAI_API_KEY=your-openai-api-key
GITHUB_API_TOKEN=your-github-token-with-repo-access
GITHUB_WEBHOOK_SECRET=your-webhook-secret
```

- `OPENAI_API_KEY`: Your OpenAI API key (requires access to GPT-4)
- `GITHUB_API_TOKEN`: A GitHub personal access token with `repo` scope
- `GITHUB_WEBHOOK_SECRET`: A secret string to verify webhook requests

## Detailed Configuration

The `config/aicode.php` file contains the following sections:

### OpenAI Configuration

```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-4-turbo'),
    'temperature' => env('OPENAI_TEMPERATURE', 0.1),
    'max_tokens' => env('OPENAI_MAX_TOKENS', 4000),
],
```

- `model`: The OpenAI model to use (default: gpt-4-turbo)
- `temperature`: Controls randomness (0-1, lower = more deterministic)
- `max_tokens`: Maximum token limit for responses

### GitHub Configuration

```php
'github' => [
    'token' => env('GITHUB_API_TOKEN'),
    'webhook_secret' => env('GITHUB_WEBHOOK_SECRET'),
],
```

### File Filters

```php
'file_filters' => [
    'include' => [
        '*.php',
        '*.js',
        // etc.
    ],
    'exclude' => [
        'vendor/*',
        'node_modules/*',
        // etc.
    ],
],
```

### Review Settings

```php
'settings' => [
    'max_files_per_review' => env('MAX_FILES_PER_REVIEW', 10),
    'max_diff_size' => env('MAX_DIFF_SIZE', 5000),
    'comment_threshold' => env('COMMENT_THRESHOLD', 0.7),
    'batch_processing' => env('BATCH_PROCESSING', true),
],
```

## Usage

### Setting up the GitHub Webhook

1. Go to your GitHub repository's settings
2. Click on "Webhooks" → "Add webhook"
3. Set the Payload URL to:
   ```
   https://your-app.com/github/webhook
   ```
4. Select content type: `application/json`
5. Enter your webhook secret (same as `GITHUB_WEBHOOK_SECRET`)
6. Under "Which events would you like to trigger this webhook?", select "Pull requests"
7. Ensure "Active" is checked and click "Add webhook"

Once set up, the AI Code Reviewer will automatically analyze new pull requests and post its review as comments.

### Example GitHub PR Comments

When the AI Code Reviewer analyzes a pull request, it will post comments like these:

#### Example 1: Bug Detection

```
**High**: Potential null reference exception in UserController.php on line 42.

The `$user` variable might be null if the user is not found, but it's being accessed without a null check.

**Suggestion**: Add a null check before accessing properties:
```php
if ($user !== null) {
    $user->update($request->validated());
}
```
```

#### Example 2: Code Improvement

```
**Medium**: Inefficient database query in ProductService.php on line 78.

The current implementation runs a separate query for each product, which could lead to N+1 query issues.

**Suggestion**: Use eager loading with the `with()` method:
```php
return Product::with('category', 'tags')->get();
```
```

#### Example 3: Security Issue

```
**High**: SQL Injection vulnerability in ReportController.php on line 126.

Raw user input is being directly used in a database query.

**Suggestion**: Use query bindings instead:
```php
DB::select("SELECT * FROM reports WHERE status = ?", [$request->status]);
```
```

## Testing

```bash
composer test
```

## Security

If you discover any security issues, please contact the author directly instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently. 