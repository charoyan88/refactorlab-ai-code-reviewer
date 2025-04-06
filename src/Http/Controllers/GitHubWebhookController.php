<?php

namespace RefactorLab\AICodeReviewer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use RefactorLab\AICodeReviewer\AIReviewService;
use RefactorLab\AICodeReviewer\GitHubCommenter;

class GitHubWebhookController extends Controller
{
    protected $aiReviewService;
    protected $gitHubCommenter;

    public function __construct(AIReviewService $aiReviewService, GitHubCommenter $gitHubCommenter)
    {
        $this->aiReviewService = $aiReviewService;
        $this->gitHubCommenter = $gitHubCommenter;
    }

    /**
     * Handle the GitHub webhook event.
     */
    public function handle(Request $request): Response
    {
        // Validate webhook signature
        if (!$this->validateSignature($request)) {
            Log::warning('Invalid GitHub webhook signature');
            return response('Unauthorized', 401);
        }

        // Get the event type
        $githubEvent = $request->header('X-GitHub-Event');
        
        // Only process pull request events
        if ($githubEvent !== 'pull_request') {
            return response('Event not processed', 202);
        }

        $payload = $request->json()->all();
        $action = $payload['action'] ?? null;

        // Only process opened or synchronize events
        if (!in_array($action, ['opened', 'synchronize'])) {
            return response('PR action not processed', 202);
        }

        try {
            // Extract PR information
            $prNumber = $payload['number'] ?? null;
            $repoFullName = $payload['repository']['full_name'] ?? null;
            
            if (!$prNumber || !$repoFullName) {
                return response('Missing PR information', 400);
            }

            // Get PR diff
            $diff = $this->gitHubCommenter->getPullRequestDiff($repoFullName, $prNumber);
            
            // Filter diff based on configuration
            $filteredDiff = $this->filterDiff($diff);
            
            // Process diff with AI
            $reviewComments = $this->aiReviewService->reviewCode($filteredDiff);
            
            // Post comments to GitHub
            $this->gitHubCommenter->postComments($repoFullName, $prNumber, $reviewComments);

            return response('Review completed', 200);
        } catch (\Exception $e) {
            Log::error('Error processing PR review: ' . $e->getMessage());
            return response('Error processing review', 500);
        }
    }

    /**
     * Validate the webhook signature.
     */
    protected function validateSignature(Request $request): bool
    {
        $signature = $request->header('X-Hub-Signature-256');
        if (!$signature) {
            return false;
        }

        $secret = config('aicode.github.webhook_secret');
        $payload = $request->getContent();
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Filter the diff based on configuration.
     */
    protected function filterDiff(string $diff): string
    {
        $filters = config('aicode.file_filters');
        $includes = $filters['include'] ?? [];
        $excludes = $filters['exclude'] ?? [];
        
        // Apply filtering logic here
        // For simplicity, we're returning the original diff
        // In a real implementation, you would parse and filter the diff
        
        return $diff;
    }
} 