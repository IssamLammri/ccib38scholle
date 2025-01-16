<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\ParentEntity;
use App\Entity\Payment;
use App\Entity\Student;
use App\Entity\StudyClass;
use App\Form\PaymentType;
use App\Model\ApiResponseTrait;
use App\Repository\ParentsRepository;
use App\Repository\PaymentRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/payment')]
class PaymentController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PaymentRepository $paymentRepository,
        private ParentsRepository $parentsRepository,
    ){
    }

    #[Route('/list', name: 'payments_list')]
    public function allPayment(Request $request): Response
    {
        $allPayments = $this->paymentRepository->findAll();
        $parents = $this->parentsRepository->findAll();

        return $this->render('payment/list.html.twig', [
            'allPayments' => $allPayments,
            'parents' => $parents,
        ]);
    }

    #[Route('/all', name: 'all_payments', options: ['expose' => true], methods: ['GET'])]
    public function getAllPayments(): JsonResponse
    {
        $payments = $this->paymentRepository->findAll();
        $parents = $this->parentsRepository->findAll();

        return $this->json([
            'payments' => $payments,
            'parents' => $parents,
        ], 200, [], ['groups' => 'read_payment']);
    }


    #[Route('/new', name: 'payment_new', options: ['expose' => true], methods: ['POST'])]
    public function newPayment(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $parentId = $data['parent']['id'];
        $paidAmount = $data['paidAmount'];
        $discount = $data['discount'];
        $selectedChildren = $data['selectedChildren'];
        $parent = $this->entityManager->getRepository(ParentEntity::class)->find($parentId);

        $invoice = new Invoice();
        $invoice->setParent($parent);
        $invoice->setInvoiceDate(new \DateTime());
        $invoice->setTotalAmount($paidAmount);
        $invoice->setDiscount($discount);
        $invoice->setComment($data['comment']);

        $serviceType = $data['paymentType'];

        foreach ($selectedChildren as $childData) {
            $child = $this->entityManager->getRepository(Student::class)->find($childData['id']);
            if ($serviceType === 'arab') {
                $payment = new Payment();
                $payment->setParent($parent);
                $payment->setStudent($child);
                $payment->setAmountPaid($paidAmount / count($selectedChildren));
                $payment->setPaymentDate(new \DateTime());
                $payment->setPaymentType($data['paymentMethod']);
                $payment->setServiceType($serviceType);
                $payment->setComment($data['comment']);
                $payment->setInvoice($invoice);

                $this->entityManager->persist($payment);
            } elseif ($serviceType === 'soutien') {
                $totalClasses = 0;
                foreach ($selectedChildren as $childDataClasse) {
                    $totalClasses += count($childDataClasse['classes']);
                }

                $amountPerClass = ($paidAmount - $discount) / $totalClasses;

                foreach ($childData['classes'] as $classData) {
                    $studyClass = $this->entityManager->getRepository(StudyClass::class)->find($classData['id']);

                    $payment = new Payment();
                    $payment->setParent($parent);
                    $payment->setStudent($child);
                    $payment->setStudyClass($studyClass);
                    $payment->setAmountPaid($amountPerClass);
                    $payment->setPaymentDate(new \DateTime());
                    $payment->setPaymentType($data['paymentMethod']);
                    $payment->setServiceType($serviceType);
                    $payment->setMonth($data['selectedMonth']);
                    $payment->setComment($data['comment']);
                    $payment->setInvoice($invoice);

                    $this->entityManager->persist($payment);
                }
            }
        }

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return $this->apiResponse('Paiements et facture créés avec succès.');
    }

    #[Route('/receipt/{id}', name: 'payment_receipt')]
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

    #[Route('/delete/{id}', name: 'payment_delete', options: ['expose' => true], methods: ['DELETE'])]
    public function deletePayment(int $id): JsonResponse
    {
        $payment = $this->paymentRepository->find($id);

        if (!$payment) {
            return $this->json([
                'message' => 'Le paiement avec cet ID n\'existe pas.',
            ], 404);
        }

        $this->entityManager->remove($payment);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Le paiement a été supprimé avec succès.',
        ], 200);
    }

}
