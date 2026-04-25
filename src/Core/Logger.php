<?php
namespace App\Core;

class Logger
{
    private string $logFile;

    public function __construct(string $logPath)
    {
        if (!is_dir($logPath)) mkdir($logPath, 0755, true);
        $this->logFile = $logPath . '/app-' . date('Y-m-d') . '.log';
    }

    public function info(string $message, array $context = []): void  { $this->write('INFO',  $message, $context); }
    public function error(string $message, array $context = []): void { $this->write('ERROR', $message, $context); }
    public function debug(string $message, array $context = []): void { $this->write('DEBUG', $message, $context); }

    private function write(string $level, string $message, array $context): void
    {
        $line = sprintf("[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'), $level, $message,
            $context ? json_encode($context) : ''
        );
        file_put_contents($this->logFile, $line, FILE_APPEND);
    }
}
