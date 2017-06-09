<?php

namespace AppBundle\Consumer;

class TransactionalMailjetConsumer extends AbstractMailjetConsumer
{
    const NAME = 'mailjet-delayed-transactional';
    const CLIENT_ID = 'app.mailjet.transactional_client';
}
