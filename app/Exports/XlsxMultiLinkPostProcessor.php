<?php

namespace App\Exports;

use DOMDocument;
use ZipArchive;

class XlsxMultiLinkPostProcessor
{
    const NS_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    const NS_HYPERLINK = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink';

    /**
     * Rebuild the generated xlsx so that every document URL stacked inside a
     * single cell (separated by newline) becomes its own clickable hyperlink.
     *
     * @param string $filePath path to the xlsx file (modified in place)
     * @param array  $docCells map of sheet row -> array of absolute URLs (column F)
     */
    public static function embed($filePath, array $docCells)
    {
        if (empty($docCells)) {
            return $filePath;
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return $filePath;
        }

        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $entries[$name] = $zip->getFromIndex($i);
        }
        $zip->close();

        $sheetPath = 'xl/worksheets/sheet1.xml';
        $relsPath = 'xl/worksheets/_rels/sheet1.xml.rels';
        $sharedPath = 'xl/sharedStrings.xml';

        if (!isset($entries[$sheetPath], $entries[$relsPath], $entries[$sharedPath])) {
            return $filePath;
        }

        $sheetDom = self::loadXml($entries[$sheetPath]);
        $relsDom = self::loadXml($entries[$relsPath]);
        $sharedDom = self::loadXml($entries[$sharedPath]);

        if (!$sheetDom || !$relsDom || !$sharedDom) {
            return $filePath;
        }

        $relsRoot = $relsDom->documentElement;

        // find the highest existing relationship id
        $maxRel = 1000;
        foreach ($relsRoot->getElementsByTagName('Relationship') as $rel) {
            $id = $rel->getAttribute('Id');
            if (preg_match('/^rId(\d+)$/', $id, $m)) {
                $maxRel = max($maxRel, (int) $m[1]);
            }
        }
        $rIdCounter = $maxRel + 1;

        // map cell reference (e.g. F2) -> shared string index
        $refToSi = [];
        foreach ($sheetDom->getElementsByTagName('c') as $cell) {
            if ($cell->getAttribute('t') !== 's') {
                continue;
            }
            $ref = strtoupper($cell->getAttribute('r'));
            $v = $cell->getElementsByTagName('v')->item(0);
            if ($v) {
                $refToSi[$ref] = (int) $v->nodeValue;
            }
        }

        $siList = $sharedDom->getElementsByTagName('si');

        foreach ($docCells as $row => $urls) {
            $ref = 'F' . $row;
            if (!isset($refToSi[$ref])) {
                continue;
            }

            $si = $siList->item($refToSi[$ref]);
            if (!$si) {
                continue;
            }

            // clear old text runs
            while ($si->firstChild) {
                $si->removeChild($si->firstChild);
            }

            $lastKey = max(array_keys($urls));

            foreach ($urls as $urlKey => $url) {
                $run = $sharedDom->createElement('r');
                $run->setAttributeNS(self::NS_REL, 'r:id', 'rId' . $rIdCounter);

                $rPr = $sharedDom->createElement('rPr');
                $rFont = $sharedDom->createElement('rFont');
                $rFont->setAttribute('val', 'Calibri');
                $color = $sharedDom->createElement('color');
                $color->setAttribute('rgb', 'FF0563C1');
                $u = $sharedDom->createElement('u');
                $rPr->appendChild($rFont);
                $rPr->appendChild($color);
                $rPr->appendChild($u);
                $run->appendChild($rPr);

                $t = $sharedDom->createElement('t');
                $t->setAttribute('xml:space', 'preserve');
                $text = ($urlKey === $lastKey) ? $url : $url . "\n";
                $t->appendChild($sharedDom->createTextNode($text));
                $run->appendChild($t);

                $si->appendChild($run);

                $relationship = $relsDom->createElement('Relationship');
                $relationship->setAttribute('Id', 'rId' . $rIdCounter);
                $relationship->setAttribute('Type', self::NS_HYPERLINK);
                $relationship->setAttribute('Target', $url);
                $relationship->setAttribute('TargetMode', 'External');
                $relsRoot->appendChild($relationship);

                $rIdCounter++;
            }
        }

        $relsRoot->setAttribute('Count', $relsRoot->getElementsByTagName('Relationship')->length);

        $entries[$sheetPath] = $sheetDom->saveXML();
        $entries[$relsPath] = $relsDom->saveXML();
        $entries[$sharedPath] = $sharedDom->saveXML();

        // rebuild the archive
        $tmpPath = $filePath . '.tmp';
        $newZip = new ZipArchive();
        if ($newZip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($entries as $name => $data) {
                $newZip->addFromString($name, $data);
            }
            $newZip->close();

            if (file_exists($filePath)) {
                unlink($filePath);
            }
            rename($tmpPath, $filePath);
        }

        return $filePath;
    }

    private static function loadXml($content)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($content);
        libxml_use_internal_errors($previous);
        if (!$loaded) {
            return null;
        }
        return $dom;
    }
}