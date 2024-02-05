<?php

namespace Tests\App\Controller\Webhook;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('webhook')]
class TelegramBotControllerTest extends AbstractWebhookTestCase
{
    use ControllerTestTrait;

    #[DataProvider('provideBadRequestContent')]
    public function testBadRequest(?string $jsonContent): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/telegram',
            [],
            [],
            [
                'Content-Type' => 'application/json',
                'HTTP_X-Telegram-Bot-Api-Secret-Token' => 'bot-1-secret',
            ],
            $jsonContent
        );

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    public static function provideBadRequestContent(): \Generator
    {
        yield 'Empty content' => [null];
        yield 'Invalid JSON content' => ['[Invalid JSON'];
        yield 'Content with missing required parameter "update_id"' => [
            json_encode(
                [
                    'message' => [
                        'message_id' => 455,
                        'from' => [
                            'id' => 789,
                            'is_bot' => false,
                            'first_name' => 'John',
                            'last_name' => 'Doe',
                            'username' => 'johnd',
                            'language_code' => 'en',
                        ],
                        'chat' => [
                            'id' => 1011,
                            'first_name' => 'John',
                            'last_name' => 'Doe',
                            'username' => 'johnd',
                            'type' => 'private',
                        ],
                        'date' => 1707065709,
                        'text' => 'Lorem ipsum dolor sit amet.',
                    ],
                ]
            ),
        ];
    }

    public function testRequestSuccess(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/telegram',
            [],
            [],
            [
                'Content-Type' => 'application/json',
                'HTTP_X-Telegram-Bot-Api-Secret-Token' => 'bot-1-secret',
            ],
            json_encode(
                [
                    'update_id' => 123,
                    'message' => [
                        'message_id' => 455,
                        'from' => [
                            'id' => 789,
                            'is_bot' => false,
                            'first_name' => 'John',
                            'last_name' => 'Doe',
                            'username' => 'johnd',
                            'language_code' => 'en',
                        ],
                        'chat' => [
                            'id' => 1011,
                            'first_name' => 'John',
                            'last_name' => 'Doe',
                            'username' => 'johnd',
                            'type' => 'private',
                        ],
                        'date' => 1707065709,
                        'text' => 'Lorem ipsum dolor sit amet.',
                    ],
                ]
            )
        );

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }
}
