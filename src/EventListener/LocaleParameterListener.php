<?php
namespace App\EventListener;
/**
 * Ce listener définit la locale pour la requête en fonction des données de session.
 * 
 * Il écoute l'événement de requête du noyau et vérifie si la requête possède une session.
 * Si la session existe, il récupère la locale de la session (par défaut 'fr' si non définie)
 * et applique cette locale à la requête.
 * 
 * @package App\EventListener
 */
use Symfony\Component\HttpKernel\Event\RequestEvent;

class LocaleParameterListener
{
    
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->hasSession()) {
            return;
        }

        // Récupérer la locale depuis la session ou utiliser 'fr' par défaut
        $locale = $request->getSession()->get('_locale', 'fr');

        // Appliquer la locale à la requête
        $request->setLocale($locale);
    }
}
