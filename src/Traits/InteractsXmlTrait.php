<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Traits;

use DOMAttr;
use DOMDocument;
use DOMElement;
use InvalidArgumentException;

/**
 * @internal
 */
trait InteractsXmlTrait
{
    public function nospaces(string $input): string
    {
        return implode('', array_filter(array_map(function (string $line): string {
            return trim($line);
        }, explode("\n", str_replace("\r", '', $input)))));
    }

    public function readXmlDocument(string $source): DOMDocument
    {
        if ('' === $source) {
            throw new InvalidArgumentException('Cannot load an xml with empty content');
        }
        $document = new DOMDocument();
        $document->loadXML($source);
        return $document;
    }

    public function readXmlElement(string $source): DOMElement
    {
        $document = $this->readXmlDocument($source);
        /** @var DOMElement|null $element */
        $element = $document->documentElement;
        if (null === $element) {
            throw new InvalidArgumentException('Cannot load an xml without document element');
        }
        return $element;
    }

    public function findElement(DOMElement $element, string ... $names): ?DOMElement
    {
        $current = array_shift($names);
        $current = strtolower($current);
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $localName = strtolower($child->localName);
                if ($localName === $current) {
                    if (count($names) > 0) {
                        return $this->findElement($child, ... $names);
                    } else {
                        return $child;
                    }
                }
            }
        }
        return null;
    }

    public function findContent(DOMElement $element, string ... $names): string
    {
        $found = $this->findElement($element, ... $names);
        if (null === $found) {
            return '';
        }
        return $found->textContent;
    }

    public function findAttribute(DOMElement $element, string ...$search): string
    {
        $attributeName = strtolower(array_pop($search));
        $found = $this->findElement($element, ... $search);
        if (null === $found) {
            return '';
        }
        foreach ($found->attributes as $attribute) {
            if ($attribute instanceof DOMAttr) {
                $name = strtolower($attribute->localName);
                if ($name === $attributeName) {
                    return $attribute->textContent;
                }
            }
        }
        return '';
    }
}
