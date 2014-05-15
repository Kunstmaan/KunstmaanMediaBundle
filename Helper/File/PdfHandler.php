<?php

namespace Kunstmaan\MediaBundle\Helper\File;

use Imagick;
use Kunstmaan\MediaBundle\Entity\Media;

/**
 * Custom handler for PDF files (display thumbnails if imagemagick is enabled and has PDF support)
 */
class PdfHandler extends FileHandler
{
    const TYPE = 'pdf';

    protected $mediaPath;

    /**
     * Inject the root dir so we know the full path where we need to store the file.
     *
     * @param string $kernelRootDir
     */
    public function setMediaPath($kernelRootDir)
    {
        parent::setMediaPath($kernelRootDir);

        $this->mediaPath = realpath($kernelRootDir . '/../web/uploads/media'). DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return PdfHandler::TYPE;
    }

    /**
     * @param mixed $object
     *
     * @return bool
     */
    public function canHandle($object)
    {
        if (parent::canHandle($object) &&
            ($object instanceof Media && $object->getContentType() == 'application/pdf') &&
            $this->canCreatePdfThumbnails()
        ) {
            return true;
        }

        return false;
    }

    private function canCreatePdfThumbnails()
    {
        if (!extension_loaded('imagick') || !class_exists('Imagick')) {
            return false;
        }

        $imagick = new Imagick();
        $pdfSupport = $imagick->queryFormats('PDF');

        return in_array('PDF', $pdfSupport);
    }

    /**
     * @param Media $media
     */
    public function saveMedia(Media $media)
    {
        parent::saveMedia($media);

        // Generate preview for PDF
        $this->createJpgPreview($media);
    }

    /**
     * @param Media  $media    The media entity
     * @param string $basepath The base path
     *
     * @return string
     */
    public function getImageUrl(Media $media, $basepath)
    {
        $pathParts = pathinfo($media->getUrl());

        return $basepath . $pathParts['dirname'] . DIRECTORY_SEPARATOR . $pathParts['filename'] . '.jpg';
    }

    /**
     * @param $media
     */
    public function createJpgPreview(Media $media)
    {
        $previewFilename = $this->mediaPath . $media->getUuid() . '.jpg';

        if (file_exists($previewFilename)) {
            return;
        }

        $preview = new Imagick($this->mediaPath . $media->getUuid() . '.pdf[0]');
        $preview->setImageFormat('jpg');
        $preview = $preview->flattenImages();
        $preview->writeImage($previewFilename);
    }

}