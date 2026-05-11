<?php

namespace Ashrafic\AiOrbit\Http\Controllers;

use Ashrafic\AiOrbit\Services\ExportService;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ExportController extends Controller
{
    /**
     * Export a conversation as a Pest test file.
     */
    public function pest(string $id, ExportService $export): Response
    {
        $content = $export->toPest($id);

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="conversation_'.$id.'_test.php"',
        ]);
    }

    /**
     * Export a conversation as JSONL (OpenAI fine-tuning format).
     */
    public function json(string $id, ExportService $export): Response
    {
        $content = $export->toJson($id);

        return response($content, 200, [
            'Content-Type' => 'application/jsonl',
            'Content-Disposition' => 'attachment; filename="conversation_'.$id.'.jsonl"',
        ]);
    }
}
