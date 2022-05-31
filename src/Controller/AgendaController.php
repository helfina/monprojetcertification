<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Form\AgendaType;
use App\Repository\AgendaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class AgendaController extends AbstractController
{

    #[Route('/agenda', name: 'app_agenda')]
    public function index(?Agenda $agenda, AgendaRepository $agendaRepository, Request $request): Response
    {
        $rdvs = [];
        $ch = curl_init();
        try {
            curl_setopt($ch, CURLOPT_URL, "https://data.education.gouv.fr/api/v2/catalog/datasets/fr-en-calendrier-scolaire/exports/json?limit=-1&offset=0&lang=fr&timezone=UTC");
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);


            $response = curl_exec($ch);
            $donnees = json_decode($response);

            $tableauDeDonnées = [];
            $annee = date('Y');
//            date('Y-m-d H:i:s')
            foreach ($donnees as $donnee) {
                foreach ($donnee as $key => $value) {
                    if ($donnee->start_date >= $annee && $donnee->population != "Enseignants" && $value == "Rennes") {

                        $agenda = new Agenda();

                        $agenda->setTitle($donnee->description);

                        $agenda->setStart(new DateTime($donnee->start_date));
                        $agenda->setEnd(new DateTime($donnee->end_date));

                        $agenda->setAllDay(1);
                        $agenda->setBackgroundColor("#008000");
                        $agenda->setTextColor("#000000");
                        $agenda->setDescription("population : " . $donnee->population . "</br> vacances scolaire : " . $donnee->location . " </br>  Zone :  " .
                            $donnee->zones . "</br> années scolaire : " . $donnee->annee_scolaire

                        );

                        if ($this->isCsrfTokenValid('agenda' . $agenda->getId(), $request->request->get('_token'))) {
                            dump($agenda);
                            $agendaRepository->add($agenda, true);
                        }
                    }

                }


            }

            if (curl_errno($ch)) {
                echo curl_error($ch);
                die();
            }

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($http_code == intval(200)) {
               // echo $response;

            } else {
                echo "Ressource introuvable : " . $http_code;
            }
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            curl_close($ch);
        }


        $events = $agendaRepository->findAll();


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
        if ($this->isCsrfTokenValid('delete' . $agenda->getId(), $request->request->get('_token'))) {
            $agendaRepository->remove($agenda, true);
        }

        return $this->redirectToRoute('app_agenda_liste', [], Response::HTTP_SEE_OTHER);
    }

}
