<?php

namespace App\Controller\EnMarche;

use App\Form\ReportCommandType;
use App\Report\ReportCommand;
use App\Report\ReportCreationCommandHandler;
use App\Report\ReportManager;
use App\Report\ReportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ReportController extends AbstractController
{
    /**
     * @Route(
     *     "/report/{type}/{uuid}",
     *     name="app_report",
     *     requirements={
     *         "type": App\Report\ReportType::TYPES_URI_PATTERN,
     *         "uuid": "%pattern_uuid%"
     *     },
     *     methods={"GET", "POST"}
     * )
     * @Security("is_granted('REPORT')")
     */
    public function reportAction(
        Request $request,
        string $type,
        string $uuid,
        ReportManager $reportManager,
        ReportCreationCommandHandler $handler
    ): Response {
        $subject = $reportManager->getSubjectByUuid(ReportType::URI_MAP[$type], $uuid);

        if (!$subject) {
            throw $this->createNotFoundException('Subject of the report cannot be found');
        }

        $command = new ReportCommand($subject, $this->getUser());
        $form = $this->createForm(ReportCommandType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handle($command);
            $this->addFlash('info', 'Merci. Votre signalement a bien été enregistré.');

            $url = $request->query->get('redirectUrl', '/');
            // Let's protect ourself from open redirect vulnerability
            // see https://www.owasp.org/index.php/Unvalidated_Redirects_and_Forwards_Cheat_Sheet
            // So here we only redirect URL that matches one of our route
            try {
                $router = $this->container->get('router');
                $router->setContext($router->getContext()->setMethod(Request::METHOD_GET));
                $router->match($url);
            } catch (ResourceNotFoundException | MethodNotAllowedException $e) {
                $url = '/';
            }

            return $this->redirect($url);
        }

        return $this->render('report/report.html.twig', ['form' => $form->createView()]);
    }
}
