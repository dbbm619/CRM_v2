<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

/**
 * Comando Artisan para generar respaldos de la base de datos.
 *
 * - Soporta MySQL (mysqldump + gzip), SQLite (copia y gzip) y Postgres (pg_dump).
 * - Guarda los archivos en `storage/app/backups` y aplica una política de retención por días.
 * - Configurable vía variables de entorno: `DB_BACKUP_ENABLED`, `DB_BACKUP_RETENTION_DAYS`.
 */
class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear un respaldo de la base de datos y almacenar en storage/app/backups';

    /**
     * Execute the console command.
     */
    /**
     * Ejecuta el comando de backup.
     *
     * Pasos principales:
     * 1) Verifica si los backups están habilitados con `DB_BACKUP_ENABLED`.
     * 2) Determina el driver de la BD y ejecuta el método de respaldo correspondiente.
     * 3) Aplica la política de retención para eliminar backups antiguos.
     */
    public function handle(): int
    {
        $enabled = env('DB_BACKUP_ENABLED', true);
        if (! $enabled) {
            $this->info('Backups deshabilitados por configuración (DB_BACKUP_ENABLED=false).');
            return Command::SUCCESS;
        }

        // Driver de la base de datos configurado (mysql, sqlite, pgsql, etc.).
        $driver = config('database.default');
        $timestamp = Carbon::now()->format('Ymd_His');
        $backupDir = storage_path('app/backups');

        // Crear el directorio de backups si no existe.
        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = "db_backup_{$timestamp}";
        try {
            // Ejecutar la estrategia de backup según el driver configurado.
            switch ($driver) {
                case 'mysql':
                    $this->backupMysql($backupDir, $filename);
                    break;
                case 'sqlite':
                    $this->backupSqlite($backupDir, $filename);
                    break;
                case 'pgsql':
                    $this->backupPgsql($backupDir, $filename);
                    break;
                default:
                    $this->warn("Driver de BD no soportado para backup: {$driver}");
                    return Command::SUCCESS;
            }
        } catch (\Exception $e) {
            $this->error('Error al generar backup: '.$e->getMessage());
            return Command::FAILURE;
        }

        $this->info("Backup completado: {$filename}");

        // Retención: eliminar archivos más antiguos que X días
        $retentionDays = (int) env('DB_BACKUP_RETENTION_DAYS', 7);
        $this->applyRetention($backupDir, $retentionDays);

        return Command::SUCCESS;
    }

    /**
     * Ejecuta respaldo para MySQL usando mysqldump.
     */
    protected function backupMysql(string $backupDir, string $filename): void
    {
        $conn = config('database.connections.mysql');
        $host = $conn['host'] ?? '127.0.0.1';
        $port = $conn['port'] ?? 3306;
        $database = $conn['database'] ?? '';
        $username = $conn['username'] ?? '';
        $password = $conn['password'] ?? '';

        // Nombre de archivo final comprimido con extensión .sql.gz
        $filePath = $backupDir.DIRECTORY_SEPARATOR.$filename.'.sql.gz';

        // Construir y ejecutar comando `mysqldump` y comprimir con gzip.
        $passwordPart = $password !== '' ? "-p'".addslashes($password)."'" : '';
        $command = sprintf(
            'mysqldump --single-transaction --quick --lock-tables=false -h %s -P %s -u %s %s %s | gzip > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $passwordPart,
            escapeshellarg($database),
            escapeshellarg($filePath)
        );

        exec($command, $output, $returnVar);
        if ($returnVar !== 0) {
            throw new \Exception('mysqldump fracasó. Asegúrate de que mysqldump esté instalado y de que las credenciales sean correctas.');
        }
    }

    /**
     * Respaldar SQLite copiando el archivo de la base de datos y comprimiéndolo.
     */
    protected function backupSqlite(string $backupDir, string $filename): void
    {
        $conn = config('database.connections.sqlite');
        $database = $conn['database'] ?? database_path('database.sqlite');
        $source = base_path($database);
        if (! File::exists($source)) {
            throw new \Exception('Archivo SQLite no encontrado: '.$source);
        }
        $dest = $backupDir.DIRECTORY_SEPARATOR.$filename.'.sqlite';
        File::copy($source, $dest);
        // Comprimir el archivo sqlite a gzip y eliminar el archivo temporal.
        $gz = $dest.'.gz';
        $fp = gzopen($gz, 'w9');
        gzwrite($fp, File::get($dest));
        gzclose($fp);
        File::delete($dest);
    }

    /**
     * Respaldar Postgres usando pg_dump (formato personalizado o sql).
     */
    protected function backupPgsql(string $backupDir, string $filename): void
    {
        $conn = config('database.connections.pgsql');
        $host = $conn['host'] ?? '127.0.0.1';
        $port = $conn['port'] ?? 5432;
        $database = $conn['database'] ?? '';
        $username = $conn['username'] ?? '';
        $password = $conn['password'] ?? '';
        $filePath = $backupDir.DIRECTORY_SEPARATOR.$filename.'.sql';

        // Preparar comando con PGPASSWORD en la misma línea para evitar prompt interactivo
        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -F p %s > %s',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($filePath)
        );
        exec($command, $output, $returnVar);
        if ($returnVar !== 0) {
            throw new \Exception('pg_dump fracasó. Asegúrate de que pg_dump esté instalado y de que las credenciales sean correctas.');
        }
    }

    /**
     * Eliminar archivos de backup más antiguos que X días (retention policy).
     */
    protected function applyRetention(string $backupDir, int $days): void
    {
        // Si days <= 0 se entiende que no hay retención configurada (no borrar).
        if ($days <= 0) {
            return; // Si <= 0, no eliminar nada
        }

        // Lista todos los archivos en el directorio de backups y elimina los más antiguos.
        $files = File::files($backupDir);
        $threshold = Carbon::now()->subDays($days);
        foreach ($files as $file) {
            if (Carbon::createFromTimestamp($file->getMTime())->lt($threshold)) {
                File::delete($file->getPathname());
            }
        }
    }
}
