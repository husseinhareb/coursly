<?php
// src/Controller/LocaleController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class LocaleController extends AbstractController
{
    #[Route('/change-language/{locale}', name: 'change_language')]
    public function changeLanguage(Request $request, string $locale): RedirectResponse
    {
        // Enregistrer la locale dans la session.
        $request->getSession()->set('_locale', $locale);

        // Facultativement, déterminer la page vers laquelle rediriger
        // Ici, nous redirigeons vers la page de provenance, ou par défaut vers la page d'accueil
        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer ?: $this->generateUrl('app_home'));
    }
}
