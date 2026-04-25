<?php
namespace App\Mail;

class Mailer
{
    private array $config;
    private array $to      = [];
    private array $cc      = [];
    private array $bcc     = [];
    private string $subject = '';
    private string $body    = '';
    private bool   $isHtml  = true;
    private array  $attachments = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public static function create(): static
    {
        return new static([
            'from_name'  => env('MAIL_FROM_NAME',    'BlogPHP'),
            'from_email' => env('MAIL_FROM_ADDRESS',  'no-reply@blogphp.com'),
            'host'       => env('MAIL_HOST',          'smtp.mailtrap.io'),
            'port'       => env('MAIL_PORT',          '587'),
            'username'   => env('MAIL_USERNAME',      ''),
            'password'   => env('MAIL_PASSWORD',      ''),
            'encryption' => env('MAIL_ENCRYPTION',    'tls'),
            'driver'     => env('MAIL_DRIVER',        'log'),
        ]);
    }

    public function to(string $email, string $name = ''): static
    {
        $this->to[] = $name ? "{$name} <{$email}>" : $email;
        return $this;
    }

    public function cc(string $email): static  { $this->cc[]  = $email; return $this; }
    public function bcc(string $email): static { $this->bcc[] = $email; return $this; }

    public function subject(string $subject): static { $this->subject = $subject; return $this; }
    public function html(string $body): static       { $this->body = $body; $this->isHtml = true;  return $this; }
    public function text(string $body): static       { $this->body = $body; $this->isHtml = false; return $this; }

    public function view(string $template, array $data = []): static
    {
        $this->body   = app('view')->render($template, $data);
        $this->isHtml = true;
        return $this;
    }

    public function attach(string $filePath, string $name = ''): static
    {
        $this->attachments[] = ['path' => $filePath, 'name' => $name ?: basename($filePath)];
        return $this;
    }

    public function send(): bool
    {
        return match($this->config['driver']) {
            'smtp' => $this->sendSmtp(),
            'log'  => $this->sendLog(),
            default => $this->sendNative(),
        };
    }

    private function sendNative(): bool
    {
        $headers  = "From: {$this->config['from_name']} <{$this->config['from_email']}>\r\n";
        $headers .= "Reply-To: {$this->config['from_email']}\r\n";
        if ($this->isHtml) $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        if ($this->cc)  $headers .= "Cc: "  . implode(', ', $this->cc)  . "\r\n";
        if ($this->bcc) $headers .= "Bcc: " . implode(', ', $this->bcc) . "\r\n";

        return mail(implode(', ', $this->to), $this->subject, $this->body, $headers);
    }

    private function sendLog(): bool
    {
        $log = sprintf(
            "[%s] EMAIL\n  To: %s\n  Subject: %s\n  Body: %s\n",
            date('Y-m-d H:i:s'),
            implode(', ', $this->to),
            $this->subject,
            substr(strip_tags($this->body), 0, 200)
        );
        log_info('Mail envoyé (log driver)', ['to' => $this->to, 'subject' => $this->subject]);
        file_put_contents(base_path('storage/logs/mail-' . date('Y-m-d') . '.log'), $log, FILE_APPEND);
        return true;
    }

    private function sendSmtp(): bool
    {
        // SMTP natif PHP via fsockopen
        try {
            $host = ($this->config['encryption'] === 'ssl' ? 'ssl://' : '') . $this->config['host'];
            $sock = fsockopen($host, (int)$this->config['port'], $errno, $errstr, 30);
            if (!$sock) throw new \RuntimeException("SMTP connexion échouée: {$errstr}");

            $this->smtpRead($sock);
            $this->smtpSend($sock, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
            $this->smtpRead($sock);

            if ($this->config['encryption'] === 'tls') {
                $this->smtpSend($sock, 'STARTTLS');
                $this->smtpRead($sock);
                stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->smtpSend($sock, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
                $this->smtpRead($sock);
            }

            $this->smtpSend($sock, 'AUTH LOGIN');
            $this->smtpRead($sock);
            $this->smtpSend($sock, base64_encode($this->config['username']));
            $this->smtpRead($sock);
            $this->smtpSend($sock, base64_encode($this->config['password']));
            $this->smtpRead($sock);

            $from = $this->config['from_email'];
            $this->smtpSend($sock, "MAIL FROM:<{$from}>");
            $this->smtpRead($sock);

            foreach ($this->to as $to) {
                preg_match('/<(.+)>/', $to, $m);
                $email = $m[1] ?? $to;
                $this->smtpSend($sock, "RCPT TO:<{$email}>");
                $this->smtpRead($sock);
            }

            $this->smtpSend($sock, 'DATA');
            $this->smtpRead($sock);

            $ct = $this->isHtml ? 'text/html' : 'text/plain';
            $headers  = "From: {$this->config['from_name']} <{$from}>\r\n";
            $headers .= "To: " . implode(', ', $this->to) . "\r\n";
            $headers .= "Subject: {$this->subject}\r\n";
            $headers .= "Content-Type: {$ct}; charset=UTF-8\r\n";
            $headers .= "MIME-Version: 1.0\r\n";

            $this->smtpSend($sock, $headers . "\r\n" . $this->body . "\r\n.");
            $this->smtpRead($sock);
            $this->smtpSend($sock, 'QUIT');
            fclose($sock);
            return true;
        } catch (\Throwable $e) {
            log_error('SMTP error: ' . $e->getMessage());
            return false;
        }
    }

    private function smtpSend($sock, string $cmd): void { fwrite($sock, $cmd . "\r\n"); }
    private function smtpRead($sock): string { return fgets($sock, 512); }
}
