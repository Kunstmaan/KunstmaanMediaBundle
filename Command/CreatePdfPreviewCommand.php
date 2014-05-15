<?php

namespace Kunstmaan\MediaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreatePdfPreviewCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('kuma:media:create-pdf-previews')
            ->setDescription('Create preview images for PDFs that have already been uploaded')
            ->setHelp(
                "The <info>kuma:media:create-pdf-previews</info> command can be used to create preview images for PDFs that have already been uploaded."
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Creating PDF preview images...');

        /**
         * @var EntityManager
         */
        $em         = $this->getContainer()->get('doctrine.orm.entity_manager');
        $medias     = $em->getRepository('KunstmaanMediaBundle:Media')->findBy(
            array('contentType' => 'application/pdf', 'deleted' => false)
        );
        $pdfHandler = $this->getContainer()->get('kunstmaan_media.media_handlers.pdf');

        /** @var Media $media */
        try {
            foreach ($medias as $media) {
                $pdfHandler->createJpgPreview($media);
            }
            $output->writeln('<info>Missing PDF preview images have been created.</info>');
        } catch (\Exception $e) {
            $output->writeln(
                'An error occured while creating PDF preview images : <error>' . $e->getMessage() . '</error>'
            );
        }
    }
}