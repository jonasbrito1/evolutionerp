<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use Exception;

class DocumentExtractionService
{
    protected $pdfParser;

    public function __construct()
    {
        $this->pdfParser = new Parser();
    }

    /**
     * Extrai texto de qualquer tipo de documento suportado
     */
    public function extractText(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        try {
            switch ($extension) {
                case 'pdf':
                    return $this->extractFromPdf($filePath);

                case 'doc':
                case 'docx':
                    return $this->extractFromWord($filePath);

                case 'xls':
                case 'xlsx':
                case 'csv':
                    return $this->extractFromExcel($filePath);

                case 'txt':
                    return $this->extractFromText($filePath);

                default:
                    throw new Exception("Formato de arquivo não suportado: {$extension}");
            }
        } catch (Exception $e) {
            // Log detalhado do erro
            \Log::error('Erro detalhado na extração de texto:', [
                'file' => $filePath,
                'extension' => $extension,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception("Erro ao extrair texto do documento: " . $e->getMessage());
        }
    }

    /**
     * Extrai texto de PDF
     */
    protected function extractFromPdf(string $pdfPath): string
    {
        try {
            $pdf = $this->pdfParser->parseFile($pdfPath);
            $text = $pdf->getText();

            // Corrigir problemas de encoding UTF-8
            $text = $this->fixEncoding($text);

            return $this->cleanText($text);
        } catch (Exception $e) {
            throw new Exception("Erro ao extrair texto do PDF: " . $e->getMessage());
        }
    }

    /**
     * Corrige problemas de encoding UTF-8
     */
    protected function fixEncoding(string $text): string
    {
        // Se já é UTF-8 válido, retorna
        if (mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }

        // Tentar detectar e converter encoding
        $encoding = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);

        if ($encoding && $encoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        } else {
            // Se falhou detecção, forçar conversão removendo caracteres inválidos
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        // Remove caracteres de controle problemáticos mas preserva quebras de linha
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        return $text;
    }

    /**
     * Extrai texto de Word (DOC/DOCX)
     */
    protected function extractFromWord(string $wordPath): string
    {
        try {
            $phpWord = WordIOFactory::load($wordPath);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                $text .= $this->extractTextFromSection($section) . "\n\n";
            }

            return $this->cleanText($text);
        } catch (Exception $e) {
            throw new Exception("Erro ao extrair texto do Word: " . $e->getMessage());
        }
    }

    /**
     * Extrai texto de uma seção do Word
     */
    protected function extractTextFromSection($section): string
    {
        $text = '';

        try {
            foreach ($section->getElements() as $element) {
                $text .= $this->extractTextFromElement($element) . "\n";
            }
        } catch (Exception $e) {
            \Log::warning('Erro ao extrair texto de seção Word: ' . $e->getMessage());
        }

        return $text;
    }

    /**
     * Extrai texto de um elemento do Word
     */
    protected function extractTextFromElement($element): string
    {
        $text = '';

        try {
            $className = get_class($element);

            // TextRun (texto com formatação)
            if (strpos($className, 'TextRun') !== false) {
                if (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $textElement) {
                        if (method_exists($textElement, 'getText')) {
                            $elementText = $textElement->getText();
                            $text .= $this->convertToString($elementText) . ' ';
                        }
                    }
                } elseif (method_exists($element, 'getText')) {
                    $text .= $this->convertToString($element->getText()) . ' ';
                }
            }
            // Text (texto simples)
            elseif (strpos($className, 'Text') !== false) {
                if (method_exists($element, 'getText')) {
                    $text .= $this->convertToString($element->getText()) . ' ';
                }
            }
            // Table (tabela)
            elseif (strpos($className, 'Table') !== false) {
                $text .= $this->extractFromTable($element) . "\n";
            }
            // ListItem (item de lista)
            elseif (strpos($className, 'ListItem') !== false) {
                if (method_exists($element, 'getText')) {
                    $text .= '• ' . $this->convertToString($element->getText()) . "\n";
                } elseif (method_exists($element, 'getElements')) {
                    $text .= '• ' . $this->extractFromContainer($element) . "\n";
                }
            }
            // Containers genéricos
            elseif (method_exists($element, 'getElements')) {
                $text .= $this->extractFromContainer($element) . ' ';
            }
            // Elementos com getText direto
            elseif (method_exists($element, 'getText')) {
                $text .= $this->convertToString($element->getText()) . ' ';
            }
        } catch (Exception $e) {
            \Log::warning('Erro ao extrair texto de elemento Word: ' . $e->getMessage());
        }

        return $text;
    }

