<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\AgendaRepository;
use App\Repository\CalendrierVacScolaireRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    #[Route('/users', name: 'app_users')]
    public function index(AgendaRepository $agendaRepository, CalendrierVacScolaireRepository $calendrierVacScolaireRepository): Response
    {
// AFFICHAGE DES EVENEMENT DE L'AGENDA
        $rdvs = [];
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
        return $this->render('administration/users/index.html.twig',[
            'data'=>$data,

        ]);
    }


}
