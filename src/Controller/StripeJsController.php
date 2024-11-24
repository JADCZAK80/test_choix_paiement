<?php

namespace App\Controller;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeJsController extends AbstractController
{
    #[Route('/payment', name: 'payment')]
    public function index(Request $request): JsonResponse
    {
        // Vérifie si le prix est bien passé en paramètre POST
        $prix = /*$request->request->get('prix')*/125;
        if (!$prix || !is_numeric($prix)) {
            return new JsonResponse(['error' => 'Prix invalide'], 400);
        }

        // Configure la clé API Stripe
        Stripe::setApiKey($this->getParameter('stripe.secret_key'));

        // Crée le Payment Intent
        $intent = PaymentIntent::create([
            'amount' => $prix * 100, // Stripe attend les montants en centimes
            'currency' => 'eur',
        ]);

        return new JsonResponse(['clientSecret' => $intent->client_secret]);
    }

    #[Route('/payment/form', name: 'payment_form')]
    public function paymentForm(): Response
    {
        // Configure Stripe pour créer un PaymentIntent de test (ex : montant de 10€)
        Stripe::setApiKey($this->getParameter('stripe.secret_key'));
        $intent = PaymentIntent::create([
            'amount' => 12500,
            'currency' => 'eur',
        ]);
    
        return $this->render('stripe_js/index.html.twig', [
            'clientSecret' => $intent->client_secret,
        ]);
    }
    #[Route('/succes', name: 'succes')]
    public function succes(): Response
    {
        return $this->render('stripe_js/succes.html.twig', [
            
        ]);
    }
}