    /**
     * Converte valor para string (pode ser array ou string)
     */
    protected function convertToString($value): string
    {
        if (is_array($value)) {
            return implode(' ', array_filter($value, function($item) {
                return is_string($item) || is_numeric($item);
            }));
        }

        return is_string($value) || is_numeric($value) ? (string)$value : '';
    }

    /**
     * Extrai texto de uma tabela Word
     */
    protected function extractFromTable($table): string
    {
        $text = '';

        try {
            if (method_exists($table, 'getRows')) {
                foreach ($table->getRows() as $row) {
                    if (method_exists($row, 'getCells')) {
                        $cellTexts = [];
                        foreach ($row->getCells() as $cell) {
                            if (method_exists($cell, 'getElements')) {
                                $cellText = '';
                                foreach ($cell->getElements() as $element) {
                                    $cellText .= $this->extractTextFromElement($element) . ' ';
                                }
                                $cellTexts[] = trim($cellText);
                            }
                        }
                        if (!empty($cellTexts)) {
                            $text .= implode(' | ', $cellTexts) . "\n";
                        }
                    }
                }
            }
        } catch (Exception $e) {
            \Log::warning('Erro ao extrair texto de tabela Word: ' . $e->getMessage());
        }

        return $text;
    }

    /**
     * Extrai texto de elementos container do Word
     */
    protected function extractFromContainer($container): string
    {
        $text = '';

        if (!method_exists($container, 'getElements')) {
            return $text;
        }

        foreach ($container->getElements() as $element) {
            if (method_exists($element, 'getText')) {
                $elementText = $element->getText();
                // getText() pode retornar array ou string
                if (is_array($elementText)) {
                    $text .= implode(' ', $elementText) . ' ';
                } else {
                    $text .= $elementText . ' ';
                }
            } elseif (method_exists($element, 'getElements')) {
                $text .= $this->extractFromContainer($element);
            }
        }

        return $text;
    }

    /**
     * Extrai texto de Excel (XLS/XLSX/CSV)
     */
    protected function extractFromExcel(string $excelPath): string
    {
        try {
            $spreadsheet = SpreadsheetIOFactory::load($excelPath);
            $text = '';

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $text .= "Planilha: " . $sheet->getTitle() . "\n\n";

                foreach ($sheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $value = $cell->getValue();
                        if ($value !== null && $value !== '') {
                            $rowData[] = $value;
                        }
                    }

                    if (!empty($rowData)) {
                        $text .= implode(' | ', $rowData) . "\n";
                    }
                }

                $text .= "\n";
            }

