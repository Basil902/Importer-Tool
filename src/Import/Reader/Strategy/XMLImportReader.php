<?php

namespace App\Import\Reader\Strategy;

use App\Import\UnreadeableFileException;
use DOMDocument;
use Generator;
use XMLReader;

final class XMLImportReader implements ReaderInterface
{
    public function supports(string $type): bool
    {
        return 'xml' === $type;
    }

    public function read(string $file): Generator
    {
        $reader = new XMLReader;
        $reader->open($file);
        # $doc allocates memory for a node and its children, for example person in this case
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            if (false === $reader->read() && LIBXML_ERR_FATAL === libxml_get_last_error()->level){
                throw new UnreadeableFileException("XML file '{$file}' is empty or malformed.");
            }

            /**
             * The while loop positions the readers internal cursor at the right spot, before the main loop starts.
             * The cursor is going over the nodes until it finds the node person, then it stops there.
             */
            while ($reader->read()) {
                if ('person' === $reader->name) {
                    break;
                }
            }

            while($reader->name === 'person') {
                # expand($doc) creates a subtree of nodes (person and its children) and assigns the $doc as the nodes' owner 
                $el = $reader->expand($doc);
                $row = [];

                foreach ($el->childNodes as $child) {
                    if (XML_ELEMENT_NODE === $child->nodeType) {
                        $row[$child->localName] = trim($child->textContent);
                    }
                }

                yield $row;

                $reader->next('person');
            }
        } finally {
            $reader->close();
        }
    }
}