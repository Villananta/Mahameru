<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(Request $request): View
    {
        $query = Client::query()
            ->active()
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        $clients = $query->paginate(15)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    /**
     * Store a newly created client.
     */
    public function store(StoreClientRequest $request): RedirectResponse|JsonResponse
    {
        $client = Client::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Klien berhasil didaftarkan.',
                'client'  => $client->only(['id', 'name', 'email', 'phone', 'address', 'npwp']),
            ], 201);
        }

        return redirect()
            ->route('clients.index')
            ->with('success', 'Klien berhasil didaftarkan.');
    }

    /**
     * Update the specified client.
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse|JsonResponse
    {
        $client->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Klien berhasil diperbarui.',
                'client'  => $client->fresh()->only(['id', 'name', 'email', 'phone', 'address', 'npwp']),
            ]);
        }

        return redirect()
            ->route('clients.index')
            ->with('success', 'Klien berhasil diperbarui.');
    }

    /**
     * Remove the specified client (soft delete).
     */
    public function destroy(Client $client): RedirectResponse|JsonResponse
    {
        // Check if client has active invoices
        $activeInvoices = $client->invoices()
            ->whereIn('status', ['draft', 'sent'])
            ->exists();

        if ($activeInvoices) {
            $message = 'Klien memiliki faktur aktif (draft/terkirim) dan tidak bisa dihapus. Nonaktifkan saja.';
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->withErrors(['error' => $message]);
        }

        $client->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Klien berhasil dinonaktifkan.',
            ]);
        }

        return redirect()
            ->route('clients.index')
            ->with('success', 'Klien berhasil dinonaktifkan (soft delete). Riwaya faktur tetap utuh.');
    }
}