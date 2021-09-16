<?php

namespace App\Controller;

use App\Entity\Link;
use App\Form\LinkType;
use App\Repository\LinkRepository;
use ConvertApi\ConvertApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinkController extends AbstractController
{
    /**
     * list all the links that are configured in the system
     * @Route("/", name="listing")
     */
    public function index(LinkRepository $linkRepository): Response
    {
        return $this->render('link/index.html.twig', [
            'links' => $linkRepository->findAll()
        ]);
    }

    /**
     * view the details of the link in question as well as generate a PDF for a preview
     * @Route("/view/{uri}", name="details")
     */
    public function view(Link $link, Request $request, ConvertApi $convertApi): Response
    {
        $reattempt = ($request->query->get('reattempt', false) !== false);
        if(
            is_null($link->getPreview()) &&
            (
                $link->getPreviewAttempts() === 0 ||
                ( $link->getPreviewAttempts() >= 1 && $reattempt )
            )
        ){
            // todo: move this to a service
            $em = $this->getDoctrine()->getManager();
            $filesystem = new Filesystem();
            $link->incrementPreviewAttempt();
            try{
                $result = ConvertApi::convert('pdf', [ 'Url' => $link->getUrl() ], 'web');
                $basePath = $this->getParameter('kernel.project_dir').'/public';
                $sitePath = '/pdf/'.$link->getUri().'.pdf';
                $filesystem->dumpFile($basePath.$sitePath, $result->getFile()->getContents());
                $link->setPreview($sitePath);
            }catch (\Exception $e){
                // todo: feature enhancement, error checking?
            }
            $em->persist($link);
            $em->flush();
            $em->refresh($link);
            if($reattempt){
                return $this->redirectToRoute('details', ['uri' => $link->getUri()]);
            }
        }
        return $this->render('link/link.html.twig', [
            'link' => $link
        ]);
    }

    /**
     * edit the details of the link in question or create a new one
     * @Route("/edit/{uri}", name="edit")
     * @Route("/new", name="new")
     */
    public function edit(Request $request, Link $link = null): Response
    {
        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $link = $form->getData();
            $link->setUpdated(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($link);
            $em->flush();
            return $this->redirectToRoute('listing');
        }
        return $this->render('link/edit-link.html.twig', [
            'link' => $link, 'form' => $form->createView()
        ]);
    }

    /**
     * delete the link in question
     * @Route("/delete/{uri}", name="delete")
     */
    public function delete(Link $link): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($link);
        $em->flush();
        return $this->redirectToRoute('listing');
    }

    /**
     * Redirect the user to the configured domain
     * the analytic incrementation happens inside the event subscriber
     * this goes last so it will catch the URL in question
     * @Route("/{uri}", name="redirect")
     */
    public function redirectLink(Link $link): Response
    {
        return $this->redirect(
            $link->getUrl()
        );
    }


}
