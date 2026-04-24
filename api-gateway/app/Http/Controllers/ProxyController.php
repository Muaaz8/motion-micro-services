<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProxyController extends Controller
{
    public function forward(Request $request, string $path = '')
    {
        $service = $request->route('service');

        $baseUrl = config("services.{$service}.url");

        if (!$baseUrl) {
            return response()->json(['message' => "Unknown service: {$service}"], 404);
        }

        // ← forward the full original path, not just the captured segment
        $url = rtrim($baseUrl, '/') . '/' . ltrim($request->path(), '/');

        if ($request->getQueryString()) {
            $url .= '?' . $request->getQueryString();
        }

        $response = Http::withHeaders($this->forwardHeaders($request))
            ->withBody($request->getContent(), $request->header('Content-Type', 'application/json'))
            ->{strtolower($request->method())}($url);

        return response($response->body(), $response->status())
            ->withHeaders($this->filterResponseHeaders($response->headers()));
    }

    private function forwardHeaders(Request $request): array
    {
        // Forward all headers except host
        return collect($request->headers->all())
            ->except(['host'])
            ->map(fn($value) => $value[0])
            ->toArray();
    }

    private function filterResponseHeaders(array $headers): array
    {
        // Strip headers that shouldn't be forwarded back to client
        $skip = ['transfer-encoding', 'connection', 'keep-alive'];

        return collect($headers)
            ->except($skip)
            ->map(fn($value) => is_array($value) ? $value[0] : $value)
            ->toArray();
    }
}
