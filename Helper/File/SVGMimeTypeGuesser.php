<?php

namespace Kunstmaan\MediaBundle\Helper\File;

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

@trigger_error(sprintf('The "%s" class is deprecated since KunstmaanMediaBundle 5.7 and will be removed in KunstmaanMediaBundle 6.0. Use the "symfony/mime" component instead.', __CLASS__), E_USER_DEPRECATED);

/**
 * @deprecated this class is deprecated since KunstmaanMediaBundle 5.7 and will be removed in KunstmaanMediaBundle 6.0. Use the "symfony/mime" component instead.
 *
 * Simple Mime type guesser to detect SVG image files, it will test if the file is an XML file and return SVG mime type
 * if the XML contains a valid SVG namespace...
 */
class SVGMimeTypeGuesser implements MimeTypeGuesserInterface
{
    private $_MIMETYPE_NAMESPACES = [
        'http://www.w3.org/2000/svg' => 'image/svg+xml',
    ];

    /**
     * @return string|null
     */
    public function guess($path)
    {
        if (!is_file($path)) {
            throw new FileNotFoundException($path);
        }

        if (!is_readable($path)) {
            throw new AccessDeniedException($path);
        }

        if (!self::isSupported()) {
            return null;
        }

        $dom = new \DOMDocument();
        $xml = $dom->load($path, LIBXML_NOERROR + LIBXML_ERR_FATAL + LIBXML_ERR_NONE);
        if ($xml === false) {
            return null;
        }
        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('namespace::*') as $node) {
            if (isset($this->_MIMETYPE_NAMESPACES[$node->nodeValue])) {
                return $this->_MIMETYPE_NAMESPACES[$node->nodeValue];
            }
        }

        return null;
    }

    /**
     * Returns whether this guesser is supported on the current OS
     *
     * @return bool
     */
    public static function isSupported()
    {
        return class_exists('DOMDocument') && class_exists('DOMXPath');
    }
}
