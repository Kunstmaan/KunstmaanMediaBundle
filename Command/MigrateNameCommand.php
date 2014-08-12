<?php

namespace Kunstmaan\MediaBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateNameCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('kuma:media:migrate-name')
            ->setDescription('Migrate media name to new column.')
            ->setHelp(
                "The <info>kuma:media:migrate-name</info> command can be used to migrate the media name to the newly added column."
            );
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Migrating media name...');
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
            $output->writeln('An error occured while migrating media name : <error>' . $e->getMessage() . '</error>');
        }
    }
}