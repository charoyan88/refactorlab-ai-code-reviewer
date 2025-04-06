# Changelog

All notable changes to the `refactorlab/ai-code-reviewer` package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v1.1.0] - 2024-03-15

### Added
- Support for Laravel 12 (illuminate components ^12.0)
- Support for PHP 8.3 and 8.4
- Updated testbench to support both ^8.0 and ^9.0

## [v1.0.0] - 2023-11-30

### Added
- Initial stable release
- GitHub webhook integration for pull requests
- OpenAI-powered Laravel code review using GPT-4
- Automatic PR comments with issue details and suggestions
- Configurable file filters for including/excluding specific file types
- Customizable review settings (threshold, batch processing)
- Webhook signature verification for security 