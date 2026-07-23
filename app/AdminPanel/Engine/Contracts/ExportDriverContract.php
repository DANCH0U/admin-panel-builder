<?php

namespace App\AdminPanel\Engine\Contracts;

interface ExportDriverContract
{
    public function getExtension(): string;
    public function getMimeType(): string;

    /**
     * Stream or return a response with exported data.
     *
     * @param iterable $records
     * @param array    $columns  ColumnContract[]
     */
    public function export(iterable $records, array $columns): \Symfony\Component\HttpFoundation\StreamedResponse;
}
