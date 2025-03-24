<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\WatsonService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected $watsonService;

    public function __construct(WatsonService $watsonService)
    {
        $this->watsonService = $watsonService;
    }

    public function createSession(): JsonResponse
    {
        try {
            $response = $this->watsonService->createSession();

            return response()->json([
                'success' => true,
                'sessionId' => $response['session_id'],
                'message' => 'Hello! I am Chatbot AI, an artificial intelligence model designed to help you with information, questions or tasks you need. How can I help you today?'
            ]);
        } catch (\Exception $e) {
            Log::error('Watson Session Creation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create chat session'
            ], 500);
        }
    }

    public function sendMessage(Request $request, string $sessionId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string'
            ]);

            $response = $this->watsonService->sendMessage($sessionId, $validated['message']);

            $botMessage = $response['output']['generic'][0]['text'] ??
                "I'm afraid I don't understand. Please rephrase your question.";

            return response()->json([
                'success' => true,
                'message' => $botMessage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process message'
            ], 500);
        }
    }
}
