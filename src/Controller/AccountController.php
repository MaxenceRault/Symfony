<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'account')]
    public function index(EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', $translator->trans('flash.login_required', [], 'messages'));
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les commandes validées
        $orders = $em->getRepository(Panier::class)->findBy([
            'user' => $user,
            'etat' => true,
        ]);

        // Ajouter les totaux pour chaque commande
        $ordersWithTotals = [];
        foreach ($orders as $order) {
            $total = 0;
            foreach ($order->getContenuPaniers() as $contenu) {
                $total += $contenu->getQuantite() * $contenu->getProduit()->getPrice();
            }
            $ordersWithTotals[] = [
                'order' => $order,
                'total' => $total,
            ];
        }

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'orders' => $ordersWithTotals,
        ]);
    }

    #[Route('/account/edit', name: 'account_edit')]
    public function edit(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', $translator->trans('flash.profile_updated', [], 'messages'));
            return $this->redirectToRoute('account');
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/order/{id}', name: 'order_show')]
    public function show(Panier $order, TranslatorInterface $translator): Response
    {
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException($translator->trans('flash.access_denied', [], 'messages'));
        }

        // Calculer le montant total de la commande
        $total = 0;
        foreach ($order->getContenuPaniers() as $contenu) {
            $total += $contenu->getQuantite() * $contenu->getProduit()->getPrice();
        }

        return $this->render('account/order_show.html.twig', [
            'order' => $order,
            'total' => $total,
        ]);
    }
}

