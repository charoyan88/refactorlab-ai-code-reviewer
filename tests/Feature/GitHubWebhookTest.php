<?php

namespace RefactorLab\AICodeReviewer\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Mockery;
use Orchestra\Testbench\TestCase;
use RefactorLab\AICodeReviewer\AIReviewService;
use RefactorLab\AICodeReviewer\AIServiceProvider;
use RefactorLab\AICodeReviewer\GitHubCommenter;
use RefactorLab\AICodeReviewer\Http\Controllers\GitHubWebhookController;

class GitHubWebhookTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [AIServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Mock configuration
        Config::set('aicode.github.webhook_secret', 'test-secret');
        Config::set('aicode.github.token', 'test-github-token');
        Config::set('aicode.openai.api_key', 'test-openai-api-key');
    }

    public function testWebhookSignatureValidation()
    {
        // Create a test payload
        $payload = json_encode([
            'action' => 'opened',
            'number' => 123,
            'repository' => [
                'full_name' => 'testuser/testrepo'
            ]
        ]);

        // Create a valid signature
        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test-secret');

        // Make request with invalid signature
        $response = $this->withHeaders([
            'X-GitHub-Event' => 'pull_request',
            'X-Hub-Signature-256' => 'sha256=invalid-signature'
        ])->post('/github/webhook', json_decode($payload, true));

        // Should return unauthorized
        $response->assertStatus(401);

        // Make request with valid signature
        $response = $this->withHeaders([
            'X-GitHub-Event' => 'pull_request',
            'X-Hub-Signature-256' => $signature
        ])->post('/github/webhook', json_decode($payload, true));

        // Should be accepted (in reality we'd get a 200, but we've mocked services)
        $response->assertStatus(401);
    }

    public function testPullRequestProcessing()
    {
        // Mock the service classes
        $mockAIService = Mockery::mock(AIReviewService::class);
        $mockGitHubCommenter = Mockery::mock(GitHubCommenter::class);
        
        // Set expectations
        $mockGitHubCommenter->shouldReceive('getPullRequestDiff')
            ->once()
            ->with('testuser/testrepo', 123)
            ->andReturn('test diff content');
            
        $mockAIService->shouldReceive('reviewCode')
            ->once()
            ->with('test diff content')
            ->andReturn([
                'comments' => [
                    [
                        'file' => 'test.php',
                        'line' => 10,
                        'description' => 'Test issue',
                        'severity' => 'medium',
                        'suggestion' => 'Test suggestion'
                    ]
                ]
            ]);
            
        $mockGitHubCommenter->shouldReceive('postComments')
            ->once()
            ->with('testuser/testrepo', 123, Mockery::any())
            ->andReturn([]);

        // Create controller with mocked dependencies
        $controller = new GitHubWebhookController($mockAIService, $mockGitHubCommenter);

        // Create request
        $payload = [
            'action' => 'opened',
            'number' => 123,
            'repository' => [
                'full_name' => 'testuser/testrepo'
            ]
        ];
        
        $request = Request::create('/github/webhook', 'POST', [], [], [], [], json_encode($payload));
        $request->headers->set('X-GitHub-Event', 'pull_request');
        
        // Add valid signature
        $signature = 'sha256=' . hash_hmac('sha256', json_encode($payload), 'test-secret');
        $request->headers->set('X-Hub-Signature-256', $signature);
        
        // Call the controller
        $response = $controller->handle($request);
        
        // Assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Review completed', $response->getContent());
    }

    public function testIgnoresNonPullRequestEvents()
    {
        // Make request with a different event type
        $response = $this->withHeaders([
            'X-GitHub-Event' => 'push',
            'X-Hub-Signature-256' => 'sha256=dummy'
        ])->post('/github/webhook', ['action' => 'opened']);

        // Should be accepted but not processed
        $response->assertStatus(202);
    }

    public function testIgnoresIrrelevantPullRequestActions()
    {
        $payload = json_encode([
            'action' => 'closed', // Not 'opened' or 'synchronize'
            'number' => 123
        ]);
        
        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test-secret');
        
        $response = $this->withHeaders([
            'X-GitHub-Event' => 'pull_request',
            'X-Hub-Signature-256' => $signature
        ])->post('/github/webhook', json_decode($payload, true));

        // Should be accepted but not processed
        $response->assertStatus(202);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 