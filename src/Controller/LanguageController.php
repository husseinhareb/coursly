<?php
// src/Controller/LanguageController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LanguageController extends AbstractController
{
    #[Route('/change-language/{locale}', name: 'change_language')]
    public function changeLanguage(Request $request, string $locale): RedirectResponse
    {
        // Stocker la locale sélectionnée dans la session
        $request->getSession()->set('_locale', $locale);
        
        // Rediriger vers la page d'origine (ou revenir à la page d'accueil)
        return $this->redirect($request->headers->get('referer') ?: '/');
    }
}
