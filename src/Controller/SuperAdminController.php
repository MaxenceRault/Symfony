<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Panier;
use App\Entity\User; 


class SuperAdminController extends AbstractController
{


    #[Route('/superadmin/paniers', name: 'superadmin_paniers')]
    public function listUnpaidCarts(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $carts = $em->getRepository(Panier::class)->findBy(['etat' => false]);

        return $this->render('superadmin/unpaid_carts.html.twig', [
            'carts' => $carts,
        ]);
    }

    #[Route('/superadmin/utilisateurs', name: 'superadmin_users_today')]
    public function listUsersToday(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $today = new \DateTime('today');
        $users = $em->getRepository(User::class)->createQueryBuilder('u')
            ->where('u.createdAt >= :today')
            ->setParameter('today', $today)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('superadmin/users_today.html.twig', [
            'users' => $users,
        ]);
    }


}
