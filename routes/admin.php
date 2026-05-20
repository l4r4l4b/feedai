<?php

use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\ConversationList;
use App\Livewire\Admin\PaymentLog;
use App\Livewire\Admin\VendorList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::livewire('/', AdminDashboard::class)->name('dashboard');
        Route::livewire('vendors', VendorList::class)->name('vendors');
        Route::livewire('payments', PaymentLog::class)->name('payments');
        Route::livewire('conversations', ConversationList::class)->name('conversations');
    });
