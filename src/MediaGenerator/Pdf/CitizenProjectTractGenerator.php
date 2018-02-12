<?php

namespace AppBundle\MediaGenerator\Pdf;

use AppBundle\MediaGenerator\BaseSnappyGenerator;
use AppBundle\MediaGenerator\Command\CitizenProjectTractCommand;
use AppBundle\MediaGenerator\Command\MediaCommandInterface;
use AppBundle\MediaGenerator\MediaContent;
use Knp\Snappy\GeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

class CitizenProjectTractGenerator extends BaseSnappyGenerator
{
    private $badgeImagePath;

    public function __construct(
        GeneratorInterface $pdfGenerator,
        EngineInterface $templateEngine,
        string $badgeImagePath
    ) {
        parent::__construct($pdfGenerator, $templateEngine);

        $this->badgeImagePath = $badgeImagePath;
    }

    public function generate(MediaCommandInterface $command): MediaContent
    {
        /** @var CitizenProjectTractCommand $command */
        $html = $this->templateEngine->render(
            'citizen_project/tract_pdf.html.twig',
            [
                'command' => $command,
                'badge_path' => $this->badgeImagePath,
            ]
        );

        $pdfContent = $this->mediaGenerator->getOutputFromHtml(
            $html,
            [
                'dpi' => 300,
                'lowquality' => false,
                'margin-bottom' => 0,
                'margin-top' => 0,
                'margin-left' => 0,
                'margin-right' => 0,
                'page-size' => 'A5',
                'disable-smart-shrinking' => true,
                'zoom' => 0.3199, // Hack for filling all page
                'title' => $command->getCitizenProjectTitle(),
            ]
        );

        return new MediaContent($pdfContent, 'application/pdf', \strlen($pdfContent));
    }
}
