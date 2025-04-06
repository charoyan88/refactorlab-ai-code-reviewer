<?php

namespace RefactorLab\AICodeReviewer;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GitHubCommenter
{
    protected $client;
    protected $config;

    /**
     * Create a new GitHubCommenter instance.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization' => 'token ' . $config['token'],
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'AI-Code-Reviewer-Bot',
            ],
        ]);
    }

    /**
     * Get the diff for a pull request.
     */
    public function getPullRequestDiff(string $repo, int $pullRequestNumber): string
    {
        try {
            $response = $this->client->request('GET', "repos/{$repo}/pulls/{$pullRequestNumber}", [
                'headers' => [
                    'Accept' => 'application/vnd.github.v3.diff',
                ],
            ]);

            return (string) $response->getBody();
        } catch (\Exception $e) {
            Log::error("Error fetching PR diff: {$e->getMessage()}");
            throw new \Exception("Error fetching PR diff: {$e->getMessage()}");
        }
    }

    /**
     * Post review comments to a pull request.
     */
    public function postComments(string $repo, int $prNumber, array $reviewData): array
    {
        $comments = $reviewData['comments'] ?? [];
        $error = $reviewData['error'] ?? null;

        if ($error) {
            Log::warning("Warning while posting comments: {$error}");
        }

        if (empty($comments)) {
            Log::info("No comments to post for PR #{$prNumber} in {$repo}");
            return [];
        }

        $reviewComments = [];
        foreach ($comments as $comment) {
            try {
                $reviewComments[] = $this->createReviewComment($repo, $prNumber, $comment);
            } catch (\Exception $e) {
                Log::error("Error posting comment: {$e->getMessage()}");
            }
        }

        return $reviewComments;
    }

    /**
     * Create a review comment on a specific line.
     */
    protected function createReviewComment(string $repo, int $prNumber, array $comment): array
    {
        // Skip comments without a file or line
        if (empty($comment['file']) || $comment['line'] === null) {
            // Create a general PR comment instead
            return $this->createPullRequestComment($repo, $prNumber, $comment);
        }

        $severity = $comment['severity'] ?? 'info';
        $suggestion = $comment['suggestion'] ?? '';
        
        $body = "**{$severity}**: {$comment['description']}";
        if (!empty($suggestion)) {
            $body .= "\n\n**Suggestion**: {$suggestion}";
        }
        
        try {
            // First, we need to get the commit ID
            $prInfo = $this->getPullRequestInfo($repo, $prNumber);
            $commitId = $prInfo['head']['sha'] ?? null;
            
            if (!$commitId) {
                throw new \Exception("Could not determine the commit ID");
            }
            
            // Create a review comment
            $response = $this->client->request('POST', "repos/{$repo}/pulls/{$prNumber}/comments", [
                'json' => [
                    'commit_id' => $commitId,
                    'path' => $comment['file'],
                    'line' => $comment['line'],
                    'body' => $body,
                ],
            ]);
            
            return json_decode((string) $response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("Error creating review comment: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Create a general pull request comment.
     */
    protected function createPullRequestComment(string $repo, int $prNumber, array $comment): array
    {
        $body = "**AI Code Review**: {$comment['description']}";
        
        try {
            $response = $this->client->request('POST', "repos/{$repo}/issues/{$prNumber}/comments", [
                'json' => [
                    'body' => $body,
                ],
            ]);
            
            return json_decode((string) $response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("Error creating PR comment: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Get pull request information.
     */
    protected function getPullRequestInfo(string $repo, int $prNumber): array
    {
        try {
            $response = $this->client->request('GET', "repos/{$repo}/pulls/{$prNumber}");
            return json_decode((string) $response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("Error fetching PR info: {$e->getMessage()}");
            throw new \Exception("Error fetching PR info: {$e->getMessage()}");
        }
    }
} 