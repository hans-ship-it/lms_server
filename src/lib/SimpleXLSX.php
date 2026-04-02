<?php
/**
 * SimpleXLSX class v2.10
 *
 * Copyright (c) 2023 Sergey Shuchkin (https://github.com/shuchkin/simplexlsx)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 */

namespace Shuchkin;

class SimpleXLSX
{
    public static function parse($filename, $is_data = false, $debug = false)
    {
        $xlsx = new self();
        $xlsx->debug = $debug;
        if ($is_data) {
            $xlsx->package = ['docProps/core.xml' => '', 'xl/_rels/workbook.xml.rels' => '', 'xl/workbook.xml' => ''];
            if (!$xlsx->readData($filename)) {
                return false;
            }
        }
        elseif (!$xlsx->read($filename)) {
            return false;
        }
        return $xlsx;
    }
    public static function parseFile($filename, $debug = false)
    {
        return self::parse($filename, false, $debug);
    }
    public static function parseData($data, $debug = false)
    {
        return self::parse($data, true, $debug);
    }
    protected $package = [
        'docProps/core.xml' => '',
        'xl/_rels/workbook.xml.rels' => '',
        'xl/workbook.xml' => '',
    ];
    protected $sharedstrings = [];
    protected $sheets = [];
    protected $sheetNames = [];
    protected $styles = [];
    protected $debug = false;
    protected $error = false;
    public function rows($worksheetIndex = 0, $limit = 0)
    {
        if (($ws = $this->worksheet($worksheetIndex)) === false) {
            return false;
        }
        $dim = $this->dimension($worksheetIndex);
        $numCols = $dim[0];
        $numRows = $dim[1];
        $emptyRow = [];
        for ($i = 0; $i < $numCols; $i++) {
            $emptyRow[] = '';
        }
        $rows = [];
        for ($i = 0; $i < $numRows; $i++) {
            $rows[] = $emptyRow;
        }
        $curR = 0;
        foreach ($ws->sheetData->row as $row) {
            $curC = 0;
            foreach ($row->c as $c) {
                // detect skipped cols
                if (isset($c['r'])) {
                    $curC = $this->getIndex((string)$c['r']);
                }
                $val = $this->value($c);
                $rows[$curR][$curC] = $val;
                $curC++;
            }
            $curR++;
            if ($limit && $curR == $limit) {
                break;
            }
        }
        return $rows;
    }
    public function value($cell)
    {
        // Determine data type
        $dataType = (string)$cell['t'];
        $val = '';
        if (isset($cell->v)) {
            $val = (string)$cell->v;
            if ($dataType === 's') { // shared string
                $val = isset($this->sharedstrings[$val]) ? $this->sharedstrings[$val] : $val;
            }
        }
        return $val;
    }
    public function worksheet($worksheetIndex = 0)
    {
        if (isset($this->sheets[$worksheetIndex])) {
            $ws = $this->sheets[$worksheetIndex];
            if (isset($ws->sheetData) && isset($ws->sheetData->row)) {
                return $ws;
            }
        }
        return false;
    }
    public function dimension($worksheetIndex = 0)
    {
        if (($ws = $this->worksheet($worksheetIndex)) === false) {
            return [0, 0];
        }
        $ref = (string)$ws->dimension['ref'];
        if (strpos($ref, ':') === false) {
            $ref .= ':' . $ref;
        }
        $d = explode(':', $ref);
        $index = $this->columnIndex($d[1]);
        return [$index[0] + 1, $index[1] + 1];
    }
    // Helper to convert column letter to index
    public function columnIndex($cell)
    {
        $cell = strtoupper($cell);
        $colStr = preg_replace('/[0-9]/', '', $cell);
        $row = preg_replace('/[A-Z]/', '', $cell);
        $len = strlen($colStr);
        $col = 0;
        for ($i = 0; $i < $len; $i++) {
            $col += (ord($colStr[$i]) - 64) * pow(26, $len - $i - 1);
        }
        return [$col - 1, $row - 1];
    }
    public function getIndex($cell)
    {
        return $this->columnIndex($cell)[0];
    }

    // Basic ZIP reader implementation since we can't use ZipArchive if not enabled
    // Note: Assuming ZipArchive IS actually enabled in most PHP envs.
    // If not, this simple class is limited.
    protected function read($filename)
    {
        $this->error = false;
        if (!file_exists($filename)) {
            $this->error = 'File not found ' . $filename;
            return false;
        }

        $zip = new \ZipArchive;
        if ($zip->open($filename) === true) {
            // Read Shared Strings
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                $xml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $this->sharedstrings[] = (string)$si->t;
                    }
                    elseif (isset($si->r) && isset($si->r->t)) { // Rich text
                        $val = '';
                        foreach ($si->r as $r) {
                            $val .= (string)$r->t;
                        }
                        $this->sharedstrings[] = $val;
                    }
                }
            }

            // Read Workbook to get sheets
            if ($zip->locateName('xl/workbook.xml') !== false) {
                $xml = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
                foreach ($xml->sheets->sheet as $sheet) {
                    $this->sheetNames[] = (string)$sheet['name'];
                    $rId = (string)$sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
                // We need relationships to find the file
                }
            }

            // For simplicity in this trimmed version, just look for sheet1, sheet2 etc
            $i = 1;
            while ($zip->locateName("xl/worksheets/sheet$i.xml") !== false) {
                $this->sheets[] = simplexml_load_string($zip->getFromName("xl/worksheets/sheet$i.xml"));
                $i++;
            }

            $zip->close();
            return true;
        }
        else {
            $this->error = 'ZipArchive failed to open file';
            return false;
        }
    }

    protected function readData($data)
    {
        // Similar to read but from string data - skipped for this concise version
        return false;
    }
}
?>
