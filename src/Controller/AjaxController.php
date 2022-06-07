<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Repository\AgendaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;


class AjaxController extends AbstractController
{

    #[Route('/ajax/edit/{id}', name: 'app_ajax_event_edit')]
    public function updateEvent(?Agenda $agenda, Request $request,AgendaRepository $agendaRepository): Response
    {
        // On récupère les données
        $donnees = json_decode($request->getContent());
        // !TODO returne une erreur 500 revoir la requete
        if(
            isset($donnees->title) && !empty($donnees->title) &&
            isset($donnees->start) && !empty($donnees->start) &&
            isset($donnees->description) && !empty($donnees->description) &&
            isset($donnees->backgroundColor) && !empty($donnees->backgroundColor) &&
            isset($donnees->textColor) && !empty($donnees->textColor)
        ){
            // Les données sont complètes
            // On initialise un code
            $code = 200; //=> creations

            // On vérifie si l'id existe
            if(!$agenda){
                // On instancie un rendez-vous
                $agenda = new Agenda();

                // On change le code
                $code = 201; //=> j'ai crées
            }

            // On hydrate l'objet avec les données

            $agenda->setTitle($donnees->title);
            $agenda->setDescription($donnees->description);
            $agenda->setStart(new DateTime($donnees->start));
            if($donnees->allDay){
                $agenda->setEnd(new DateTime($donnees->start));
            }else{
                $agenda->setEnd(new DateTime($donnees->end));
            }
            $agenda->setAllDay($donnees->allDay);
            $agenda->setBackgroundColor($donnees->backgroundColor);
            $agenda->setTextColor($donnees->textColor);

            //$entityManager = $doctrine->getManager();
//            $entityManager->persist($calendar);
//            $entityManager->flush();
            $agendaRepository->add($agenda, true);
//            dump($entityManager);

            // On retourne le code
            return new Response('Ok', $code);
        }else{
            // Les données sont incomplètes
            return new Response('Données incomplètes', 404);
        }

    }
}
