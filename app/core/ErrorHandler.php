<?php

namespace App\Core;

class ErrorHandler
{
    private string $environment;

    public function __construct(string $environment = 'development')
    {
        $this->environment = $environment;

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public function handleException(\Throwable $e): void
    {
        $this->logError($e);

        if ($this->environment === 'development') {
            $this->renderDevelopmentError($e);
        } else {
            http_response_code(500);
            try {
                $pageTitle = 'حدث خطأ';
                $activePage = '';
                $content = $this->getProductionErrorContent();
                require ROOT_PATH . '/app/views/layouts/main.php';
            } catch (\Throwable $layoutError) {
                $this->renderMinimalErrorPage();
            }
        }
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleException(new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    private function logError(\Throwable $e): void
    {
        try {
            $model = new \App\Models\ErrorLogModel();
            $model->logError([
                'error_level' => $this->getErrorLevel($e),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'request_url' => $_SERVER['REQUEST_URI'] ?? '',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Throwable $dbError) {
            $logMessage = sprintf(
                "[%s] %s: %s in %s:%d\nStack trace:\n%s\nRequest: %s %s\n",
                date('Y-m-d H:i:s'),
                $this->getErrorLevel($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString(),
                $_SERVER['REQUEST_METHOD'] ?? '',
                $_SERVER['REQUEST_URI'] ?? ''
            );
            error_log($logMessage, 3, ROOT_PATH . '/storage/logs/error.log');
        }
    }

    private function getErrorLevel(\Throwable $e): string
    {
        if ($e instanceof \ErrorException) {
            $severity = $e->getSeverity();
            return match ($severity) {
                E_ERROR, E_USER_ERROR => 'error',
                E_WARNING, E_USER_WARNING => 'warning',
                E_NOTICE, E_USER_NOTICE => 'notice',
                E_DEPRECATED, E_USER_DEPRECATED => 'notice',
                default => 'error',
            };
        }
        return 'error';
    }

    private function renderDevelopmentError(\Throwable $e): void
    {
        http_response_code(500);
        $errorClass = get_class($e);
        $errorMessage = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        $errorFile = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
        $errorLine = $e->getLine();
        $stackTrace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');
        $requestUrl = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '', ENT_QUOTES, 'UTF-8');
        $requestMethod = htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? '', ENT_QUOTES, 'UTF-8');

        echo <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطأ في التطوير</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6" dir="ltr">
    <div class="max-w-4xl mx-auto">
        <div class="bg-red-600 text-white p-4 rounded-t-lg">
            <h1 class="text-xl font-bold">{$errorClass}</h1>
            <p class="mt-1">{$errorMessage}</p>
        </div>
        <div class="bg-gray-900 text-green-400 p-4 rounded-b-lg font-mono text-sm">
            <p>{$errorFile}:{$errorLine}</p>
        </div>
        <div class="bg-white rounded-lg shadow mt-6 p-4">
            <h2 class="font-bold text-gray-800 mb-2">Stack Trace</h2>
            <pre class="bg-gray-100 p-4 rounded text-sm overflow-x-auto">{$stackTrace}</pre>
        </div>
        <div class="bg-white rounded-lg shadow mt-4 p-4">
            <h2 class="font-bold text-gray-800 mb-2">Request Info</h2>
            <p class="text-sm"><strong>Method:</strong> {$requestMethod}</p>
            <p class="text-sm"><strong>URL:</strong> {$requestUrl}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getProductionErrorContent(): string
    {
        ob_start();
        require ROOT_PATH . '/app/views/errors/500.php';
        return ob_get_clean();
    }

    private function renderMinimalErrorPage(): void
    {
        http_response_code(500);
        $basePath = $GLOBALS['app_config']['base_path'] ?? '';
        echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>حدث خطأ</title></head><body style="display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:sans-serif;background:#f3f4f6"><div style="text-align:center"><p style="font-size:2rem;font-weight:bold;color:#111">حدث خطأ</p><p style="color:#6b7280">عذراً، حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى لاحقاً</p><a href="' . $basePath . '/" style="display:inline-block;margin-top:1rem;background:#F4C400;color:#111;padding:0.5rem 1.5rem;border-radius:0.25rem;text-decoration:none;font-weight:bold">العودة للرئيسية</a></div></body></html>';
    }
}