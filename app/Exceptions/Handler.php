<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     * Garantiza que todos los errores de la API se devuelvan en JSON uniforme.
     */
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {

            // Error de validación (Form Request o ValidationException del Service)
            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los datos enviados no son válidos.',
                    'errors'  => $e->errors(),
                ], 422);
            }

            // Modelo no encontrado (findOrFail o Route Model Binding)
            if ($e instanceof ModelNotFoundException) {
                $model = class_basename($e->getModel());

                return response()->json([
                    'success' => false,
                    'message' => "{$model} no encontrado.",
                ], 404);
            }

            // Ruta no encontrada
            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint no encontrado.',
                ], 404);
            }

            // Cualquier otro error inesperado
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        return parent::render($request, $e);
    }
}
