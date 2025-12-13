<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    // Lista de comandos Artisan registrados manualmente en la aplicación.
    // Agrega aquí los nuevos comandos para que Laravel los descubra automáticamente.
    protected $commands = [
        \App\Console\Commands\SnapshotUnpaidFacturas::class,
        \App\Console\Commands\BackupDatabase::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Programa la ejecución diaria del comando de snapshot.
        // Por defecto `daily()` ejecuta la tarea a medianoche en la zona horaria
        // configurada para la aplicación. Asegúrate de tener `php artisan schedule:run`
        // programado en Cron (o una tarea equivalente en Windows).
        $schedule->command('snapshots:unpaid-facturas')->daily();
        // Reservado: realiza un respaldo diario de la base de datos a las 02:00.
        $schedule->command('backup:database')->dailyAt('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
