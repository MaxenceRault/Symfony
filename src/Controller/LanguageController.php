<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LanguageController extends AbstractController
{
    #[Route('/change-language/{locale}', name: 'change_language')]
    public function changeLanguage(string $locale, Request $request): Response
    {
        // Liste des langues supportées
        $supportedLocales = ['fr', 'en', 'es'];

        if (!in_array($locale, $supportedLocales)) {
            throw $this->createNotFoundException("La langue $locale n'est pas supportée.");
        }

        // Mettre à jour la locale dans la session
        $request->getSession()->set('_locale', $locale);

        // Rediriger vers la page précédente ou la page d'accueil
        $referer = $request->headers->get('referer', $this->generateUrl('home'));

        return $this->redirect($referer);
    }



    

}


