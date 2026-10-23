<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\TeamManagementController;
use App\Http\Controllers\UnitManagementController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WorkProgramController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/kampus/{campus}', [OrganizationController::class, 'campus'])
        ->can('view', 'campus')
        ->name('campus.show');
    Route::get('/unit/{unit}', [OrganizationController::class, 'unit'])
        ->can('view', 'unit')
        ->name('unit.show');
    Route::get('/unit/{unit}/pengelolaan', [OrganizationController::class, 'manageUnit'])
        ->middleware('role:campus_head,unit_head')
        ->can('manage', 'unit')
        ->name('unit.manage');

    Route::middleware('role:campus_head')->prefix('kelola-unit')->name('units.')->group(function () {
        Route::get('/', [UnitManagementController::class, 'index'])->name('index');
        Route::post('/', [UnitManagementController::class, 'store'])->name('store');
        Route::get('/{unit}', [UnitManagementController::class, 'show'])->name('show');
        Route::put('/{unit}', [UnitManagementController::class, 'update'])->name('update');
        Route::patch('/{unit}/status', [UnitManagementController::class, 'toggleStatus'])->name('status');
        Route::post('/{unit}/tim', [TeamManagementController::class, 'store'])->name('teams.store');
        Route::put('/{unit}/tim/{team}', [TeamManagementController::class, 'update'])->name('teams.update');
        Route::patch('/{unit}/tim/{team}/status', [TeamManagementController::class, 'toggleStatus'])->name('teams.status');
    });

    Route::middleware('role:campus_head,unit_head,unit_member')
        ->prefix('program-kerja')
        ->name('work-programs.')
        ->controller(WorkProgramController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/buat', 'create')->middleware('role:unit_head,unit_member')->name('create');
            Route::post('/', 'store')->middleware('role:unit_head,unit_member')->name('store');
            Route::get('/{program}/ubah', 'edit')->middleware('role:unit_head,unit_member')->whereNumber('program')->name('edit');
            Route::put('/{program}', 'update')->middleware('role:unit_head,unit_member')->whereNumber('program')->name('update');
            Route::patch('/{program}/status', 'changeStatus')->middleware('role:campus_head,unit_head,unit_member')->whereNumber('program')->name('status');
            Route::put('/{program}/target-bulanan', 'updateMonthlyTargets')->middleware('role:unit_head,unit_member')->whereNumber('program')->name('monthly-targets');
            Route::post('/{program}/capaian', 'reportProgress')->whereNumber('program')->name('progress');
            Route::get('/{program}', 'show')->whereNumber('program')->name('show');
        });

    Route::middleware('role:campus_head,unit_head,unit_member')
        ->prefix('komunikasi')
        ->name('communication.')
        ->controller(\App\Http\Controllers\CommunicationTopicController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/buat', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{topic}', 'show')->whereNumber('topic')->name('show');
            Route::post('/{topic}/pesan', 'storeMessage')->whereNumber('topic')->name('message');
            Route::patch('/{topic}/status', 'updateStatus')->whereNumber('topic')->name('status');
        });

    Route::middleware('role:campus_head,unit_head,unit_member')
        ->get('/laporan', [\App\Http\Controllers\ReportController::class, 'index'])
        ->name('reports.index');

    Route::middleware('role:system_admin,campus_head,unit_head')
        ->prefix('manajemen-pengguna')
        ->name('users.')
        ->group(function () {
            Route::get('/', [UserManagementController::class, 'index'])->name('index');
            Route::get('/buat', [UserManagementController::class, 'create'])->name('create');
            Route::post('/', [UserManagementController::class, 'store'])->name('store');
            Route::get('/{user}/ubah', [UserManagementController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
            Route::patch('/{user}/status', [UserManagementController::class, 'toggleStatus'])
                ->name('status');
        });
});
