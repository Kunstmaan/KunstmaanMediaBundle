<?php

namespace Kunstmaan\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Kunstmaan\MediaBundle\Entity\Image;
use Kunstmaan\MediaBundle\Helper\MediaHelper;
use Symfony\Component\HttpFoundation\File\File;

class AviaryController extends Controller
{

    public function indexAction($gallery_id, $image_id)
    {
        $ch = curl_init($_GET['url']);

        $url = parse_url($_GET['url']);
        $info = pathinfo($url['path']);
        $filename = $info['filename'].".".$info['extension'];
        $path = sys_get_temp_dir()."/".$filename;
        $savefile = fopen($path, 'w');

        curl_setopt($ch, CURLOPT_FILE, $savefile);
        curl_exec($ch);
        curl_close($ch);

        chmod($path, 777);

        $upload = new File($path);

        fclose($savefile);

        $gallery = $this->getImageGallery($gallery_id);

        $picturehelper = new MediaHelper();
        $picturehelper->setMedia( $upload );

        if ($picturehelper->getMedia()!=null) {
            $hulp = $this->getPicture($image_id);
            $picture = new Image();
            $picture->setName($hulp->getName()."-edited");
            $picture->setContent($picturehelper->getMedia());
            $picture->setOriginal($hulp);
            $picture->setGallery($gallery);

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($picture);
            $em->flush();

            $em = $this->getDoctrine()->getEntityManager();
            $galleries = $em->getRepository('KunstmaanMediaBundle:ImageGallery')
                            ->getAllGalleries();
            unlink($path);

            $picturehelper = new MediaHelper();
                            $form = $this->createForm(new \Kunstmaan\MediaBundle\Form\MediaType(), $picturehelper);

                            $sub = new \Kunstmaan\MediaBundle\Entity\ImageGallery();
                            $sub->setParent($gallery);
                            $subform = $this->createForm(new \Kunstmaan\MediaBundle\Form\SubGalleryType(), $sub);

                           //$picturehelp = $this->getPicture($picture->getId());
            return $this->render('KunstmaanMediaBundle:Gallery:show.html.twig', array(
                         'gallery' => $gallery,
                         'galleries' => $galleries,
                'form' => $form->createView(),
                                            'subform' => $subform->createView()
            ));
        }

        unlink($path);

        return $this->render('KunstmaanMediaBundle:Amazon:index.html.twig');
    }

    protected function getPicture($picture_id){
        $em = $this->getDoctrine()
                   ->getEntityManager();
        $picture = $em->getRepository('KunstmaanMediaBundle:Image')->find($picture_id);

        if (!$picture) {
            throw $this->createNotFoundException('Unable to find picture.');
        }

        return $picture;
    }

    protected function getImageGallery($gallery_id)
    {
        $em = $this->getDoctrine()
                    ->getEntityManager();
        $imagegallery = $em->getRepository('KunstmaanMediaBundle:ImageGallery')->find($gallery_id);

        if (!$imagegallery) {
            throw $this->createNotFoundException('Unable to find image gallery.');
        }

        return $imagegallery;
    }

}
