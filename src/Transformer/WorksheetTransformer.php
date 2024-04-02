<?php

namespace EntityImporterBundle\Transformer;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorksheetTransformer
{
    function worksheetToArray(Worksheet $worksheet): array {
        $data = [];

        // Assume the first row contains the column headers
        $columnHeaders = [];
        foreach ($worksheet->getRowIterator() as $row) {
            if ($row->getRowIndex() === 1) {
                // Get column headers from the first row
                foreach ($row->getCellIterator() as $cell) {
                    $columnHeaders[] = $cell->getValue();
                }
            } else {
                // Create an associative array with column headers as keys
                $rowData = [];
                foreach ($row->getCellIterator() as $index => $cell) {
                    $rowData[$columnHeaders[$index]] = $cell->getValue();
                }
                // Add the row data to the main data array
                $data[] = $rowData;
            }
        }

        return $data;
    }
}