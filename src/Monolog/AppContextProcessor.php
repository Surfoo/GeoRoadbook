<?php

namespace App\Monolog;

use App\Security\User;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Adds request/user context to every record on the "app" channel,
 * so an error log line is enough to reproduce the request without
 * cross-referencing the access log.
 */
final readonly class AppContextProcessor implements ProcessorInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private Security $security,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return $record;
        }

        $user = $this->security->getUser();

        $record->extra['request_uri']    = $request->getRequestUri();
        $record->extra['request_method'] = $request->getMethod();
        $record->extra['ip']             = $request->getClientIp();
        $record->extra['user']           = $user instanceof User ? $user->getUserIdentifier() : null;

        return $record;
    }
}
