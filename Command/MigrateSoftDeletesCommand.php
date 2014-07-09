<?php
/**
 * Created by Kunstmaan.
 * Date: 08/07/14
 * Time: 15:44
 */

namespace Kunstmaan\MediaBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateSoftDeletesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('kuma:media:migrate-soft-deletes')
            ->setDescription('Migrate media for soft deletes.')
            ->setHelp(
                'The <info>kuma:media:migrate-soft-deletes</info> will loop over all media entries and set soft delete timestamps for deleted media.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $this->updateFolders($output, $em);
        $this->updateMedia($output, $em);
    }

    /**
     * @param OutputInterface $output
     * @param EntityManager   $em
     */
    protected function updateFolders(OutputInterface $output, $em)
    {
        $sql  = 'UPDATE kuma_folders SET deleted_at = updated_at WHERE deleted = true';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $output->writeln('Updated all folders');
    }

    /**
     * @param OutputInterface $output
     * @param EntityManager   $em
     */
    protected function updateMedia(OutputInterface $output, $em)
    {
        $sql  = 'UPDATE kuma_media SET deleted_at = updated_at WHERE deleted = true';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $output->writeln('Updated all media');
    }

} 