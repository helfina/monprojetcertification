<?php

namespace App\Controller\Admin;

use App\Entity\Agenda;
use App\Entity\CalendrierVacScolaire;
use App\Entity\User;
use App\Repository\AgendaRepository;
use App\Repository\CalendrierVacScolaireRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(?CalendrierVacScolaire $calendrierVacScolaire, CalendrierVacScolaireRepository $calendrierVacScolaireRepository, AgendaRepository $agendaRepository, UserRepository $usersRepository ): Response
    {

        // or add an optional message - seen by developers
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');

        // REQUETE API VACANCES SCOLAIRE
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

            if (curl_errno($ch)) {
                echo curl_error($ch);
                die();
            }

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($http_code == intval(200)) {
                // echo $response;

                $donnees = json_decode($response);
                //dump($donnees);
                foreach ($donnees as $eventVac) {

                    $calendrierVacScolaire = new CalendrierVacScolaire();
                    $calendrierVacScolaire->setDescription($eventVac->description);
                    $calendrierVacScolaire->setPopulation($eventVac->population);
                    $calendrierVacScolaire->setStartDate(new \DateTime($eventVac->start_date));
                    $calendrierVacScolaire->setEndDate(new \DateTime($eventVac->end_date));
                    $calendrierVacScolaire->setLocation($eventVac->location);
                    $calendrierVacScolaire->setZones($eventVac->zones);
                    $calendrierVacScolaire->setAnneeScolaire($eventVac->annee_scolaire);
                    switch ($eventVac->zones){
                        case "Corse":
                            $calendrierVacScolaire->setBackColor('#ffea00');
                        case "Zone A":
                            $calendrierVacScolaire->setBackColor('#BC3908');
                        case "Zone B":
                            $calendrierVacScolaire->setBackColor('#76ff03');
                        case "Zone C":
                            $calendrierVacScolaire->setBackColor('#00e676');
                        default :
                            $calendrierVacScolaire->setBackColor('#d84315');
                    }
                    //dd($calendrierVacScolaire->getStartDate());
                    // si les date et la zone est differente alors tu enregistre en bdd
                    if($eventVac->start_date != $calendrierVacScolaire->getStartDate() && $eventVac->end_date != $calendrierVacScolaire->getEndDate() && $eventVac->zones != $calendrierVacScolaire->getZones()){
                          dd($calendrierVacScolaire);
                       // $calendrierVacScolaireRepository->add($calendrierVacScolaire, true);
                    }
                }

            } else {
                echo "Ressource introuvable : " . $http_code;
            }
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            curl_close($ch);
        }

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

        // AFFICHAGE DE LA LISTE DES UTILISATEUR
        $users = $usersRepository->findAll();

        return $this->render('administration/admin/index.html.twig', [
            'events' => $events,
            'data'=>$data,
            'users'=>$users
        ]);
    }


    #[Route('/admin/users/{id}', name: 'app_user_show')]
    public function show(User $user): Response
    {
        return $this->render('administration/admin/showUser.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/users_edit/{id}', name: 'app_user_edit')]
    public function edit(Request $request, User $user, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $form = $this->createForm(\App\Form\UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //!TODO revoir la gestion de nouveau mdp
            $NewPass = $form->get('password')->getData();
            if(isset($NewPass)){
                dump($NewPass);
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
            }else{
                $oldPass = $user->getPassword();
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $oldPass
                    )
                );
            }
            // encode the plain password

            $roles = $form->get('roles')->getData();

            if(in_array('ROLE_ADMIN', $roles, true) || in_array('ROLE_USER', $roles, true)){
               $role = array_unique($roles);
               $user->setRoles($role); dump($roles, $role);
            }


            $userRepository->add($user, true);

            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('administration/admin/editUser.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }

    #[Route('/admin/delete_user/{id}', name: 'app_user_delete')]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        // or add an optional message - seen by developers
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
    }
}
