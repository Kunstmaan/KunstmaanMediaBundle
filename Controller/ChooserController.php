<?php

namespace Kunstmaan\MediaBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Kunstmaan\MediaBundle\AdminList\MediaAdminListConfigurator;
use Kunstmaan\MediaBundle\Entity\Folder;
use Kunstmaan\MediaBundle\Entity\Media;
use Kunstmaan\MediaBundle\Form\FolderType;
use Kunstmaan\MediaBundle\Helper\Media\AbstractMediaHandler;
use Kunstmaan\MediaBundle\Helper\MediaManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @final since 5.9
 */
class ChooserController extends Controller
{
    private const TYPE_ALL = 'all';

    /**
     * @Route("/chooser", name="KunstmaanMediaBundle_chooser")
     *
     * @return RedirectResponse
     */
    public function chooserIndexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $folderId = false;

        $type = $request->get('type', self::TYPE_ALL);
        $cKEditorFuncNum = $request->get('CKEditorFuncNum');
        $linkChooser = $request->get('linkChooser');

        // Go to the last visited folder
        if ($session->get('last-media-folder')) {
            try {
                $em->getRepository(Folder::class)->getFolder($session->get('last-media-folder'));
                $folderId = $session->get('last-media-folder');
            } catch (EntityNotFoundException $e) {
                $folderId = false;
            }
        }

        if (!$folderId) {
            // Redirect to the first top folder
            /* @var Folder $firstFolder */
            $firstFolder = $em->getRepository(Folder::class)->getFirstTopFolder();
            $folderId = $firstFolder->getId();
        }

        $params = [
            'folderId' => $folderId,
            'type' => $type,
            'CKEditorFuncNum' => $cKEditorFuncNum,
            'linkChooser' => $linkChooser,
        ];

        return $this->redirect($this->generateUrl('KunstmaanMediaBundle_chooser_show_folder', $params));
    }

    /**
     * @param int $folderId The folder id
     *
     * @Route("/chooser/{folderId}", requirements={"folderId" = "\d+"}, name="KunstmaanMediaBundle_chooser_show_folder")
     * @Template("@KunstmaanMedia/Chooser/chooserShowFolder.html.twig")
     *
     * @return array
     */
    public function chooserShowFolderAction(Request $request, $folderId)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        $type = $request->get('type');
        $cKEditorFuncNum = $request->get('CKEditorFuncNum');
        $linkChooser = $request->get('linkChooser');

        // Remember the last visited folder in the session
        $session->set('last-media-folder', $folderId);

        // Check when user switches between thumb -and list view
        $viewMode = $request->query->get('viewMode');
        if ($viewMode && $viewMode == 'list-view') {
            $session->set('media-list-view', true);
        } elseif ($viewMode && $viewMode == 'thumb-view') {
            $session->remove('media-list-view');
        }

        /* @var MediaManager $mediaHandler */
        $mediaHandler = $this->get('kunstmaan_media.media_manager');

        /* @var Folder $folder */
        $folder = $em->getRepository(Folder::class)->getFolder($folderId);

        /** @var AbstractMediaHandler $handler */
        $handler = null;
        if ($type && $type !== self::TYPE_ALL) {
            $handler = $mediaHandler->getHandlerForType($type);
        }

        /* @var MediaManager $mediaManager */
        $mediaManager = $this->get('kunstmaan_media.media_manager');

        $adminListConfigurator = new MediaAdminListConfigurator($em, $mediaManager, $folder, $request);
        $adminList = $this->get('kunstmaan_adminlist.factory')->createList($adminListConfigurator);
        $adminList->bindRequest($request);

        $sub = new Folder();
        $sub->setParent($folder);
        $subForm = $this->createForm(FolderType::class, $sub, ['folder' => $sub]);

        $linkChooserLink = null;
        if (!empty($linkChooser)) {
            $params = [];
            if (!empty($cKEditorFuncNum)) {
                $params['CKEditorFuncNum'] = $cKEditorFuncNum;
                $routeName = 'KunstmaanNodeBundle_ckselecturl';
            } else {
                $routeName = 'KunstmaanNodeBundle_selecturl';
            }
            $linkChooserLink = $this->generateUrl($routeName, $params);
        }

        $viewVariabels = [
            'cKEditorFuncNum' => $cKEditorFuncNum,
            'linkChooser' => $linkChooser,
            'linkChooserLink' => $linkChooserLink,
            'mediamanager' => $mediaManager,
            'foldermanager' => $this->get('kunstmaan_media.folder_manager'),
            'handler' => $handler,
            'type' => $type,
            'folder' => $folder,
            'adminlist' => $adminList,
            'subform' => $subForm->createView(),
        ];

        /* generate all forms */
        $forms = [];

        foreach ($mediaManager->getFolderAddActions()  as $addAction) {
            $forms[$addAction['type']] = $this->createTypeFormView($mediaHandler, $addAction['type']);
        }

        $viewVariabels['forms'] = $forms;

        return $viewVariabels;
    }

    /**
     * @param string $type
     *
     * @return \Symfony\Component\Form\FormView
     */
    private function createTypeFormView(MediaManager $mediaManager, $type)
    {
        $handler = $mediaManager->getHandlerForType($type);
        $media = new Media();
        $helper = $handler->getFormHelper($media);

        return $this->createForm($handler->getFormType(), $helper, $handler->getFormTypeOptions())->createView();
    }
}
