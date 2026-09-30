<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Você pode adicionar lógicas de log aqui se necessário
        });

        $this->renderable(function (Throwable $e, $request) {
            // Garante que as rotas de API sempre retornem JSON
            if ($request->is('api/*')) {
                $status = 500;
                $response = [
                    'message' => 'Erro Interno do Servidor.',
                ];

                if ($e instanceof ValidationException) {
                    $status = 422;
                    $response['message'] = 'Os dados fornecidos são inválidos.';
                    $response['errors'] = $e->errors();
                } elseif ($e instanceof AuthenticationException) {
                    $status = 401;
                    $response['message'] = 'Não autenticado.';
                } elseif ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                    $status = 403;
                    $response['message'] = $e->getMessage() ?: 'Acesso negado.';
                } elseif ($e instanceof NotFoundHttpException) {
                    $status = 404;
                    $response['message'] = 'Recurso não encontrado.';
                }

                if (config('app.debug')) {
                    $response['exception'] = get_class($e);
                    $response['trace'] = $e->getMessage();
                }

                return response()->json($response, $status);
            }
        });
    }
}
