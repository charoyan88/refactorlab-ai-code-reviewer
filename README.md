# AI Code Reviewer v1.0.0

A Laravel package that automatically reviews GitHub pull requests using OpenAI's GPT-4. The AI Code Reviewer analyzes code changes and provides intelligent feedback, making your code review process more efficient.

## Features

- 🤖 Listens to GitHub pull request webhooks
- 🔍 Sends code diffs to OpenAI for analysis
- 💬 Posts AI-generated review comments back to the pull request
- ⚙️ Configurable file filters and review settings
- 🔒 Secure webhook handling with signature verification
- 🔄 Batch processing for large pull requests

## Installation

You can install the package via composer:

```bash
composer require refactorlab/ai-code-reviewer:^1.0
```

The package will automatically register its service provider.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=aicode-config
```

Then, update your `.env` file with your API keys:

```
OPENAI_API_KEY=your-openai-api-key
GITHUB_API_TOKEN=your-github-token
GITHUB_WEBHOOK_SECRET=your-webhook-secret
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

## Configuration Options

The package comes with several configuration options in `config/aicode.php`:

- **OpenAI Settings**: API key, model, temperature, and token limits
- **GitHub Settings**: API token and webhook secret
- **File Filters**: Include or exclude specific file types from review
- **Review Settings**: Control review behavior, comment thresholds, and more

## Security

If you discover any security issues, please contact the author directly instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently. 