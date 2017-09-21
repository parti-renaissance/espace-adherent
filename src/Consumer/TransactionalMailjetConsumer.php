<?php

namespace AppBundle\Consumer;

class TransactionalMailjetConsumer extends AbstractMailjetConsumer
{
    protected function getClientId(): string
    {
        return 'app.mailjet.transactional_client';
    }
}
