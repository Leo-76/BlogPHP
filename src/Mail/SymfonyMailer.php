<?php
namespace App\Mail;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer as SfMailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

/**
 * Mailer basé sur symfony/mailer
 */
class SymfonyMailer
{
    private Email $email;
    private SfMailer $mailer;

    public function __construct()
    {
        $this->email = new Email();
        $this->email->from(new Address(
            env('MAIL_FROM_ADDRESS', 'no-reply@blogphp.com'),
            env('MAIL_FROM_NAME', 'BlogPHP')
        ));

        $dsn = $this->buildDsn();
        $transport    = Transport::fromDsn($dsn);
        $this->mailer = new SfMailer($transport);
    }

    private function buildDsn(): string
    {
        return match(env('MAIL_DRIVER', 'log')) {
            'smtp'    => sprintf('smtp://%s:%s@%s:%s',
                env('MAIL_USERNAME', ''),
                env('MAIL_PASSWORD', ''),
                env('MAIL_HOST', 'localhost'),
                env('MAIL_PORT', 587)
            ),
            'sendmail' => 'sendmail://default',
            default    => 'null://null', // log driver — n'envoie pas
        };
    }

    public function to(string $email, string $name = ''): static
    {
        $this->email->to($name ? new Address($email, $name) : $email);
        return $this;
    }

    public function cc(string $email): static   { $this->email->cc($email); return $this; }
    public function bcc(string $email): static  { $this->email->bcc($email); return $this; }
    public function subject(string $s): static  { $this->email->subject($s); return $this; }
    public function html(string $body): static  { $this->email->html($body); return $this; }
    public function text(string $body): static  { $this->email->text($body); return $this; }
    public function attach(string $path): static { $this->email->attachFromPath($path); return $this; }

    public function view(string $template, array $data = []): static
    {
        $this->email->html(app('view')->render($template, $data));
        return $this;
    }

    public function send(): bool
    {
        try {
            // Log dans tous les cas
            log_info('Mail envoyé', [
                'to'      => (string)$this->email->getTo()[0] ?? '',
                'subject' => $this->email->getSubject(),
                'driver'  => env('MAIL_DRIVER', 'log'),
            ]);

            // Log driver : écrire dans fichier log
            if (env('MAIL_DRIVER', 'log') === 'log') {
                $log = sprintf("[%s] TO: %s | SUBJECT: %s\n",
                    date('Y-m-d H:i:s'),
                    (string)($this->email->getTo()[0] ?? ''),
                    $this->email->getSubject()
                );
                file_put_contents(
                    base_path('storage/logs/mail-' . date('Y-m-d') . '.log'),
                    $log, FILE_APPEND
                );
                return true;
            }

            $this->mailer->send($this->email);
            return true;
        } catch (\Throwable $e) {
            log_error('Mailer error: ' . $e->getMessage());
            return false;
        }
    }
}
