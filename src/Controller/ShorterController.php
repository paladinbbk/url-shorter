<?php

namespace App\Controller;

use App\Entity\Url;
use App\Form\UrlType;
use App\Repository\UrlRepository;
use App\Service\UrlShorter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ShorterController extends Controller
{
    /**
     * @Route("/", name="make_link")
     */
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        $shortUrl = null;

        $url = new Url();

        $form = $this->createForm(UrlType::class, $url);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($url);
            $entityManager->flush();
            $shorter = $this->get(UrlShorter::class);
            $shortUrl = $this->generateUrl('short_link', ['shortUrl' => $shorter->encode($url->getId())], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->render('shorter/index.html.twig', ['form' => $form->createView(), 'shortUrl' => $shortUrl]);
    }

    /**
     * @Route("/{shortUrl}", name="short_link")
     */
    public function shortLink($shortUrl, UrlRepository $urlRepository)
    {
        $shorter = $this->get(UrlShorter::class);

        $url = $urlRepository->find($shorter->decode($shortUrl));

        if (!$url) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($url->getLink());
    }
}
