<?php

namespace App\Authentication\Model\Repository;

use App\Authentication\Model\Entity\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RefreshToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method RefreshToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method RefreshToken[]    findAll()
 * @method RefreshToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function findValidToken($token): ?RefreshToken
    {
        // Auth:EntityName refer to doctrine.orm.mappings.{mapping_name}.alias
        $dql = '
            SELECT t
            FROM Auth:RefreshToken t
            WHERE t.token = :token';

        try {
            return $this
                ->getEntityManager()
                ->createQuery($dql)
                ->setParameter('token', $token)
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
