<?php

declare(strict_types=1);

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Mahameru Supplies Invoice MVP
|--------------------------------------------------------------------------
*/

// Redirect root to invoices
Route::get('/', fn () => redirect()->route('invoices.index'));

// Invoices
Route::resource('invoices', InvoiceController::class)
    ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
    ->parameters(['invoices' => 'invoice']);

// Custom routes
Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
    ->name('invoices.pdf');

Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])
    ->name('invoices.status.update');

// Clients
Route::resource('clients', ClientController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->parameters(['clients' => 'client']);