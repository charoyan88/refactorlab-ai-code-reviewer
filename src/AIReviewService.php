<?php

namespace RefactorLab\AICodeReviewer;

use OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class AIReviewService
{
    protected $client;
    protected $config;

    /**
     * Create a new AIReviewService instance.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = OpenAI::client($config['api_key']);
    }

    /**
     * Review code changes and return suggested comments.
     */
    public function reviewCode(string $diff): array
    {
        // Check if diff is too large
        $maxDiffSize = config('aicode.settings.max_diff_size', 5000);
        if (strlen($diff) > $maxDiffSize * 100) { // rough estimation
            Log::warning('Diff too large for review');
            return [
                'error' => 'Diff too large for review',
                'comments' => [],
            ];
        }

        try {
            $response = $this->client->chat()->create([
                'model' => $this->config['model'],
                'temperature' => $this->config['temperature'],
                'max_tokens' => $this->config['max_tokens'],
                'messages' => $this->buildPrompt($diff),
            ]);

            return $this->parseResponse($response->toArray());
        } catch (\Exception $e) {
            Log::error('OpenAI API error: ' . $e->getMessage());
            return [
                'error' => 'OpenAI API error: ' . $e->getMessage(),
                'comments' => [],
            ];
        }
    }

    /**
     * Build the prompt for the AI model.
     */
    protected function buildPrompt(string $diff): array
    {
        return [
            [
                'role' => 'system',
                'content' => 'You are an expert code reviewer. You analyze code diffs and provide helpful feedback, ' .
                            'suggestions for improvements, and identify potential bugs or issues. ' .
                            'For each issue identified, provide: 1) the file name, 2) the line number(s), 3) a clear description of the issue, ' .
                            '4) the severity (Low, Medium, High), and 5) a suggested fix if applicable. ' .
                            'Format each issue as a JSON object within a JSON array. ' .
                            'Focus on important issues and avoid nitpicking.'
            ],
            [
                'role' => 'user',
                'content' => "Review the following code diff and provide feedback:\n\n```\n$diff\n```"
            ]
        ];
    }

    /**
     * Parse the API response into structured comments.
     */
    protected function parseResponse(array $response): array
    {
        $content = $response['choices'][0]['message']['content'] ?? '';
        
        // Try to extract JSON from the response
        preg_match('/\[[\s\S]*\]/m', $content, $matches);
        
        if (!empty($matches)) {
            try {
                $comments = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($comments)) {
                    return [
                        'error' => null,
                        'comments' => $this->filterComments($comments),
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Error parsing AI response: ' . $e->getMessage());
            }
        }
        
        // If we couldn't parse JSON, try to extract comments manually
        return [
            'error' => 'Could not parse structured comments from AI response',
            'comments' => $this->fallbackParsing($content),
        ];
    }

    /**
     * Filter comments based on threshold and other criteria.
     */
    protected function filterComments(array $comments): array
    {
        $threshold = config('aicode.settings.comment_threshold', 0.7);
        
        return collect($comments)
            ->filter(function ($comment) use ($threshold) {
                // Here you would normally filter based on some confidence score
                // Since our mock API doesn't provide that, we're keeping all comments
                return true;
            })
            ->toArray();
    }

    /**
     * Fallback parsing for when JSON extraction fails.
     */
    protected function fallbackParsing(string $content): array
    {
        // Basic fallback that returns the entire content as a single comment
        // In a real implementation, you'd use regex or other parsing to extract structured information
        return [
            [
                'file' => 'unknown',
                'line' => null,
                'description' => 'AI Review (unparsed): ' . $content,
                'severity' => 'info',
                'suggestion' => null,
            ]
        ];
    }
} 