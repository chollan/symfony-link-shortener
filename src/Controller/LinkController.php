<?php

namespace App\Controller;

use App\Entity\Link;
use App\Form\LinkType;
use App\Repository\LinkRepository;
use App\Service\PreviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinkController extends AbstractController
{
    /**
     * one of the main "actions" in th test documentation
     *
     * list all the links that are configured in the system
     * @Route("/", name="listing")
     */
    public function index(Request $request, LinkRepository $linkRepository): Response
    {
        $form = $this->createForm(LinkType::class, new Link());
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $link = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($link);
            $em->flush();
            return $this->redirectToRoute('details', ['uri' => $link->getUri()]);
        }
        return $this->render('link/index.html.twig', [
            'links' => $linkRepository->findAll(),
            'form' => $form->createView()
        ]);
    }

    /**
     * one of the main "actions" in th test documentation as well as
     * some additional feature functionality sprinkled through here
     *
     * view the details of the link in question as well as generate a PDF for a preview
     * @Route("/view/{uri}", name="details")
     * @param Link $link
     * @param Request $request
     * @param PreviewService $previewService
     * @return Response
     */
    public function view(Link $link, Request $request, PreviewService $previewService): Response
    {
        $reattempt = ($request->query->get('reattempt', false) !== false);
        if(
            is_null($link->getPreview()) &&
            (
                $link->getPreviewAttempts() === 0 ||
                ( $link->getPreviewAttempts() >= 1 && $reattempt )
            )
        ){
            $previewService->createPreview($link);
            if($reattempt){
                return $this->redirectToRoute('details', ['uri' => $link->getUri()]);
            }
        }
        return $this->render('link/link.html.twig', [
            'link' => $link
        ]);
    }

    /**
     * An "additional feature" as outlined in the test documentation
     *
     * edit the details of the link in question or create a new one
     * @Route("/edit/{uri}", name="edit")
     * @param Request $request
     * @param Link|null $link
     * @param PreviewService $previewService
     * @return Response
     */
    public function edit(Request $request, PreviewService $previewService, Link $link): Response
    {
        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $link = $form->getData();
            $link->setUpdated(new \DateTime());
            if(!is_null($link->getId())){
                // this is not a new item, assuming the URL has changed,
                // we'll need to remove the image to re-generate a screenshot
                $previewService->removePreview($link);
            }
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
     * An "additional feature" as outlined in the test documentation
     *
     * I went this route for deletion vs a form button because this is an easier
     * method than managing multiple forms on the layout page.
     * an option here is to add security to prevent accidental deletion.
     * delete the link in question
     * @Route("/delete/{uri}", name="delete")
     */
    public function delete(Link $link, PreviewService $previewService): Response
    {
        $previewService->removePreview($link);
        $em = $this->getDoctrine()->getManager();
        $em->remove($link);
        $em->flush();
        return $this->redirectToRoute('listing');
    }

    /**
     * one of the main "actions" in th test documentation
     *
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
