<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/book')]
class BookController extends AbstractController
{
    public function __construct(
        private BookRepository $bookRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/new', name: 'book_new', options: ['expose' => true], methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('book/new.html.twig');
    }

    #[Route('/list', name: 'app_book_list', options: ['expose' => true], methods: ['GET'])]
    public function list(): Response
    {
        $allBooks = $this->bookRepository->findAll();
        return $this->render('book/list.html.twig', [
            'bookList' => $allBooks,
        ]);
    }

    #[Route('/edit/{id}', name: 'book_edit', options: ['expose' => true], methods: ['GET'])]
    public function edit(Book $book): Response
    {
        return $this->render('book/edit.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/save', name: 'book_save', options: ['expose' => true], methods: ['POST'])]
    public function save(Request $request, SluggerInterface $slugger): Response
    {
        $book = new Book();
        $book->setName($request->request->get('name'));
        $book->setDescription($request->request->get('description'));
        $book->setLevel($request->request->get('level'));
        $book->setPurchasePrice($request->request->get('purchasePrice'));
        $book->setSalePrice($request->request->get('salePrice'));

        // Upload image si présente
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            $safeFilename = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move($this->getParameter('book_images_dir'), $newFilename);
                $book->setImageName($newFilename);
            } catch (FileException $e) {
                return $this->json(['error' => 'Erreur upload image'], 400);
            }
        }

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $this->json(['id' => $book->getId()]);
    }

    #[Route('/update/{id}', name: 'book_update', options: ['expose' => true], methods: ['POST'])]
    public function update(
        Book $book,
        Request $request,
        SluggerInterface $slugger
    ): Response {
        $filesystem = new Filesystem();
        $uploadDir  = $this->getParameter('book_images_dir');
        $oldImage   = $book->getImageName();

        // Champs simples
        $book->setName($request->request->get('name', $book->getName()));
        $book->setDescription($request->request->get('description', $book->getDescription()));
        $book->setLevel($request->request->get('level', $book->getLevel()));
        $book->setPurchasePrice($request->request->get('purchasePrice', $book->getPurchasePrice()));
        $book->setSalePrice($request->request->get('salePrice', $book->getSalePrice()));

        // Suppression d'image ?
        $removeImage = filter_var($request->request->get('removeImage', 'false'), FILTER_VALIDATE_BOOLEAN);
        if ($removeImage && $oldImage) {
            $oldPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $oldImage;
            if ($filesystem->exists($oldPath)) {
                $filesystem->remove($oldPath);
            }
            $book->setImageName(null);
            $oldImage = null;
        }

        // Nouveau fichier ?
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            $safeFilename = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move($uploadDir, $newFilename);

                // Supprime l’ancienne image si elle existe et est différente
                if ($oldImage) {
                    $oldPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $oldImage;
                    if ($filesystem->exists($oldPath)) {
                        $filesystem->remove($oldPath);
                    }
                }

                $book->setImageName($newFilename);
            } catch (FileException $e) {
                return $this->json(['message' => "Erreur d'upload de l'image"], 400);
            }
        }

        $this->entityManager->flush();

        return $this->json([
            'id' => $book->getId(),
            'imageName' => $book->getImageName(),   // null si supprimée
        ]);
    }
}
