<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Refund;
use App\Model\ApiResponseTrait;
use App\Repository\ParentsRepository;
use App\Repository\PaymentRepository;
use App\Repository\RefundRepository;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/refunds')]
class RefundController extends AbstractController
{
    use ApiResponseTrait;
    public function __construct(
        private EntityManagerInterface $em,
        private RefundRepository $refundRepo,
        private ParentsRepository $parentRepo,
        private InvoiceRepository $invoiceRepo,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {}



    #[Route('/list',name: 'app_refund_index', options: ['expose' => true], methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): Response
    {
        return $this->render('refund/list.html.twig', [
            'currentUser' => $this->getUser(),
        ]);
    }


    #[Route('/new',name: 'app_refund_new', options: ['expose' => true], methods: ['GET'])]
    public function new(EntityManagerInterface $entityManager): Response
    {
        $parents = $this->parentRepo->findAll();
        return $this->render('refund/new.html.twig', [
            'currentUser' => $this->getUser(),
            'parents' => $parents,
        ]);
    }

    #[Route('/', name: 'api_refund_index',  options: ['expose' => true], methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $criteria = [
            'parentId' => $request->query->getInt('parentId') ?: null,
            'status'   => $request->query->get('status'),
            'q'        => $request->query->get('q'),
            'page'     => $request->query->getInt('page', 1),
            'limit'    => $request->query->getInt('limit', 20),
        ];

        if ($df = $request->query->get('dateFrom')) {
            $criteria['dateFrom'] = new \DateTimeImmutable($df);
        }
        if ($dt = $request->query->get('dateTo')) {
            $criteria['dateTo'] = new \DateTimeImmutable($dt);
        }

        $result = $this->refundRepo->search($criteria);

        $data = [
            'items' => json_decode($this->serializer->serialize($result['items'], 'json', [
                'groups' => ['read_refund', 'read_invoice'],
            ]), true),
            'total' => $result['total'],
            'page'  => $result['page'],
            'limit' => $result['limit'],
        ];

