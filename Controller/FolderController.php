<?php

namespace Kunstmaan\MediaBundle\Controller;

use Doctrine\ORM\EntityManager;
use Kunstmaan\AdminBundle\FlashMessages\FlashTypes;
use Kunstmaan\MediaBundle\AdminList\MediaAdminListConfigurator;
use Kunstmaan\MediaBundle\Entity\Folder;
use Kunstmaan\MediaBundle\Form\FolderType;
use Kunstmaan\MediaBundle\Helper\MediaManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * FolderController.
 */
class FolderController extends Controller
{
    /**
     * @param Request $request
     * @param int     $folderId The folder id
     *
     * @Route("/{folderId}", requirements={"folderId" = "\d+"}, name="KunstmaanMediaBundle_folder_show")
     * @Template("@KunstmaanMedia/Folder/show.html.twig")
     *
     * @return array
     */
    public function showAction(Request $request, $folderId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        // Check when user switches between thumb -and list view
        $viewMode = $request->query->get('viewMode');
        if ($viewMode && $viewMode == 'list-view') {
            $session->set('media-list-view', true);
        } elseif ($viewMode && $viewMode == 'thumb-view') {
            $session->remove('media-list-view');
        }

        /* @var MediaManager $mediaManager */
        $mediaManager = $this->get('kunstmaan_media.media_manager');

        /* @var Folder $folder */
        $folder = $em->getRepository('KunstmaanMediaBundle:Folder')->getFolder($folderId);

        $adminListConfigurator = new MediaAdminListConfigurator($em, $mediaManager, $folder, $request);
        $adminList = $this->get('kunstmaan_adminlist.factory')->createList($adminListConfigurator);
        $adminList->bindRequest($request);

        $sub = new Folder();
        $sub->setParent($folder);
        $subForm = $this->createForm(FolderType::class, $sub, array('folder' => $sub));

        $emptyForm = $this->createEmptyForm();

        $editForm = $this->createForm(FolderType::class, $folder, array('folder' => $folder));

        if ($request->isMethod('POST')) {
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {
                $em->getRepository('KunstmaanMediaBundle:Folder')->save($folder);

                $this->addFlash(
                    FlashTypes::SUCCESS,
                    $this->get('translator')->trans('media.folder.show.success.text', array(
                        '%folder%' => $folder->getName(),
                    ))
                );

                return new RedirectResponse(
                    $this->generateUrl(
                        'KunstmaanMediaBundle_folder_show',
                        array('folderId' => $folderId)
                    )
                );
            }
        }

        return array(
            'foldermanager' => $this->get('kunstmaan_media.folder_manager'),
            'mediamanager' => $this->get('kunstmaan_media.media_manager'),
            'subform' => $subForm->createView(),
            'emptyform' => $emptyForm->createView(),
            'editform' => $editForm->createView(),
            'folder' => $folder,
            'adminlist' => $adminList,
            'type' => null,
        );
    }

    /**
     * @param Request $request
     * @param int     $folderId
     *
     * @Route("/delete/{folderId}", requirements={"folderId" = "\d+"}, name="KunstmaanMediaBundle_folder_delete")
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $folderId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /* @var Folder $folder */
        $folder = $em->getRepository('KunstmaanMediaBundle:Folder')->getFolder($folderId);
        $folderName = $folder->getName();
        $parentFolder = $folder->getParent();

        if (\is_null($parentFolder)) {
            $this->addFlash(
                FlashTypes::DANGER,
                $this->get('translator')->trans('media.folder.delete.failure.text', array(
                    '%folder%' => $folderName,
                ))
            );
        } else {
            $em->getRepository('KunstmaanMediaBundle:Folder')->delete($folder);
            $this->addFlash(
                FlashTypes::SUCCESS,
                $this->get('translator')->trans('media.folder.delete.success.text', array(
                    '%folder%' => $folderName,
                ))
            );
            $folderId = $parentFolder->getId();
        }
        if (strpos($request->server->get('HTTP_REFERER', ''), 'chooser')) {
            $redirect = 'KunstmaanMediaBundle_chooser_show_folder';
        } else {
            $redirect = 'KunstmaanMediaBundle_folder_show';
        }

        $type = $this->get('request_stack')->getCurrentRequest()->get('type');

