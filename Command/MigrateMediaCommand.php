<?php

namespace Kunstmaan\MediaBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateMediaCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('kuma:media:migrate')
            ->setDescription('Migrate old media to new table structure.')
            ->setHelp(
                "The <info>kuma:media:migrate</info> command can be used to migrate media to the new table structure."
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Migrating media...');
        /**
         * @var EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $medias = $em->getRepository('KunstmaanMediaBundle:Media')->findAll();
        /** @var Media $media */
        try {
            $em->beginTransaction();
            $updates = 0;
            foreach ($medias as $media) {
                $filename = $media->getOriginalFilename();
                if (empty($filename)) {
                    $media->setOriginalFilename($media->getName());
                    $em->persist($media);
                    $updates++;
                }
            }
            $em->flush();
            $em->commit();
            $output->writeln('<info>' .$updates . ' media files have been migrated.</info>');
        } catch (\Exception $e) {
            $em->rollback();
            $output->writeln('An error occured while migrating media : <error>' . $e->getMessage() . '</error>');
        }
    }
}