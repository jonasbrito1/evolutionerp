<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EdictController;
use App\Http\Controllers\CompanyDocumentController;
use App\Http\Controllers\KanbanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rotas públicas
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Rotas protegidas (requer token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Health check
    Route::get('/health', function () {
        return response()->json(['status' => 'ok'], 200);
    });

    // ============================================
    // DASHBOARD
    // ============================================
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index']);

    // ============================================
    // CHATBOT IA
    // ============================================
    Route::post('/chat/message', [App\Http\Controllers\ChatController::class, 'message']);
    Route::post('/chat/upload-document', [App\Http\Controllers\ChatController::class, 'uploadDocument']);

    // ============================================
    // EDITAIS (Public Bidding Edicts)
    // ============================================
    Route::prefix('edicts')->group(function () {
        Route::post('/upload', [EdictController::class, 'upload']);
        Route::get('/stats', [EdictController::class, 'stats']);
        Route::get('/', [EdictController::class, 'index']);
        Route::get('/{id}', [EdictController::class, 'show']);
        Route::put('/{id}', [EdictController::class, 'update']);
        Route::delete('/{id}', [EdictController::class, 'destroy']);
        Route::post('/{id}/reanalyze', [EdictController::class, 'reanalyze']);
        Route::get('/{id}/download', [EdictController::class, 'download']);
    });

    // ============================================
    // DOCUMENTOS DA EMPRESA (Company Documents)
    // ============================================
    Route::prefix('company/documents')->group(function () {
        Route::post('/upload', [CompanyDocumentController::class, 'upload']);
        Route::get('/types', [CompanyDocumentController::class, 'types']);
        Route::get('/expiring', [CompanyDocumentController::class, 'expiring']);
        Route::get('/stats', [CompanyDocumentController::class, 'stats']);
        Route::get('/', [CompanyDocumentController::class, 'index']);
        Route::get('/{id}', [CompanyDocumentController::class, 'show']);
        Route::put('/{id}', [CompanyDocumentController::class, 'update']);
        Route::delete('/{id}', [CompanyDocumentController::class, 'destroy']);
        Route::get('/{id}/download', [CompanyDocumentController::class, 'download']);
    });

    // Alias routes for compatibility (frontend uses /company-documents)
    Route::prefix('company-documents')->group(function () {
        Route::get('/types', [CompanyDocumentController::class, 'types']);
        Route::get('/expiring', [CompanyDocumentController::class, 'expiring']);
        Route::get('/stats', [CompanyDocumentController::class, 'stats']);
        Route::get('/{id}/download', [CompanyDocumentController::class, 'download']);
        Route::get('/{id}', [CompanyDocumentController::class, 'show']);
        Route::post('/', [CompanyDocumentController::class, 'upload']);
        Route::put('/{id}', [CompanyDocumentController::class, 'update']);
        Route::delete('/{id}', [CompanyDocumentController::class, 'destroy']);
        Route::get('/', [CompanyDocumentController::class, 'index']);
    });

    // ============================================
    // FINANCIAL (Gestão Financeira)
    // ============================================
    Route::prefix('financial')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\FinancialController::class, 'dashboard']);
        Route::get('/categories', [App\Http\Controllers\FinancialController::class, 'categories']);
        Route::get('/payment-methods', [App\Http\Controllers\FinancialController::class, 'paymentMethods']);
        Route::get('/transactions', [App\Http\Controllers\FinancialController::class, 'index']);
        Route::post('/transactions', [App\Http\Controllers\FinancialController::class, 'store']);
        Route::put('/transactions/{id}', [App\Http\Controllers\FinancialController::class, 'update']);
        Route::delete('/transactions/{id}', [App\Http\Controllers\FinancialController::class, 'destroy']);
        Route::get('/export/csv', [App\Http\Controllers\FinancialController::class, 'exportCsv']);
        Route::get('/export/pdf', [App\Http\Controllers\FinancialController::class, 'exportPdf']);
    });

    // ============================================
    // KANBAN (Workflow Management)
    // ============================================
    Route::prefix('kanban')->group(function () {
        // Board geral
        Route::get('/', [KanbanController::class, 'index']);
        Route::post('/initialize', [KanbanController::class, 'initialize']);

        // Colunas
        Route::post('/columns', [KanbanController::class, 'createColumn']);
        Route::put('/columns/{id}', [KanbanController::class, 'updateColumn']);
        Route::delete('/columns/{id}', [KanbanController::class, 'deleteColumn']);

        // Cards
        Route::post('/cards', [KanbanController::class, 'createCard']);
        Route::get('/cards/{id}', [KanbanController::class, 'showCard']);
        Route::put('/cards/{id}', [KanbanController::class, 'updateCard']);
        Route::delete('/cards/{id}', [KanbanController::class, 'deleteCard']);
        Route::post('/cards/{id}/move', [KanbanController::class, 'moveCard']);
        Route::post('/cards/{id}/checklist', [KanbanController::class, 'updateChecklist']);
        Route::post('/cards/{id}/comments', [KanbanController::class, 'addComment']);
    });
});

// Para futuras versões - estrutura base para mais controllers
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Edicts, Tenders, Documents, etc virão aqui
});
