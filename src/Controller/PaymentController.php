<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Form\PaymentType;
use App\Repository\ParentsRepository;
use App\Repository\PaymentRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PaymentRepository $paymentRepository,
        private ParentsRepository $parentsRepository,
    )
    {
    }

    #[Route('/payments', name: 'payments_list')]
    public function allPayment(Request $request): Response
    {
        $allPayments = $this->paymentRepository->findAll();
        $parents = $this->parentsRepository->findAll();


        return $this->render('payment/list.html.twig', [
            'allPayments' => $allPayments,
            'parents' => $parents,
        ]);
    }

    #[Route('/payment/new', name: 'payment_new')]
    public function newPayment(Request $request): Response
    {
        $payment = new Payment();
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($payment);
            $this->entityManager->flush();

            // Générer un reçu (optionnel)
            return $this->redirectToRoute('payment_receipt', ['id' => $payment->getId()]);
        }

        return $this->render('payment/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/payment/receipt/{id}', name: 'payment_receipt')]
    public function paymentReceipt(int $id, EntityManagerInterface $entityManager): Response
    {
        $payment = $entityManager->getRepository(Payment::class)->find($id);

        if (!$payment) {
            throw $this->createNotFoundException('Le paiement n\'existe pas.');
        }

        return $this->render('payment/receipt.html.twig', [
            'payment' => $payment,
        ]);
    }
}
