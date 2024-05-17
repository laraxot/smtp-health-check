<?php

declare(strict_types=1);

namespace Laraxot\SmtpHealthCheck;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Webmozart\Assert\Assert;

class SmtpCheck extends Check
{
    public function run(): Result
    {
        $this->label = 'Mail ('.config('mail.default').')';

        switch (config('mail.default')) {
            case 'smtp':
                return $this->checkSmtp();

            default:
                $result = Result::make();

                return $result->failed('Mailer '.config('mail.default').' is not supported');
        }
    }

    private function checkSmtp(): Result
    {
        $result = Result::make();
        Assert::string($host = config('mail.mailers.smtp.host'));
        Assert::integer($port = config('mail.mailers.smtp.port'));
        Assert::nullOrString($encryption = config('mail.mailers.smtp.encryption'));
        Assert::nullOrString($username = config('mail.mailers.smtp.username'));
        Assert::nullOrString($password = config('mail.mailers.smtp.password'));
        $tls = ($encryption !== null);
        try {
            $transport = new EsmtpTransport($host, $port, $tls);
            if ($username !== null) {
                $transport->setUsername($username);
            }
            if ($password !== null) {
                $transport->setPassword($password);
            }
            $transport->start();

            return $result->ok();
        } catch (\Exception $e) {
            return $result->failed($e->getMessage());
        }
    }
}
