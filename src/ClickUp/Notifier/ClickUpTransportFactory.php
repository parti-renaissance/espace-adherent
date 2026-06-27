<?php

declare(strict_types=1);

namespace App\ClickUp\Notifier;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;

class ClickUpTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): ClickUpTransport
    {
        if ('clickup' !== $dsn->getScheme()) {
            throw new UnsupportedSchemeException($dsn, 'clickup', $this->getSupportedSchemes());
        }

        $token = $this->getUser($dsn);
        $workspaceId = $dsn->getOption('workspace_id');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return new ClickUpTransport($token, $workspaceId, $this->client, $this->dispatcher)
            ->setHost($host)
            ->setPort($port)
        ;
    }

    protected function getSupportedSchemes(): array
    {
        return ['clickup'];
    }
}
