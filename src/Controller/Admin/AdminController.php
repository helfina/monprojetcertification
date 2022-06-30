<?php

namespace App\Controller\Admin;

use App\Entity\Agenda;
use App\Entity\User;
use App\Repository\AgendaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(?Agenda $agenda, AgendaRepository $agendaRepository, Request $request): Response
    {

        // or add an optional message - seen by developers
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');

        //        !TODO verifier la condition d'enregistrement en bdd
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

            $annee = date('Y');
//            date('Y-m-d H:i:s')
            foreach ($donnees as $donnee) {
                foreach ($donnee as $key => $value) {
                    if ($donnee->start_date >= $annee && $donnee->population != "Enseignants" && $value == "Rennes") {
                        // On vérifie si l'id existe
                        if(!$agenda){
                            $agenda = new Agenda();
                        }

                        $agenda->setTitle($donnee->description);

                        $agenda->setStart(new \DateTime($donnee->start_date));
                        $agenda->setEnd(new \DateTime($donnee->end_date));

                        $agenda->setAllDay(1);
                        $agenda->setBackgroundColor("#008000");
                        $agenda->setTextColor("#000000");
                        $agenda->setDescription(
                            "population :  " . $donnee->population . "</br>".
                                        " vacances scolaire : " . $donnee->location . " </br> ".
                                        " Zone :  " .$donnee->zones . "</br> ".
                                        "années scolaire : " . $donnee->annee_scolaire

                        );
//                        $agendaRepository->add($agenda, true);
                        if ($this->isCsrfTokenValid('agenda' . $agenda->getId(), $request->request->get('_token'))) {
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

        dump($events);
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


        return $this->render('administration/admin/index.html.twig', [
            'events' => $events,
            'data'=>$data,
        ]);
    }
}
