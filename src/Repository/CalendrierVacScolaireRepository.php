<?php

namespace App\Repository;

use App\Entity\CalendrierVacScolaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CalendrierVacScolaire>
 *
 * @method CalendrierVacScolaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendrierVacScolaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendrierVacScolaire[]    findAll()
 * @method CalendrierVacScolaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendrierVacScolaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendrierVacScolaire::class);
    }

    public function add(CalendrierVacScolaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CalendrierVacScolaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    /*    !TODO Cree une requete de trie
    SELECT * FROM `calendrier_vac_scolaire` WHERE `location` = 'rennes' ORDER BY `population`, `annee_scolaire` DESC, `start_date` DESC*/

//    /**
//     * @return CalendrierVacScolaire[] Returns an array of CalendrierVacScolaire objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CalendrierVacScolaire
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
