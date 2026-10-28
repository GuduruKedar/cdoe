<?php

use Illuminate\Support\Facades\Route;
use Modules\Visa\Http\Controllers\VisaController;

Route::middleware(['web'])->group(function () {
    // Exact menu reference from master.menus: visa/ticketriase
    Route::get('visa/ticketriase', [VisaController::class, 'ticketRaise'])
        ->name('visa.ticketraise');
    Route::post('visa/ticketriase', [VisaController::class, 'storeTicket'])
        ->name('visa.ticketraise.store');

    // Correct-spelling & kebab-case aliases
    Route::get('visa/ticketraise', [VisaController::class, 'ticketRaise']);
    Route::post('visa/ticketraise', [VisaController::class, 'storeTicket']);
    Route::get('visa/ticket-raise', [VisaController::class, 'ticketRaise']);
    Route::post('visa/ticket-raise', [VisaController::class, 'storeTicket']);

    // Dynamic dropdown sub-types
    Route::get('visa/ticket-subtypes', [VisaController::class, 'getSubTypes'])
        ->name('visa.subtypes');

    // Tickets List (menu: visa/ticketslist)
    Route::get('visa/ticketslist', [VisaController::class, 'ticketsList'])
        ->name('visa.ticketslist');

    // Tickets KPI / Performance (menu: visa/ticketskpi)
    Route::get('visa/ticketskpi', [VisaController::class, 'ticketsKpi'])
        ->name('visa.ticketskpi');
    Route::get('visa/ticket-kpi', [VisaController::class, 'ticketsKpi']);
    Route::get('visa/tickets-kpi', [VisaController::class, 'ticketsKpi']);

    // Root VISA hub (menu: visa/index)
    Route::get('visa/index', [VisaController::class, 'index'])
        ->name('visa.index');

    // Ticket Details & View (DSM viewing auto-updates status from 0 to 1)
    Route::get('visa/ticket-details/{ticketId}', [VisaController::class, 'getTicketDetails'])
        ->name('visa.ticket.details');

    // Update Ticket Status (DSM status updates: 1, 2, or 3 with comment & optional file)
    Route::post('visa/ticket-status/{ticketId}', [VisaController::class, 'updateTicketStatus'])
        ->name('visa.ticket.update_status');

    // Secure file download
    Route::get('visa/ticket-file/{ticketId}', [VisaController::class, 'downloadTicketFile'])
        ->name('visa.ticket.download');

    // Delete Ticket
    Route::match(['post', 'delete'], 'visa/ticket-delete/{ticketId}', [VisaController::class, 'deleteTicket'])
        ->name('visa.ticket.delete');

    Route::resource('visa', VisaController::class)->names('visa');
});
