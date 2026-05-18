<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    protected function getWebUser(): ?User
    {
        return auth()->user();
    }

    protected function getApiUser(Request $request): ?User
    {
        return $request->user();
    }

    protected function webRedirectLogin(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('login')->with('error', 'Please login to continue.');
    }

    protected function webRedirect(\Illuminate\Http\RedirectResponse $response, ?User $user): \Illuminate\Http\RedirectResponse
    {
        if (! $user) {
            return $this->webRedirectLogin();
        }

        return $response;
    }

    protected function jsonSuccess(string $message, $data = null, int $code = 200): \Illuminate\Http\JsonResponse
    {
        $response = ['message' => $message, 'status' => 'success'];
        if (! is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    protected function jsonError(string $message, int $code = 400, $errors = null): \Illuminate\Http\JsonResponse
    {
        $response = ['message' => $message, 'status' => 'error'];
        if (! is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    protected function jsonPaginated(string $message, $paginator): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => 'success',
            'data' => [
                'items' => $paginator->items(),
                'meta' => [
                    'total' => $paginator->total(),
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
        ]);
    }
}
