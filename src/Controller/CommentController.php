<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/comment")
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"})
     *
     * @param CommentRepository $commentRepository
     *
     * @return JsonResponse
     */
    public function index(CommentRepository $commentRepository): JsonResponse
    {
        return $this->json($commentRepository->findAll());
    }

    /**
     * @Route("/new", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function new(Request $request)
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return new JsonResponse($this->getErrorMessages($form), Response::HTTP_BAD_REQUEST);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($comment);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_CREATED);
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    protected function getErrorMessages(FormInterface $form)
    {
        $errors = [];

        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}
