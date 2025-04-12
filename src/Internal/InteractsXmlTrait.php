<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Internal;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNamedNodeMap;
use DOMNode;
use InvalidArgumentException;

/**
 * Contain functions to interact with XML contents and XML DOM
 *
 * This class is internal, do not use it outside this project
 * @internal
 */
trait InteractsXmlTrait
{
    public function readXmlDocument(string $source): DOMDocument
    {
        if ('' === $source) {
            throw new InvalidArgumentException('Cannot load an xml with empty content');
        }
        $document = new DOMDocument();
        // as of libxml2 >= 1.11.0 it will truncate huge text nodes (like the zip files in base64)
        $document->loadXML($source, LIBXML_PARSEHUGE);
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

    /**
     * Find the element determined by the chain of children
     */
    public function findElement(DOMElement $element, string ...$names): ?DOMElement
    {
        $current = strtolower(strval(array_shift($names)));
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $localName = strtolower(strval($child->localName));
                if ($localName === $current) {
                    if (count($names) > 0) {
                        return $this->findElement($child, ...$names);
                    } else {
                        return $child;
                    }
                }
            }
        }
        return null;
    }

    public function findContent(DOMElement $element, string ...$names): string
    {
        $found = $this->findElement($element, ...$names);
        if (null === $found) {
            return '';
        }
        return $this->extractElementContent($found);
    }

    private function extractElementContent(DOMElement $element): string
    {
        $buffer = [];
        /** @var DOMNode $node */
        foreach ($element->childNodes as $node) {
            if (XML_TEXT_NODE === $node->nodeType) {
                $buffer[] = trim($node->textContent);
            }
        }
        return implode('', $buffer);
    }

    /**
     * @return DOMElement[]
     */
    public function findElements(DOMElement $element, string ...$names): array
    {
        $current = strtolower(strval(array_pop($names)));
        $element = $this->findElement($element, ...$names);
        if (null === $element) {
            return [];
        }

        $found = [];
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $localName = strtolower(strval($child->localName));
                if ($localName === $current) {
                    $found[] = $child;
                }
            }
        }
        return $found;
    }

    /**
     * @return string[]
     */
    public function findContents(DOMElement $element, string ...$names): array
    {
        return array_map(
            fn (DOMElement $element) => $this->extractElementContent($element),
            $this->findElements($element, ...$names)
        );
    }

    /**
     * Find the element determined by the chain of children and return the attributes as an
     * array using the attribute name as array key and attribute value as entry value.
     *
     * @return array<string, string>
     */
    public function findAttributes(DOMElement $element, string ...$search): array
    {
        $found = $this->findElement($element, ...$search);
        if (null === $found) {
            return [];
        }
        $attributes = [];
        /**
         * @var DOMNamedNodeMap<DOMAttr> $elementAttributes
         * phpstan doesn't know that $found->attributes cannot be null since $found is a DOMElement
         */
        $elementAttributes = $found->attributes;
        foreach ($elementAttributes as $attribute) {
            $attributes[$attribute->localName] = $attribute->value;
        }
        return array_change_key_case($attributes, CASE_LOWER);
    }
}
