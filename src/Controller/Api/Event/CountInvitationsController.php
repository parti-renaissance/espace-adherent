<?php

namespace App\Controller\Api\Event;

use App\Event\Request\CountInvitationsRequest;
use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class CountInvitationsController extends AbstractController
{
    public function __invoke(Request $request, SerializerInterface $serializer, AdherentRepository $adherentRepository): array
    {
        return [
            'count' => $adherentRepository->countInvitations($serializer->deserialize($request->getContent(), CountInvitationsRequest::class, 'json')),
        ];
    }
}
