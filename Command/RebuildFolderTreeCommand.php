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
            ->setHelp("The <info>kuma:media:rebuild-folder-tree</info> will loop over all node translation entries and update the urls for the entries.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $repo = $em->getRepository('KunstmaanMediaBundle:Folder');
        $folders = $repo->findBy(array('deleted' => false), array('parent' => 'ASC', 'name' => 'asc'));

        $rootFolder = $folders[0];
        foreach ($folders as $folder) {
            // Force parent load
            $parent = $folder->getParent();
            if (is_null($parent)) {
                $folder->setLevel(0);
            } else {
                $folder->setLevel($parent->getLevel() + 1);
            }
            $em->persist($folder);
        }
        $repo->verify();
        $repo->recover();
        $repo->reorder($rootFolder, 'name');
        $em->flush();

        $output->writeln('Updated all folders');
    }

} 