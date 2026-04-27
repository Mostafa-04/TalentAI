<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;

class AppController extends Controller
{
    /**
     * Show the application create page.
     */
    public function create()
    {
        /** @var ActivityLogger $logger */
        $logger = app(ActivityLogger::class);

        try {
            $logger->log(
                'app.create',
                'Affichage de la page de création.',
            );

            dd();
        } catch (\Throwable $e) {
            $logger->log(
                'app.create.error',
                'Erreur lors de l\'affichage de la page de création : ' . $e->getMessage(),
                ['exception' => $e->getMessage()]
            );

            throw $e;
        }
    }

    /**
     * Show the application edit page.
     */
    public function edit()
    {
        /** @var ActivityLogger $logger */
        $logger = app(ActivityLogger::class);

        try {
            $logger->log(
                'app.edit',
                'Affichage de la page d\'édition.',
            );

            dd('edit');
        } catch (\Throwable $e) {
            $logger->log(
                'app.edit.error',
                'Erreur lors de l\'affichage de la page d\'édition : ' . $e->getMessage(),
                ['exception' => $e->getMessage()]
            );

            throw $e;
        }
    }
}