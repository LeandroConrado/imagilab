<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        // Configuração do Servidor
        $this->mailer->isSMTP();
        $this->mailer->Host       = $_ENV['MAIL_HOST'];
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $_ENV['MAIL_USERNAME'];
        $this->mailer->Password   = $_ENV['MAIL_PASSWORD'];
        $this->mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = $_ENV['MAIL_PORT'];
        $this->mailer->CharSet    = 'UTF-8';
        $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
    }

    public function send(string $toAddress, string $toName, string $subject, string $htmlBody): bool
    {
        try {
            $this->mailer->addAddress($toAddress, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $htmlBody;
            $this->mailer->AltBody = strip_tags($htmlBody);

            return $this->mailer->send();
        } catch (Exception $e) {
            // Em um app real, você logaria o erro: error_log("Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }
}