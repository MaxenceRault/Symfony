<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Panier;
use App\Entity\ContenuPanier;
use App\Entity\Product;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', $translator->trans('flash.login_required', [], 'messages'));
            return $this->redirectToRoute('app_login');
        }

        $panier = $em->getRepository(Panier::class)->findOneBy([
            'user' => $user,
            'etat' => false
        ]);

        if (!$panier) {
            $this->addFlash('info', $translator->trans('flash.cart_empty', [], 'messages'));
            return $this->render('cart/index.html.twig', [
                'panier' => null,
                'total' => 0,
            ]);
        }

        // Calculer le total du panier
        $total = 0;
        foreach ($panier->getContenuPaniers() as $contenu) {
            $total += $contenu->getQuantite() * $contenu->getProduit()->getPrice();
        }

        return $this->render('cart/index.html.twig', [
            'panier' => $panier,
            'total' => $total,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(Product $product, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', $translator->trans('flash.login_required', [], 'messages'));
            return $this->redirectToRoute('app_login');
        }

        // Récupérer le panier de l'utilisateur ou en créer un nouveau s'il n'existe pas
        $panier = $em->getRepository(Panier::class)->findOneBy(['user' => $user, 'etat' => false]);
        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $panier->setDateAchat(new \DateTime());
            $panier->setEtat(false);
            $em->persist($panier);
        }

        // Ajouter le produit au panier ou mettre à jour la quantité s'il existe déjà
        $contenu = $em->getRepository(ContenuPanier::class)->findOneBy(['panier' => $panier, 'produit' => $product]);
        if ($contenu) {
            $contenu->setQuantite($contenu->getQuantite() + 1);
        } else {
            $contenu = new ContenuPanier();
            $contenu->setPanier($panier);
            $contenu->setProduit($product);
            $contenu->setQuantite(1);
            $contenu->setDate(new \DateTime());
            $em->persist($contenu);
        }

        $em->flush();

        $this->addFlash('success', $translator->trans('flash.product_added', [], 'messages'));
        return $this->redirectToRoute('home');
    }

    #[Route('/cart/checkout', name: 'cart_checkout')]
    public function checkout(EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        $panier = $em->getRepository(Panier::class)->findOneBy([
            'user' => $user,
            'etat' => false
        ]);

        if (!$panier) {
            $this->addFlash('error', $translator->trans('flash.cart_empty', [], 'messages'));
            return $this->redirectToRoute('app_cart');
        }

        // Vérification du stock et mise à jour
        foreach ($panier->getContenuPaniers() as $contenu) {
            $produit = $contenu->getProduit();
            $nouveauStock = $produit->getStock() - $contenu->getQuantite();

            if ($nouveauStock < 0) {
                $this->addFlash('error', $translator->trans('flash.insufficient_stock', ['%product%' => $produit->getTitle()], 'messages'));
                return $this->redirectToRoute('app_cart');
            }

            $produit->setStock($nouveauStock);
        }

        // Marquer le panier comme "payé"
        $panier->setEtat(true);
        $em->flush();

        $this->addFlash('success', $translator->trans('flash.order_validated', [], 'messages'));
        return $this->redirectToRoute('home');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(ContenuPanier $contenu, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        // Supprimer le produit du panier
        $em->remove($contenu);
        $em->flush();

        $this->addFlash('success', $translator->trans('flash.product_removed', [], 'messages'));
        return $this->redirectToRoute('app_cart');
    }
}