        return $this->json($data, 200);
    }

    #[Route('/{id}', name: 'api_refund_show', options: ['expose' => true],  methods: ['GET'])]
    public function show(Refund $refund): Response
    {
        return $this->render('refund/show.html.twig', [
            'currentUser' => $this->getUser(),
            'refund' => $refund,
        ]);
    }

    /**
     * Crée un remboursement à partir d'une sélection de paiements.
     *
     * Payload JSON attendu:
     * {
     *   "parentId": 123,
     *   "paymentIds": [463, 464, 1238],
     *   "refundAmount": 120.00,
     *   "refundMethod": "Chèque",
     *   "comment": "Cours annulé"
     * }
     */
    #[Route('/create', name: 'app_refund_create', options: ['expose' => true], methods: ['POST'])]
    public function create(Request $request, SerializerInterface $serializer,UrlGeneratorInterface $urlGenerator, ParentsRepository $parentsRepository, PaymentRepository $paymentRepository): Response
    {
        // ---- 1) Lire / valider le payload
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->apiErrorResponse('Payload JSON invalide.', Response::HTTP_BAD_REQUEST);
        }

        $parentId     = (int)($data['parentId'] ?? 0);
        $paymentIds   = array_values(array_unique(array_filter($data['paymentIds'] ?? [], fn($v) => $v !== null && $v !== '')));
        $refundAmount = (string)($data['refundAmount'] ?? '0'); // stocké en string (decimal)
        $refundMethod = trim((string)($data['refundMethod'] ?? ''));
        $comment      = trim((string)($data['comment'] ?? ''));

        if ($parentId <= 0) {
            return $this->apiErrorResponse('Paramètre "parentId" manquant ou invalide.', Response::HTTP_BAD_REQUEST);
        }
        if (empty($paymentIds)) {
            return $this->apiErrorResponse('Aucun paiement sélectionné.', Response::HTTP_BAD_REQUEST);
        }
        if (!is_numeric($refundAmount) || (float)$refundAmount <= 0) {
            return $this->apiErrorResponse('Le montant de remboursement doit être un nombre positif.', Response::HTTP_BAD_REQUEST);
        }
        if ($refundMethod === '') {
            return $this->apiErrorResponse('La méthode de remboursement est requise.', Response::HTTP_BAD_REQUEST);
        }

        // ---- 2) Charger le parent
        $parent = $parentsRepository->find($parentId);
        if (!$parent) {
            return $this->apiErrorResponse('Parent introuvable.', Response::HTTP_NOT_FOUND);
        }

        // ---- 3) Charger les paiements
        // ---- 3) Charger les paiements (et filtrer par parent directement)
        $qb = $paymentRepository->createQueryBuilder('pay')
            ->innerJoin('pay.invoice', 'inv')
            ->innerJoin('inv.parent', 'par')
            ->addSelect('inv', 'par')
            ->andWhere('pay.id IN (:ids)')
            ->andWhere('par.id = :parentId')
            ->setParameter('ids', $paymentIds)
            ->setParameter('parentId', $parentId);

        $payments = $qb->getQuery()->getResult();

        if (count($payments) !== count($paymentIds)) {
            // au moins 1 id invalide
            $foundIds = array_map(fn($p) => $p->getId(), $payments);
            $missing  = array_values(array_diff($paymentIds, $foundIds));
            return $this->apiErrorResponse('Certains paiements sont introuvables.', Response::HTTP_BAD_REQUEST, ['missingPaymentIds' => $missing]);
        }

        // ---- 4) Vérifier que tous les paiements appartiennent au même parent
        foreach ($payments as $p) {
            $inv = $p->getInvoice();
            if (!$inv || !$inv->getParent() || $inv->getParent()->getId() !== $parentId) {
                return $this->apiErrorResponse(
                    'Tous les paiements doivent appartenir au parent sélectionné.',
                    Response::HTTP_BAD_REQUEST,
                    ['offendingPaymentId' => $p->getId()]
                );
            }
        }

        // ---- 5) Calculer la somme sélectionnée (decimal)
        // Utilise bcadd si dispo (meilleure précision), sinon float avec round(2)
        $sumSelected = '0.00';
        $hasBc = function_exists('bcadd');
        foreach ($payments as $p) {
            $amt = (string)$p->getAmountPaid(); // suppose Payment::getAmountPaid() renvoie string (decimal)
            if ($hasBc) {
                $sumSelected = bcadd($sumSelected, (string)$amt, 2);
            } else {
                $sumSelected = number_format((float)$sumSelected + (float)$amt, 2, '.', '');
            }
        }

        // ---- 6) Valider refundAmount <= sumSelected
        $refundAmountStr = number_format((float)$refundAmount, 2, '.', '');
        $isTooHigh = $hasBc
            ? (bccomp($refundAmountStr, $sumSelected, 2) === 1) // refund > sum
            : ((float)$refundAmountStr > (float)$sumSelected);

        if ($isTooHigh) {
            return $this->apiErrorResponse(
                'Le montant à rembourser excède le total des paiements sélectionnés.',
                Response::HTTP_BAD_REQUEST,
                ['refundAmount' => $refundAmountStr, 'totalSelected' => $sumSelected]
            );
        }

        // ---- 7) Construire l’entité Refund + lier les factures (uniques) des paiements
        $refund = new Refund();
        $refund->setParent($parent);
        $refund->setAmount($refundAmountStr);
        $refund->setMethod($refundMethod);
        $refund->setComment($comment);
        // status par défaut = 'pending' (dans l’entité)
        // refundDate gérée par le constructeur / prePersist

        // lier les factures concernées (sans doublons)
        $invoiceById = [];
        foreach ($payments as $p) {
            $inv = $p->getInvoice();
            if ($inv instanceof Invoice) {
                $invoiceById[$inv->getId()] = $inv;
            }
        }
        foreach ($invoiceById as $inv) {
            $refund->addInvoice($inv);
        }

        // ---- 8) Persister
        try {
            $this->em->persist($refund);
            $this->em->flush();
        } catch (\Throwable $e) {
            return $this->apiErrorResponse(
                'Erreur lors de la création du remboursement.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['exception' => $e->getMessage()]
            );
        }

        // ---- 9) Sérialiser la réponse
        // On expose le remboursement + quelques métadonnées utiles
        $context = [
            'groups' => ['read_refund', 'read_invoice'], // inclut refund + (min) infos facture
            'enable_max_depth' => true,
            'circular_reference_handler' => fn($obj) => method_exists($obj, 'getId') ? $obj->getId() : spl_object_id($obj),
        ];

        $normalizedRefund = json_decode($serializer->serialize($refund, 'json', $context), true);

        $redirectUrl = $urlGenerator->generate('api_refund_show', ['id' => $refund->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // réponse
        return $this->apiResponse([
            'text'   => 'Remboursement créé avec succès.',
            'refund' => json_decode($serializer->serialize($refund, 'json', [
                'groups' => ['read_refund', 'read_invoice'],
                'enable_max_depth' => true,
                'circular_reference_handler' => fn($o) => method_exists($o,'getId') ? $o->getId() : spl_object_id($o),
            ]), true),
            'redirect' => $redirectUrl,
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_refund_update',  options: ['expose' => true], methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Refund $refund): JsonResponse
    {
        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // Parent (optionnel en update)
        if (array_key_exists('parentId', $payload)) {
            $parent = $payload['parentId'] ? $this->parentRepo->find((int)$payload['parentId']) : null;
            if (!$parent) {
                return $this->json(['error' => 'Parent not found'], 404);
            }
            $refund->setParent($parent);
        }

        if (array_key_exists('amount', $payload) && $payload['amount'] !== null) {
            $refund->setAmount((string)$payload['amount']);
        }

        if (array_key_exists('refundDate', $payload)) {
            $refund->setRefundDate($payload['refundDate'] ? new \DateTimeImmutable($payload['refundDate']) : new \DateTimeImmutable());
        }

        if (array_key_exists('method', $payload))    { $refund->setMethod($payload['method']); }
        if (array_key_exists('status', $payload))    { $refund->setStatus($payload['status']); }
        if (array_key_exists('comment', $payload))   { $refund->setComment($payload['comment']); }
        if (array_key_exists('reference', $payload)) { $refund->setReference($payload['reference']); }

        // Remplacement complet de la liste de factures si 'invoiceIds' est fourni (sinon on ne touche pas)
        if (array_key_exists('invoiceIds', $payload) && is_array($payload['invoiceIds'])) {
            // Clear existants
            foreach ($refund->getInvoices()->toArray() as $inv) {
                $refund->removeInvoice($inv);
            }
            if (!empty($payload['invoiceIds'])) {
                $invoices = $this->invoiceRepo->findBy(['id' => $payload['invoiceIds']]);
                foreach ($invoices as $invoice) {
                    $refund->addInvoice($invoice);
                }
            }
        }

        $errors = $this->validator->validate($refund);
        if (count($errors) > 0) {
            return $this->json(['error' => (string)$errors], 400);
        }

        $this->em->flush();

        return $this->json($refund, 200, [], ['groups' => ['read_refund', 'read_invoice']]);
    }

    #[Route('/{id}', name: 'api_refund_delete',  options: ['expose' => true], methods: ['DELETE'])]
    public function delete(Refund $refund): JsonResponse
    {
        $this->em->remove($refund);
        $this->em->flush();

        return $this->json(null, 204);
    }

    #[Route('/{id}/invoices', name: 'api_refund_sync_invoices',  options: ['expose' => true], methods: ['POST'])]
    public function syncInvoices(Request $request, Refund $refund): JsonResponse
    {
        // Petit endpoint pratique pour ajouter/retirer sans refaire tout l'update
        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $add = is_array($payload['add'] ?? null) ? $payload['add'] : [];
        $remove = is_array($payload['remove'] ?? null) ? $payload['remove'] : [];

        if ($add) {
            $invoices = $this->invoiceRepo->findBy(['id' => $add]);
            foreach ($invoices as $invoice) {
                $refund->addInvoice($invoice);
            }
        }

        if ($remove) {
            $invoices = $this->invoiceRepo->findBy(['id' => $remove]);
            foreach ($invoices as $invoice) {
                $refund->removeInvoice($invoice);
            }
        }

        $this->em->flush();

        return $this->json($refund, 200, [], ['groups' => ['read_refund', 'read_invoice']]);
    }

    // ParentsController.php
    #[Route('/api/parents', name: 'api_parents_search', options: ['expose' => true], methods: ['GET'])]
    public function searchParents(Request $request, ParentsRepository $repo): JsonResponse
    {
        $q = trim((string)$request->query->get('q', ''));
        $limit = (int)$request->query->get('limit', 10);

        if ($q === '') return $this->json(['items' => []]);

        $items = $repo->searchByNameOrEmail($q, $limit); // à implémenter dans le repo
        return $this->json(['items' => json_decode($this->serializer->serialize($items, 'json', [
            'groups' => ['read_refund', 'read_invoice'] // assure le nom du parent
        ]), true)]);
    }

// InvoiceController.php
    #[Route('/api/invoices', name: 'api_invoices_search', options: ['expose' => true], methods: ['GET'])]
    public function searchInvoices(Request $request, InvoiceRepository $repo): JsonResponse
    {
        $q = trim((string)$request->query->get('q', ''));
        $limit = (int)$request->query->get('limit', 10);
        $items = $repo->searchLight($q, $limit); // renvoie id, invoiceDate, totalAmount
        return $this->json(['items' => $items]);
    }

}
