<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Panier;
use App\Entity\ContenuPanier;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $em, Request $request, TranslatorInterface $translator): Response
    {
        // Crée un nouveau produit et un formulaire pour l'ajouter
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('upload_directory'), $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', $translator->trans('flash.error_upload', [], 'messages'));
                    return $this->redirectToRoute('home');
                }
                $product->setImage($newFilename);
            }

            $em->persist($product);
            $em->flush();
            $this->addFlash('success', $translator->trans('flash.product_added', [], 'messages'));
            return $this->redirectToRoute('home');
        }

        // Récupère tous les produits pour les afficher sur la page d'accueil
        $products = $em->getRepository(Product::class)->findAll();

        return $this->render('home/index.html.twig', [
            'products' => $products,
            'ajout_produit' => $form->createView(),
        ]);
    }

    #[Route('/product/{id}', name: 'product_show')]
    public function show(Product $product, EntityManagerInterface $em, Request $request, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            if (!$user) {
                $this->addFlash('error', $translator->trans('flash.login_required', [], 'messages'));
                return $this->redirectToRoute('app_login');
            }

            $panier = $em->getRepository(Panier::class)->findOneBy(['user' => $user, 'etat' => false]);
            if (!$panier) {
                $panier = new Panier();
                $panier->setUser($user);
                $panier->setDateAchat(new \DateTime());
                $panier->setEtat(false);
                $em->persist($panier);
            }

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

            $this->addFlash('success', $translator->trans('flash.added_to_cart', [], 'messages'));
            return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/edit/{id}', name: 'product_edit')]
    public function edit(Product $product, Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', $translator->trans('flash.product_updated', [], 'messages'));
            return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/product/delete/{id}', name: 'product_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', $translator->trans('flash.product_deleted', [], 'messages'));
        }

        return $this->redirectToRoute('home');
    }
}
