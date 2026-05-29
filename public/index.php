<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// ── Headers globales ───────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Para pruebas con Postman (quitar en producción):
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Cargar config ──────────────────────────────────────────────
$config = require dirname(__DIR__) . '/config/config.php';

// ── Manejo global de excepciones ───────────────────────────────
set_exception_handler(function (\Throwable $e) use ($config) {
    $status = method_exists($e, 'getHttpStatus') ? $e->getHttpStatus() : 500;
    http_response_code($status);

    $response = ['success' => false, 'message' => $e->getMessage()];

    if ($config['app']['debug']) {
        $response['debug'] = [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
});

// ── Instanciar dependencias (DI manual) ────────────────────────
use App\Http\Controllers\PaymentController;
use App\Models\Payment;
use App\Router;
use App\Services\CulqiService;
use App\Validators\PaymentValidator;

$payment    = new Payment($config['db']);
$culqi      = new CulqiService($config['culqi']);
$validator  = new PaymentValidator();
$controller = new PaymentController($culqi, $payment, $validator);

// ── Rutas ──────────────────────────────────────────────────────
$router = new Router();

$router->get('/health',          fn()           => $controller->health());
$router->post('/payment/charge', fn()           => $controller->charge());
$router->post('/payment/yape',   fn()           => $controller->yapeCharge());
$router->get('/payment/{id}',    fn(string $id) => $controller->show($id));
$router->dispatch();