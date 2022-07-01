<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Form\AgendaType;
use App\Repository\AgendaRepository;
use App\Repository\CalendrierVacScolaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgendaController extends AbstractController
{

    #[Route('/agenda', name: 'app_agenda')]
    public function index(AgendaRepository $agendaRepository, CalendrierVacScolaireRepository $calendrierVacScolaireRepository): Response
    {
        $events = $agendaRepository->findAll();
        $eventsVac = $calendrierVacScolaireRepository->findAll();

        foreach ($events as $event) {
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
        foreach ($eventsVac as $eventVac) {

            if($eventVac->getLocation() == "Rennes" && $eventVac->getZones() == "Zone B" &&  $eventVac->getPopulation() == "-" || $eventVac->getPopulation() == "Élèves" && $eventVac->getLocation() == "Rennes" && $eventVac->getZones() == "Zone B" ){
//                dd($eventVac);
                $rdvs[] = [
                    'id' => $eventVac->getId(),
                    'start' => $eventVac->getStartDate()->format('Y-m-d H:i:s'),
                    'end' => $eventVac->getEndDate()->format('Y-m-d H:i:s'),
                    'title' => $eventVac->getDescription() . " / " . $eventVac->getPopulation(),
                    'description' => $eventVac->getDescription(),
                    'backgroundColor' => $eventVac->getBackColor(),
                    'textColor' => '#000000',
                    'allDay' => true,
                ];
            }
        }

        $data = json_encode($rdvs);

        return $this->render('agenda/index.html.twig', compact('data'));
    }

    #[Route('/agenda/new', name: 'app_agenda_new')]
    public function new(Request $request, AgendaRepository $agendaRepository): Response
    {
        $agenda = new Agenda();
        $form = $this->createForm(AgendaType::class, $agenda);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $agendaRepository->add($agenda, true);

            return $this->redirectToRoute('app_agenda_liste', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('agenda/new.html.twig', [
            'event' => $agenda,
            'form' => $form
        ]);
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
        $form = $this->createForm(AgendaType::class, $agenda);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $agendaRepository->add($agenda, true);

            return $this->redirectToRoute('app_agenda_liste', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('agenda/edit.html.twig', [
            'event' => $agenda,
            'form' => $form
        ]);
    }

    #[Route('/agenda/delete/{id}', name: 'app_agenda_delete')]
    public function delete(Request $request, Agenda $agenda, AgendaRepository $agendaRepository): Response
    {
        // or add an optional message - seen by developers
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete' . $agenda->getId(), $request->request->get('_token'))) {
            $agendaRepository->remove($agenda, true);
        }

        return $this->redirectToRoute('app_agenda_liste', [], Response::HTTP_SEE_OTHER);
    }

}
