<?php

namespace Kunstmaan\MediaBundle\Tests\Helper\File;

use Kunstmaan\MediaBundle\Entity\Media;
use Kunstmaan\MediaBundle\Helper\File\PdfHandler;
use Kunstmaan\MediaBundle\Helper\Transformer\PreviewTransformerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\MimeTypes;

class PdfHandlerTest extends TestCase
{
    /** @var PdfHandler */
    protected $object;

    /** @var PreviewTransformerInterface */
    protected $pdfTransformer;

    /** @var string */
    protected $filesDir;

    protected function setUp(): void
    {
        $this->pdfTransformer = $this->createMock('Kunstmaan\MediaBundle\Helper\Transformer\PreviewTransformerInterface');
        $this->filesDir = realpath(__DIR__ . '/../../Files');

        $this->object = new PdfHandler(1, new MimeTypes());
        $this->object->setPdfTransformer($this->pdfTransformer);
    }

    public function testGetType()
    {
        $this->assertEquals(PdfHandler::TYPE, $this->object->getType());
    }

    public function testCanHandlePdfFiles()
    {
        $media = new Media();
        $media->setContent(new File($this->filesDir . '/sample.pdf'));
        $media->setContentType('application/pdf');

        $this->assertTrue($this->object->canHandle($media));
    }

    public function testCannotHandleNonPdfFiles()
    {
        $media = new Media();
        $media->setContent(new File($this->filesDir . '/sample.jpeg'));
        $media->setContentType('image/jpg');

        $this->assertFalse($this->object->canHandle($media));
        $this->assertFalse($this->object->canHandle(new \stdClass()));
    }

    public function testGetImageUrl()
    {
        $this->pdfTransformer
            ->expects($this->any())
            ->method('getPreviewFilename')
            ->willReturn('/media.pdf.jpg');

        $media = new Media();
        $media->setUrl('/path/to/media.pdf');
        $this->assertNull($this->object->getImageUrl($media, '/basepath'));

        $previewFilename = sys_get_temp_dir() . '/media.pdf.jpg';
        $fileSystem = new Filesystem();
        $fileSystem->touch($previewFilename);
        $media->setUrl('/media.pdf');
        $this->object->setWebPath(sys_get_temp_dir());
        $this->assertEquals('/media.pdf.jpg', $this->object->getImageUrl($media, ''));
        $fileSystem->remove($previewFilename);
    }
}
