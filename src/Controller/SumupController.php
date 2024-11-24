<?php

namespace App\Controller;

use App\Service\SumUpService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class SumupController extends AbstractController
{
    private $sumUpService;

    public function __construct(SumUpService $sumUpService)
    {
        $this->sumUpService = $sumUpService;
    }

    #[Route('/home', name: 'home')]
    public function home(): Response
    {
        return $this->render('sumup/home.html.twig');
    }

    #[Route('/create-checkout', name: 'create_checkout')]
    public function createCheckout(): Response
    {
        $token = $this->sumUpService->getToken();

        if (!$token) {
            throw $this->createNotFoundException('Unable to fetch SumUp token.');
        }

        // Données du checkout
        $checkoutData = [
            "amount" => "10.00",
            "currency" => "EUR",
            "checkout_reference" => uniqid('checkout_'), // Identifiant unique
            "payment_type" => "card",
            "pay_to_email" => ""/*mettre l'adresse mail associer au compte Sumup*/,
            "merchant_code" => ""/*le code marchant sur l'espace sumup*/,
        ];

        $checkoutResponse = $this->sumUpService->createCheckout($token, $checkoutData);

        if (!$checkoutResponse || empty($checkoutResponse['id'])) {
            throw $this->createNotFoundException('Unable to create SumUp checkout.');
        }

        // Rendu de la page de confirmation
        return $this->render('sumup/confirm.html.twig', [
            'checkoutId' => $checkoutResponse['id'],
            'amount' => $checkoutData['amount'],
        ]);
    }
    

    #[Route('/complete-checkout/{id}', name: 'complete_checkout')]
    public function completeCheckout(Request $request, string $id): Response
    {
        $token = $this->sumUpService->getToken();
        if (!$token) {
            throw $this->createNotFoundException('Unable to fetch SumUp token.');
        }

        $cardDetails = [
            'name' => 'Boaty McBoatface',
            'number' => '4111 1111 1111 1111',
            'expiry_month' => '12',
            'expiry_year' => '25',
            'cvv' => '123',
        ];

        $checkoutResponse = $this->sumUpService->completeCheckout($token, $id, $cardDetails);

        if (!$checkoutResponse) {
            throw $this->createNotFoundException('Unable to complete SumUp checkout.');
        }

        return $this->redirectToRoute('home', [
            'status' => $checkoutResponse['status'] ?? 'unknown',
        ]);
    }
}
