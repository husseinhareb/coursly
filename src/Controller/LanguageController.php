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
        // Store the selected locale in the session
        $request->getSession()->set('_locale', $locale);
        
        // Redirect back to the referring page (or fallback to the homepage)
        return $this->redirect($request->headers->get('referer') ?: '/');
    }
}