            return $this->cleanText($text);
        } catch (Exception $e) {
            throw new Exception("Erro ao extrair texto do Excel: " . $e->getMessage());
        }
    }

    /**
     * Extrai texto de arquivo TXT
     */
    protected function extractFromText(string $txtPath): string
    {
        try {
            $text = file_get_contents($txtPath);
            return $this->cleanText($text);
        } catch (Exception $e) {
            throw new Exception("Erro ao ler arquivo de texto: " . $e->getMessage());
        }
    }

    /**
     * Extrai metadados do documento
     */
    public function extractMetadata(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        try {
            switch ($extension) {
                case 'pdf':
                    return $this->extractPdfMetadata($filePath);

                case 'doc':
                case 'docx':
                    return $this->extractWordMetadata($filePath);

                case 'xls':
                case 'xlsx':
                    return $this->extractExcelMetadata($filePath);

                default:
                    return $this->extractBasicMetadata($filePath);
            }
        } catch (Exception $e) {
            return $this->extractBasicMetadata($filePath);
        }
    }

    /**
     * Extrai metadados de PDF
     */
    protected function extractPdfMetadata(string $pdfPath): array
    {
        try {
            $pdf = $this->pdfParser->parseFile($pdfPath);
            $details = $pdf->getDetails();

            return array_merge($this->extractBasicMetadata($pdfPath), [
                'title' => $details['Title'] ?? null,
                'author' => $details['Author'] ?? null,
                'subject' => $details['Subject'] ?? null,
                'keywords' => $details['Keywords'] ?? null,
                'creator' => $details['Creator'] ?? null,
                'producer' => $details['Producer'] ?? null,
                'creation_date' => $details['CreationDate'] ?? null,
                'modification_date' => $details['ModDate'] ?? null,
                'page_count' => count($pdf->getPages()),
            ]);
        } catch (Exception $e) {
            return $this->extractBasicMetadata($pdfPath);
        }
    }

    /**
     * Extrai metadados de Word
     */
    protected function extractWordMetadata(string $wordPath): array
    {
        try {
            $phpWord = WordIOFactory::load($wordPath);
            $properties = $phpWord->getDocInfo();

            return array_merge($this->extractBasicMetadata($wordPath), [
                'title' => $properties->getTitle(),
                'author' => $properties->getCreator(),
                'subject' => $properties->getSubject(),
                'keywords' => $properties->getKeywords(),
                'description' => $properties->getDescription(),
                'created' => $properties->getCreated(),
                'modified' => $properties->getModified(),
            ]);
        } catch (Exception $e) {
            return $this->extractBasicMetadata($wordPath);
        }
    }

    /**
     * Extrai metadados de Excel
     */
    protected function extractExcelMetadata(string $excelPath): array
    {
        try {
            $spreadsheet = SpreadsheetIOFactory::load($excelPath);
            $properties = $spreadsheet->getProperties();

            return array_merge($this->extractBasicMetadata($excelPath), [
                'title' => $properties->getTitle(),
                'author' => $properties->getCreator(),
                'subject' => $properties->getSubject(),
                'keywords' => $properties->getKeywords(),
                'description' => $properties->getDescription(),
                'created' => $properties->getCreated(),
                'modified' => $properties->getModified(),
                'sheet_count' => $spreadsheet->getSheetCount(),
            ]);
        } catch (Exception $e) {
            return $this->extractBasicMetadata($excelPath);
        }
    }

    /**
     * Extrai metadados básicos do arquivo
     */
    protected function extractBasicMetadata(string $filePath): array
    {
        return [
            'filename' => basename($filePath),
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'size' => filesize($filePath),
            'mime_type' => mime_content_type($filePath),
            'modified_time' => filemtime($filePath),
        ];
    }

    /**
     * Limpa o texto extraído
     */
    protected function cleanText(string $text): string
    {
        // Remove múltiplos espaços
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove caracteres especiais problemáticos
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Remove quebras de linha excessivas
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // Trim
        $text = trim($text);

        return $text;
    }

    /**
     * Verifica se o tipo de arquivo é suportado
     */
    public function isSupported(string $filePath): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $supportedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt'];

        return in_array($extension, $supportedExtensions);
    }

    /**
     * Retorna lista de extensões suportadas
     */
    public function getSupportedExtensions(): array
    {
        return [
            'pdf' => 'Portable Document Format',
            'doc' => 'Microsoft Word 97-2003',
            'docx' => 'Microsoft Word',
            'xls' => 'Microsoft Excel 97-2003',
            'xlsx' => 'Microsoft Excel',
            'csv' => 'Comma-Separated Values',
            'txt' => 'Plain Text',
        ];
    }
}
