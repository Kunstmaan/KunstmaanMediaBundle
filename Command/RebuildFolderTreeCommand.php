<?php
/**
 * Created by Kunstmaan.
 * Date: 08/07/14
 * Time: 15:44
 */

namespace Kunstmaan\MediaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RebuildFolderTreeCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('kuma:media:rebuild-folder-tree')
            ->setDescription('Rebuild the media folder tree.')
            ->setHelp("The <info>kuma:media:rebuild-folder-tree</info> will loop over all media folders and update the media folder tree.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // Reset tree...
        $sql  = 'UPDATE kuma_folders SET lvl=NULL,lft=NULL,rgt=NULL';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $repo = $em->getRepository('KunstmaanMediaBundle:Folder');
        $folders = $repo->findBy(array(), array('parent' => 'ASC', 'name' => 'asc'));

        $rootFolder = $folders[0];
        $first = true;
        foreach ($folders as $folder) {
            // Force parent load
            $parent = $folder->getParent();
            if (is_null($parent)) {
                $folder->setLevel(0);
                if ($first) {
                    $repo->persistAsFirstChild($folder);
                    $first = false;
                } else {
                    $repo->persistAsNextSiblingOf($folder, $rootFolder);
                }
            } else {
                $folder->setLevel($parent->getLevel() + 1);
                $repo->persistAsLastChildOf($folder, $parent);
            }
            $em->persist($folder);
        }
        $em->flush();

        $output->writeln('Updated all folders');
    }

} 