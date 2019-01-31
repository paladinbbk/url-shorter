<?php

namespace App\Controller;

use App\Entity\Url;
use App\Form\UrlType;
use App\Service\UrlShorter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ShorterController
 * @package App\Controller
 */
class ShorterController extends Controller
{
    /**
     * @Route("/", name="make_link")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UrlShorter $shorter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, EntityManagerInterface $entityManager, UrlShorter $shorter)
    {
        $shortUrl = null;
        $url = new Url();
        $form = $this->createForm(UrlType::class, $url);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($url);
            $entityManager->flush();

            $shortUrl = $this->generateUrl(
                'short_link', [
                'shortUrl' => $shorter->encode($url->getId())
            ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $this->render('shorter/index.html.twig', [
            'form' => $form->createView(),
            'shortUrl' => $shortUrl
        ]);
    }

    /**
     * @Route("/{shortUrl}", name="short_link")
     * @param $shortUrl
     * @param UrlShorter $shorter
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function shortLink($shortUrl, UrlShorter $shorter)
    {
        $url = $this->getDoctrine()
            ->getRepository(Url::class)
            ->find($shorter->decode($shortUrl));

        if (!($url instanceof Url)) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($url->getLink());
    }
}
