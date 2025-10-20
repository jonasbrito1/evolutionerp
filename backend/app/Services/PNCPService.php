<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PNCPService
{
    private $apiUrl = 'https://pncp.gov.br/api';
    private $cacheTime = 3600; // 1 hora de cache

    /**
     * Busca contratos ativos no PNCP
     */
    public function searchContracts(array $params = []): ?array
    {
        $cacheKey = 'pncp_contracts_' . md5(json_encode($params));

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($params) {
            try {
                $queryParams = [
                    'pagina' => $params['page'] ?? 1,
                    'tamanhoPagina' => $params['per_page'] ?? 20,
                ];

                if (!empty($params['cnpj'])) {
                    $queryParams['cnpjOrgao'] = $params['cnpj'];
                }

                if (!empty($params['year'])) {
                    $queryParams['ano'] = $params['year'];
                }

                if (!empty($params['search'])) {
                    $queryParams['palavraChave'] = $params['search'];
                }

                $response = Http::timeout(15)
                    ->get($this->apiUrl . '/pncp/v1/contratos', $queryParams);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('PNCP API error on searchContracts', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return null;

            } catch (\Exception $e) {
                Log::error('PNCP API exception on searchContracts: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Busca avisos/editais de contratação no PNCP
     */
    public function searchNotices(array $params = []): ?array
    {
        $cacheKey = 'pncp_notices_' . md5(json_encode($params));

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($params) {
            try {
                $queryParams = [
                    'pagina' => $params['page'] ?? 1,
                    'tamanhoPagina' => $params['per_page'] ?? 20,
                ];

                if (!empty($params['modalidade'])) {
                    $queryParams['modalidadeId'] = $this->getModalidadeId($params['modalidade']);
                }

                if (!empty($params['uf'])) {
                    $queryParams['ufSigla'] = strtoupper($params['uf']);
                }

                if (!empty($params['dataInicial'])) {
                    $queryParams['dataInicial'] = $params['dataInicial'];
                }

                if (!empty($params['dataFinal'])) {
                    $queryParams['dataFinal'] = $params['dataFinal'];
                }

                if (!empty($params['search'])) {
                    $queryParams['palavraChave'] = $params['search'];
                }

                $response = Http::timeout(15)
                    ->get($this->apiUrl . '/pncp/v1/avisos/publicados', $queryParams);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('PNCP API error on searchNotices', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return null;

            } catch (\Exception $e) {
                Log::error('PNCP API exception on searchNotices: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Busca um contrato específico por ID
     */
    public function getContract(string $cnpj, int $ano, int $sequencial): ?array
    {
        $cacheKey = "pncp_contract_{$cnpj}_{$ano}_{$sequencial}";

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($cnpj, $ano, $sequencial) {
            try {
                $response = Http::timeout(15)
                    ->get($this->apiUrl . "/pncp/v1/contratos/{$cnpj}/{$ano}/{$sequencial}");

                if ($response->successful()) {
                    return $response->json();
                }

                return null;

            } catch (\Exception $e) {
                Log::error('PNCP API exception on getContract: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Busca um aviso/edital específico
     */
    public function getNotice(string $cnpj, int $ano, int $sequencial): ?array
    {
        $cacheKey = "pncp_notice_{$cnpj}_{$ano}_{$sequencial}";

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($cnpj, $ano, $sequencial) {
            try {
                $response = Http::timeout(15)
                    ->get($this->apiUrl . "/pncp/v1/avisos/{$cnpj}/{$ano}/{$sequencial}");

                if ($response->successful()) {
                    return $response->json();
                }

                return null;

            } catch (\Exception $e) {
                Log::error('PNCP API exception on getNotice: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Busca órgãos cadastrados
     */
    public function searchOrgans(string $search = ''): ?array
    {
        $cacheKey = 'pncp_organs_' . md5($search);

        return Cache::remember($cacheKey, 86400, function () use ($search) { // Cache de 24h
            try {
                $queryParams = [];

                if (!empty($search)) {
                    $queryParams['nome'] = $search;
                }

                $response = Http::timeout(15)
                    ->get($this->apiUrl . '/pncp/v1/orgaos', $queryParams);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;

            } catch (\Exception $e) {
                Log::error('PNCP API exception on searchOrgans: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Busca itens de um contrato
     */
    public function getContractItems(string $cnpj, int $ano, int $sequencial): ?array
    {
        $cacheKey = "pncp_contract_items_{$cnpj}_{$ano}_{$sequencial}";

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($cnpj, $ano, $sequencial) {
            try {
                $response = Http::timeout(15)
                    ->get($this->apiUrl . "/pncp/v1/contratos/{$cnpj}/{$ano}/{$sequencial}/itens");

                if ($response->successful()) {
                    return $response->json();
                }

                return null;

            } catch (\Exception $e) {
                Log::error('PNCP API exception on getContractItems: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Busca estatísticas gerais do PNCP
     */
    public function getStatistics(int $year = null): ?array
    {
        $year = $year ?? date('Y');
        $cacheKey = "pncp_statistics_{$year}";

        return Cache::remember($cacheKey, 3600, function () use ($year) {
            try {
                // Busca dados agregados
                $contracts = $this->searchContracts(['year' => $year, 'per_page' => 1]);
                $notices = $this->searchNotices([
                    'dataInicial' => "{$year}-01-01",
                    'dataFinal' => "{$year}-12-31",
                    'per_page' => 1
                ]);

                return [
                    'year' => $year,
                    'total_contracts' => $contracts['totalRegistros'] ?? 0,
                    'total_notices' => $notices['totalRegistros'] ?? 0,
                    'last_update' => now()->format('Y-m-d H:i:s')
                ];

            } catch (\Exception $e) {
                Log::error('PNCP API exception on getStatistics: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Converte nome da modalidade para ID do PNCP
     */
    private function getModalidadeId(string $modalidade): ?int
    {
        $modalidades = [
            'pregao' => 1,
            'pregão' => 1,
            'pregao_eletronico' => 1,
            'concorrencia' => 2,
            'concorrência' => 2,
            'concurso' => 3,
            'leilao' => 4,
            'leilão' => 4,
            'dialogo_competitivo' => 5,
            'diálogo_competitivo' => 5,
            'dispensa' => 6,
            'inexigibilidade' => 7,
        ];

        $modalidadeLower = strtolower($modalidade);
        return $modalidades[$modalidadeLower] ?? null;
    }

    /**
     * Formata dados de contrato para exibição
     */
    public function formatContractData(array $contract): array
    {
        return [
            'numero' => $contract['numeroControlePNCP'] ?? 'N/A',
            'objeto' => $contract['objetoContrato'] ?? 'Não informado',
            'valor' => $contract['valorInicial'] ?? 0,
            'valor_formatado' => 'R$ ' . number_format($contract['valorInicial'] ?? 0, 2, ',', '.'),
            'orgao' => $contract['nomeOrgao'] ?? 'Não informado',
            'cnpj_orgao' => $contract['cnpj'] ?? '',
            'data_assinatura' => $contract['dataAssinatura'] ?? null,
            'data_vigencia_inicio' => $contract['dataVigenciaInicio'] ?? null,
            'data_vigencia_fim' => $contract['dataVigenciaFim'] ?? null,
            'fornecedor' => $contract['nomeRazaoSocialFornecedor'] ?? 'Não informado',
            'cpf_cnpj_fornecedor' => $contract['niFornecedor'] ?? '',
        ];
    }

    /**
     * Formata dados de aviso/edital para exibição
     */
    public function formatNoticeData(array $notice): array
    {
        return [
            'numero' => $notice['numeroControlePNCP'] ?? 'N/A',
            'titulo' => $notice['objetoCompra'] ?? 'Não informado',
            'modalidade' => $notice['modalidadeNome'] ?? 'Não informado',
            'orgao' => $notice['orgaoEntidade']['razaoSocial'] ?? 'Não informado',
            'cnpj_orgao' => $notice['orgaoEntidade']['cnpj'] ?? '',
            'valor_estimado' => $notice['valorEstimadoTotal'] ?? 0,
            'valor_formatado' => 'R$ ' . number_format($notice['valorEstimadoTotal'] ?? 0, 2, ',', '.'),
            'data_publicacao' => $notice['dataPublicacaoPncp'] ?? null,
            'data_abertura' => $notice['dataAberturaProposta'] ?? null,
            'situacao' => $notice['situacaoAviso'] ?? 'Não informado',
            'link_sistema' => $notice['linkSistemaOrigem'] ?? null,
        ];
    }

    /**
     * Limpa cache do PNCP
     */
    public function clearCache(): void
    {
        Cache::flush(); // Em produção, seria melhor usar tags de cache
        Log::info('PNCP cache cleared');
    }
}
