<?php

namespace App\Controller;

use App\Repository\ParentsRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Invoice;
use App\Form\InvoiceType;
use App\Model\ApiResponseTrait;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\MailService;
use Symfony\Component\Serializer\SerializerInterface;


#[IsGranted('ROLE_MANAGER')]
#[Route('/invoice')]
final class InvoiceController extends AbstractController
{
    use ApiResponseTrait;
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InvoiceRepository $invoiceRepository,
        private  MailService $mailService,
    ){
    }

    #[Route('/search-invoice', name: 'app_search_invoice', options: ['expose' => true], methods: ['GET'])]
    public function searchInvoice(
        Request $request,
        SerializerInterface $serializer,
        ParentsRepository $parentsRepository,
    ): Response {
        $parentId = (int) $request->query->get('parentId', 0);

        if ($parentId <= 0) {
            return $this->apiErrorResponse('Paramètre "parentId" manquant ou invalide.', Response::HTTP_BAD_REQUEST);
        }

        $parent = $parentsRepository->find($parentId);
        if (!$parent) {
            return $this->apiErrorResponse('Aucun parent trouvé pour cet identifiant.', Response::HTTP_NOT_FOUND);
        }

        // Récupération des factures du parent
        $invoices = $this->invoiceRepository->createQueryBuilder('i')
            ->leftJoin('i.payments', 'p')->addSelect('p')
            ->andWhere('i.parent = :parent')->setParameter('parent', $parent)
            ->orderBy('i.invoiceDate', 'DESC')
            ->getQuery()
            ->getResult();


        // Sérialisation avec groupe read_invoice_for_refund
        $context = [
            'groups' => ['read_invoice_for_refund'],
            'enable_max_depth' => true,
            'circular_reference_handler' => function ($object) {
                return method_exists($object, 'getId') ? $object->getId() : spl_object_id($object);
            },
        ];
        $serialized = $serializer->serialize($invoices, 'json', $context);
        $payload = json_decode($serialized, true);
        // Réponse OK (NB: apiResponse attend ($data, $code))
        return $this->apiResponse(['invoices' => $payload], Response::HTTP_OK);
    }

    #[Route(name: 'app_invoice_index', options: ['expose' => true], methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $isSuperAdmin = $this->isGranted('ROLE_ADMIN');
        return $this->render('invoice/index.html.twig', [
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    #[Route('/all', name: 'all_invoices', options: ['expose' => true], methods: ['GET'])]
    public function getAllInvoices(Request $request): JsonResponse
    {
        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = 100;
        $offset = ($page - 1) * $limit;

        // On récupère limit + 1 éléments pour savoir s'il reste une page derrière
        $invoices = $this->invoiceRepository->findBy(
            [],
            ['invoiceDate' => 'DESC'],
            $limit + 1,
            $offset
        );

        $hasMore = count($invoices) > $limit;

        if ($hasMore) {
            // on enlève le 101e pour ne renvoyer que 100
            array_pop($invoices);
        }

        return $this->json([
            'allInvoices' => $invoices,
            'page'        => $page,
            'hasMore'     => $hasMore,
        ], 200, [], ['groups' => 'read_invoice']);
    }

    #[Route('/new', name: 'app_invoice_new', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $invoice = new Invoice();
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/new.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_show', options: ['expose' => true], methods: ['GET'])]
    public function show(Invoice $invoice): Response
    {
        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_invoice_edit', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function edit(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_delete', options: ['expose' => true], methods: ['POST'])]
    public function delete(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete/{id}', name: 'invoice_delete', options: ['expose' => true], methods: ['DELETE'])]
    public function deletePayment(int $id): JsonResponse
    {
        $invoice = $this->invoiceRepository->find($id);

        if (!$invoice) {
            return $this->json([
                'message' => "Facture introuvable.",
            ], 404);
        }

        // Suppression des paiements associés
        foreach ($invoice->getPayments() as $payment) {
            $this->entityManager->remove($payment);
        }

        // Suppression de la facture
        $this->entityManager->remove($invoice);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'La facture a été supprimée et tous les paiements correspondants ont été supprimés avec succès.',
        ], 200);
    }

    #[Route('/invoice/send-email/{id}', name: 'invoice_send_email', options: ['expose' => true], methods: ['POST'])]
    public function sendInvoiceEmail(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        MailService $mailService
    ): Response {
        $invoice = $entityManager->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            return $this->json(['error' => 'Facture introuvable.'], 404);
        }

        try {
            // Récupérer l'email depuis la requête
            $email = $request->request->get('email');
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->json(['error' => 'Adresse email invalide.'], 400);
            }

            // Configurer Dompdf
            $options = new Options();
            $options->set('defaultFont', 'Arial');
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);

            // Générer le HTML de la facture
            $html = $this->renderView('invoice/pdf.html.twig', [
                'invoice' => $invoice,
                'parentFullName' => $invoice->getParent()->getFullNameParent(),
            ]);

            // Charger le HTML et générer le PDF
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Sauvegarder le PDF temporairement
            $pdfOutput = $dompdf->output();
            $pdfPath = sys_get_temp_dir() . '/facture_' . $invoice->getId() . '.pdf';
            file_put_contents($pdfPath, $pdfOutput);

            // Envoi de l'email avec le PDF en pièce jointe
            $mailService->sendEmail(
                $email, // Adresse email saisie par l'utilisateur
                'Votre facture ' . $invoice->getId(),
                'email/invoice.html.twig',
                [
                    'invoice' => $invoice,
                    'parentFullName' => $invoice->getParent()->getFullNameParent(),
                ],
                $pdfPath
            );

            return $this->json(['message' => 'Email envoyé avec succès à ' . $email], 200);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Une erreur est survenue lors de l\'envoi de l\'email : ' . $e->getMessage()], 500);
        } finally {
            // Nettoyer le fichier temporaire
            if (isset($pdfPath) && file_exists($pdfPath)) {
                unlink($pdfPath);
            }
        }
    }
}