        return new RedirectResponse(
            $this->generateUrl($redirect,
                array(
                    'folderId' => $folderId,
                    'type' => $type,
                )
            )
        );
    }

    /**
     * @param Request $request
     * @param int     $folderId
     *
     * @Route("/subcreate/{folderId}", requirements={"folderId" = "\d+"}, name="KunstmaanMediaBundle_folder_sub_create", methods={"GET", "POST"})
     *
     * @return Response
     */
    public function subCreateAction(Request $request, $folderId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /* @var Folder $parent */
        $parent = $em->getRepository('KunstmaanMediaBundle:Folder')->getFolder($folderId);
        $folder = new Folder();
        $folder->setParent($parent);
        $form = $this->createForm(FolderType::class, $folder);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em->getRepository('KunstmaanMediaBundle:Folder')->save($folder);
                $this->addFlash(
                    FlashTypes::SUCCESS,
                    $this->get('translator')->trans('media.folder.addsub.success.text', array(
                        '%folder%' => $folder->getName(),
                    ))
                );
                if (strpos($request->server->get('HTTP_REFERER', ''), 'chooser') !== false) {
                    $redirect = 'KunstmaanMediaBundle_chooser_show_folder';
                } else {
                    $redirect = 'KunstmaanMediaBundle_folder_show';
                }

                $type = $request->get('type');

                return new RedirectResponse(
                    $this->generateUrl($redirect,
                        array(
                            'folderId' => $folder->getId(),
                            'type' => $type,
                        )
                    )
                );
            }
        }

        $galleries = $em->getRepository('KunstmaanMediaBundle:Folder')->getAllFolders();

        return $this->render(
            '@KunstmaanMedia/Folder/addsub-modal.html.twig',
            array(
                'subform' => $form->createView(),
                'galleries' => $galleries,
                'folder' => $folder,
                'parent' => $parent,
            )
        );
    }

    /**
     * @param Request $request
     * @param int     $folderId
     *
     * @Route("/empty/{folderId}", requirements={"folderId" = "\d+"}, name="KunstmaanMediaBundle_folder_empty", methods={"GET", "POST"})
     *
     * @return Response
     */
    public function emptyAction(Request $request, $folderId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /* @var Folder $folder */
        $folder = $em->getRepository('KunstmaanMediaBundle:Folder')->getFolder($folderId);

        $form = $this->createEmptyForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $alsoDeleteFolders = $data['checked'];

                $em->getRepository('KunstmaanMediaBundle:Folder')->emptyFolder($folder, $alsoDeleteFolders);

                $this->addFlash(
                    FlashTypes::SUCCESS,
                    $this->get('translator')->trans('media.folder.empty.success.text', array(
                        '%folder%' => $folder->getName(),
                    ))
                );
                if (strpos($request->server->get('HTTP_REFERER', ''), 'chooser') !== false) {
                    $redirect = 'KunstmaanMediaBundle_chooser_show_folder';
                } else {
                    $redirect = 'KunstmaanMediaBundle_folder_show';
                }

                return new RedirectResponse(
                    $this->generateUrl($redirect,
                        array(
                            'folderId' => $folder->getId(),
                            'folder' => $folder,
                        )
                    )
                );
            }
        }

        return $this->render(
            '@KunstmaanMedia/Folder/empty-modal.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @Route("/reorder", name="KunstmaanMediaBundle_folder_reorder")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function reorderAction(Request $request)
    {
        $folders = array();
        $nodeIds = $request->get('nodes');

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('KunstmaanMediaBundle:Folder');

        foreach ($nodeIds as $id) {
            /* @var Folder $folder */
            $folder = $repository->find($id);
            $folders[] = $folder;
        }

        foreach ($folders as $id => $folder) {
            $repository->moveDown($folder, true);
        }

        $em->flush();

        return new JsonResponse(
            array(
                'Success' => 'The node-translations for have got new weight values',
            )
        );
    }

    private function createEmptyForm()
    {
        $defaultData = array('checked' => false);
        $form = $this->createFormBuilder($defaultData)
            ->add('checked', CheckboxType::class, array('required' => false, 'label' => 'media.folder.empty.modal.checkbox'))
            ->getForm();

        return $form;
    }
}
