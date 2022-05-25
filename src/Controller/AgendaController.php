<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Repository\AgendaRepository;
use phpDocumentor\Reflection\Types\AggregatedType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgendaController extends AbstractController
{
    #[Route('/agenda', name: 'app_agenda')]
    public function index(AgendaRepository $agendaRepository): Response
    {
        $events = $agendaRepository->findAll();

        $rdvs = [];

        foreach($events as $event){
            $rdvs[] = [
                'id' => $event->getId(),
                'start' => $event->getStart()->format('Y-m-d H:i:s'),
                'end' => $event->getEnd()->format('Y-m-d H:i:s'),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'backgroundColor' => $event->getBackgroundColor(),
                'textColor' => $event->getTextColor(),
                'allDay' => $event->isAllDay(),
            ];
        }

        $data = json_encode($rdvs);

        return $this->render('agenda/index.html.twig', compact('data'));
    }

    #[Route('/agenda/liste', name: 'app_agenda_liste')]
    public function liste(AgendaRepository $agendaRepository): Response
    {
        return $this->render('agenda/liste.html.twig', [
            'events' => $agendaRepository->findAll(),
        ]);
    }

    #[Route('/agenda/{id}', name: 'app_agenda_show')]
    public function show(Agenda $agenda): Response
    {
        return $this->render('agenda/show.html.twig', [
            'event' => $agenda,
        ]);
    }
    #[Route('/agenda/edit/{id}', name: 'app_agenda_edit')]
    public function edit(Request $request, Agenda $agenda, AgendaRepository $agendaRepository): Response
    {
        $form =$this->createForm(AggregatedType::class, $agenda);

        return $this->render('agenda/show.html.twig', [
            'event' => $agenda,
            'form' => $form
        ]);
    }
}
