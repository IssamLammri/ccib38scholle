<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\ParentEntity;
use App\Entity\Payment;
use App\Entity\PaymentBookItem;
use App\Entity\Student;
use App\Entity\StudyClass;
use App\Form\PaymentType;
use App\Model\ApiResponseTrait;
use App\Repository\BookRepository;
use App\Repository\ParentsRepository;
use App\Repository\PaymentRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MANAGER')]
#[Route('/payment')]
class PaymentController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PaymentRepository $paymentRepository,
        private ParentsRepository $parentsRepository,
        private BookRepository $bookRepository
    ){
    }

    #[Route('/list', name: 'payments_list', options: ['expose' => true])]
    public function allPayment(Request $request): Response
    {
        $allPayments = $this->paymentRepository->findAll();
        $parents = $this->parentsRepository->findAll();

        return $this->render('payment/list.html.twig', [
            'allPayments' => $allPayments,
            'parents' => $parents,
            'books' => $this->bookRepository->findAll(),
            'currentUser' => $this->getUser(),
        ]);
    }

    #[Route('/all', name: 'all_payments', options: ['expose' => true], methods: ['GET'])]
    public function getAllPayments(): JsonResponse
    {
        $payments = $this->paymentRepository->findBy([], ['paymentDate' => 'DESC']);
        $parents = $this->parentsRepository->findAll();

        return $this->json([
            'payments' => $payments,
            'parents' => $parents,
        ], 200, [], ['groups' => 'read_payment']);
    }


    #[Route('/new', name: 'payment_new', options: ['expose' => true], methods: ['POST'])]
    public function newPayment(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            $parentId = $data['parent']['id'];
            $paidAmount = (float)($data['paidAmount'] ?? 0);
            $discount   = (float)($data['discount'] ?? 0);
            $selectedChildren = $data['selectedChildren'] ?? [];
            $serviceType = $data['paymentType'] ?? 'soutien';

            $parent = $this->entityManager->getRepository(ParentEntity::class)->find($parentId);

            $invoice = new Invoice();
            $invoice->setParent($parent);
            $invoice->setInvoiceDate(new \DateTime());
            $invoice->setTotalAmount($paidAmount); // tu peux décider d’y mettre (paidAmount) ou (paidAmount - discount)
            $invoice->setDiscount($discount);
            $invoice->setComment($data['comment'] ?? null);

            if ($serviceType === 'arabe') {
                foreach ($selectedChildren as $childData) {
                    $child = $this->entityManager->getRepository(Student::class)->find($childData['id']);
                    $payment = new Payment();
                    $payment->setParent($parent);
                    $payment->setStudent($child);
                    $payment->setAmountPaid($paidAmount / max(1,count($selectedChildren)));
                    $payment->setPaymentDate(new \DateTime());
                    $payment->setPaymentType($data['paymentMethod'] ?? '');
                    $payment->setServiceType($serviceType);
                    $payment->setComment($data['comment'] ?? null);
                    $payment->setInvoice($invoice);
                    $payment->setProcessedBy($this->getUser());

                    $this->entityManager->persist($payment);
                }

            } elseif ($serviceType === 'soutien') {

                $selectedMonths = $data['selectedMonths'] ?? []; // tableau de mois ['Septembre','Octobre',...]
                $selectedYear   = (int)($data['selectedYear'] ?? date('Y'));

                // sécurité : si aucun mois, on refuse
                if (count($selectedMonths) === 0) {
                    return $this->apiResponse('Aucun mois sélectionné', 400);
                }

                // total des matières cochées (tous les enfants confondus)
                $totalClasses = 0;
                foreach ($selectedChildren as $childDataClasse) {
                    $totalClasses += count($childDataClasse['classes'] ?? []);
                }

                if ($totalClasses === 0) {
                    return $this->apiResponse('Aucune matière sélectionnée', 400);
                }

                // montant par classe *par mois*
                // Si "paidAmount" est le total (toutes classes × tous mois) : on répartit
                $monthsCount = count($selectedMonths);
                $amountPerClassPerMonth = ($paidAmount - $discount) / ($totalClasses * $monthsCount);
                foreach ($selectedChildren as $childData) {
                    $child = $this->entityManager->getRepository(Student::class)->find($childData['id']);

                    foreach (($childData['classes'] ?? []) as $classData) {
                        $studyClass = $this->entityManager->getRepository(StudyClass::class)->find($classData['id']);

                        foreach ($selectedMonths as $monthLabel) {
                            $payment = new Payment();
                            $payment->setParent($parent);
                            $payment->setStudent($child);
                            $payment->setStudyClass($studyClass);
                            $payment->setAmountPaid(number_format($amountPerClassPerMonth, 2, '.', ''));
                            $payment->setPaymentDate(new \DateTime()); // ou une date par défaut
                            $payment->setPaymentType($data['paymentMethod']);
                            $payment->setServiceType($serviceType);
                            $payment->setMonth($monthLabel);
                            $payment->setYear($selectedYear);
                            $payment->setComment($data['comment'] ?? null);
                            $payment->setInvoice($invoice);
                            $payment->setProcessedBy($this->getUser());

                            $this->entityManager->persist($payment);
                        }
                    }
                }
            } elseif ($serviceType === 'livres') {
                // 1 seul enfant pour les livres (selon ton UX)
                if (count($selectedChildren) !== 1) {
                    return $this->apiResponse('Pour un achat de livres, sélectionnez un seul enfant.', 400);
                }
                $childId = $selectedChildren[0]['id'] ?? null;
                $child = $childId ? $this->entityManager->getRepository(Student::class)->find($childId) : null;
                if (!$child) return $this->apiResponse('Enfant introuvable.', 404);

                $payment = new Payment();
                $payment->setParent($parent);
                $payment->setStudent($child);
                $payment->setPaymentDate(new \DateTime());
                $payment->setPaymentType($data['paymentMethod'] ?? '');
                $payment->setServiceType('livres');
                $payment->setComment($data['comment'] ?? null);
                $payment->setInvoice($invoice);

                // Lignes livres
                $lines = $data['books'] ?? []; // [{bookId, quantity, unitPrice}]
                $sum = 0.00;

                foreach ($lines as $line) {
                    $book = $this->bookRepository->find($line['bookId'] ?? 0);
                    if (!$book) { continue; }

                    $qty  = max(1, (int)($line['quantity'] ?? 1));
                    $unit = (float)($line['unitPrice'] ?? $book->getSalePrice());
                    $total = round($qty * $unit, 2);

                    $item = new PaymentBookItem();
                    $item->setBook($book);
                    $item->setQuantity($qty);
                    $item->setUnitPrice(number_format($unit, 2, '.', ''));
                    $item->setLineTotal(number_format($total, 2, '.', ''));
                    $payment->addBookItem($item);
                    $payment->setProcessedBy($this->getUser());

                    $sum += $total;
                }

                // Montant payé : total - remise (ou paidAmount si déjà calculé côté front)
                $finalPaid = $paidAmount > 0 ? $paidAmount : max(0, $sum - $discount);
                $payment->setAmountPaid(number_format($finalPaid, 2, '.', ''));

                $this->entityManager->persist($payment);
            }

            $this->entityManager->persist($invoice);
            $this->entityManager->flush();
        }catch (\Exception $e) {
            return $this->apiErrorResponse('Erreur lors de la création du paiement : ' . $e->getMessage(), 500);
        }


        return $this->json([
            'message'    => 'Paiement(s) et facture créés avec succès.',
            'invoiceId'  => $invoice->getId(),
            'invoiceUrl' => $this->generateUrl('app_invoice_show', ['id' => $invoice->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
        ], 201);
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
