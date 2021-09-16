<?php

namespace App\Service;

use App\Entity\Link;
use ConvertApi\ConvertApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class PreviewService
{
    /**
     * @var ConvertApi
     */
    private $convertApi;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string base path to the public directory
     */
    private $basePath;

    function __construct(ConvertApi $convertApi, EntityManagerInterface $em, ParameterBagInterface $parameterBag){
        $this->convertApi = $convertApi;
        $this->em = $em;
        $this->parameterBag = $parameterBag;
        $this->filesystem = new Filesystem();
        $this->basePath = $this->parameterBag->get('kernel.project_dir').'/public';
    }

    /**
     * attempt to generate a preview for this link
     * @param Link $link
     * @return bool
     */
    public function createPreview(Link $link): bool{
        $filesystem = new Filesystem();
        $link->incrementPreviewAttempt();
        try{
            // try the conversion
            $result = $this->convertApi->convert('pdf', [ 'Url' => $link->getUrl() ], 'web');

            // set some paths for this request
            $sitePath = '/pdf/'.$link->getUri().'.pdf';

            // save the file
            $filesystem->dumpFile($this->basePath.$sitePath, $result->getFile()->getContents());

            // set the path on the link object
            $link->setPreview($sitePath);
        }catch (\Exception $e){
            return false;
            // todo: feature enhancement, error checking?
        }
        $this->em->persist($link);
        $this->em->flush();
        $this->em->refresh($link);
        return true;
    }

    /**
     * remove the file and update the db record
     * @param Link $link
     * @return bool
     */
    public function removePreview(Link $link): bool {
        $path = '/pdf/'.$link->getUri().'.pdf';

        if(!$this->filesystem->exists($this->basePath.$path)) return true;

        try{
            $this->filesystem->remove($this->basePath.$path);
        }catch (\Exception $e){
            return false;
        }

        $link->setPreview(null);
        $link->setPreviewAttempts(0);
        $this->em->persist($link);
        $this->em->flush();
        $this->em->refresh($link);

        return true;
    }
}