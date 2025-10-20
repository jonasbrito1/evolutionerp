<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Exception;

class PdfExtractionService
{
    protected $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extrai texto de um arquivo PDF
     */
    public function extractText(string $pdfPath): string
    {
        try {
            // Aumentar timeout e memória para PDFs grandes
            set_time_limit(300);
            ini_set('memory_limit', '512M');

            // Verificar tamanho do arquivo
            $fileSize = filesize($pdfPath);
            $maxSize = 50 * 1024 * 1024; // 50MB

            if ($fileSize > $maxSize) {
                throw new Exception("Arquivo muito grande. Tamanho máximo: 50MB");
            }

            $pdf = $this->parser->parseFile($pdfPath);

            // Extrair apenas primeiras 50 páginas para otimização
            $pages = $pdf->getPages();
            $maxPages = 50;
            $text = '';

            $pageCount = 0;
            foreach ($pages as $page) {
                if ($pageCount >= $maxPages) {
                    break;
                }
                $text .= $page->getText() . "\n";
                $pageCount++;
            }

            // Limpar texto
            $text = $this->cleanText($text);

            // Limitar tamanho do texto extraído (máx 500KB de texto)
            if (strlen($text) > 500000) {
                $text = substr($text, 0, 500000) . "\n\n[...texto truncado devido ao tamanho...]";
            }

            return $text;
        } catch (Exception $e) {
            throw new Exception("Erro ao extrair texto do PDF: " . $e->getMessage());
        }
    }

    /**
     * Extrai metadados do PDF
     */
    public function extractMetadata(string $pdfPath): array
    {
        try {
            $pdf = $this->parser->parseFile($pdfPath);
            $details = $pdf->getDetails();

            return [
                'title' => $details['Title'] ?? null,
                'author' => $details['Author'] ?? null,
                'subject' => $details['Subject'] ?? null,
                'keywords' => $details['Keywords'] ?? null,
                'creator' => $details['Creator'] ?? null,
                'producer' => $details['Producer'] ?? null,
                'creation_date' => $details['CreationDate'] ?? null,
                'modification_date' => $details['ModDate'] ?? null,
                'page_count' => count($pdf->getPages()),
            ];
        } catch (Exception $e) {
            return [];
        }
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

        // Trim
        $text = trim($text);

        return $text;
    }

    /**
     * Extrai texto de páginas específicas
     */
    public function extractTextFromPages(string $pdfPath, array $pages): string
    {
        try {
            $pdf = $this->parser->parseFile($pdfPath);
            $allPages = $pdf->getPages();
            $text = '';

            foreach ($pages as $pageNum) {
                if (isset($allPages[$pageNum - 1])) {
                    $text .= $allPages[$pageNum - 1]->getText() . "\n";
                }
            }

            return $this->cleanText($text);
        } catch (Exception $e) {
            throw new Exception("Erro ao extrair páginas do PDF: " . $e->getMessage());
        }
    }
}
