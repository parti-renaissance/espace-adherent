<?php

namespace App\Extract;

use App\Csv\CsvResponseFactory;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractEmailExtractCommandHandler
{
    private const CSV_DELIMITER = ';';

    private $csvResponseFactory;
    protected $translator;
    private $translatedKeys = [];

    public function __construct(CsvResponseFactory $csvResponseFactory, TranslatorInterface $translator)
    {
        $this->csvResponseFactory = $csvResponseFactory;
        $this->translator = $translator;
    }

    public function createResponse(AbstractEmailExtractCommand $emailExtractCommand): Response
    {
        $emails = $emailExtractCommand->getEmails();
        $fields = $emailExtractCommand->getFields();

        $csv = Writer::createFromPath('php://temp', 'r+');
        $csv->setDelimiter(self::CSV_DELIMITER);

        $csv->insertOne(array_merge([AbstractEmailExtractCommand::FIELD_EMAIL], $fields));

        foreach ($emails as $email) {
            $row = [
                $this->translateField(AbstractEmailExtractCommand::FIELD_EMAIL) => $email,
            ];

            foreach ($fields as $field) {
                $row[$this->translateField($field)] = null;
            }

            $csv->insertOne($this->computeRow($row, $email, $fields));
        }

        return $this->csvResponseFactory->createStreamedResponse($csv);
    }

    protected function translateField(string $field): string
    {
        if (!\array_key_exists($field, $this->translatedKeys)) {
            $key = sprintf('%s%s', $this->getTranslationPrefix(), $field);

            $this->translatedKeys[$field] = $this->translator->trans($key);
        }

        return $this->translatedKeys[$field];
    }

    abstract public static function getTranslationPrefix(): string;

    abstract protected function computeRow(array $row, string $email, array $fields): array;
}
