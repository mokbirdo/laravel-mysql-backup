<?php

namespace Mokbirdo\LaravelMysqlBackup\Console\Commands;

use Ifsnop\Mysqldump\Mysqldump;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class LaravelDbBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {db_connection?} {--path=db_backup} {--type=default} {--count=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make Mysql Backup';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $count = $this->option('count');
        $db_connection = $this->argument('db_connection') ?? config('database.default');
        $type = $this->option('type');

        $path = $this->option('path');
        $path = substr($path, 0, 1) != '/'
            ? app()->storagePath() . DIRECTORY_SEPARATOR . $path
            : $path;

        $this->mkdir($path, true);

        try {
            $db_conf = config('database.connections')[$db_connection];
            $db_name = $db_conf['database'];
            $db_user = $db_conf['username'];
            $db_pass = $db_conf['password'];
            $dump = new Mysqldump(
                'mysql:host=' . $db_conf['host'] . ':' . $db_conf['port'] . ';dbname=' . $db_name,
                $db_user,
                $db_pass,
                [
                    'compress' => Mysqldump::GZIPSTREAM,
                    'add-drop-database' => true,
                    'add-drop-table' => true,
                ]
            );

            $db_backup_path = $path . DIRECTORY_SEPARATOR . $db_name . DIRECTORY_SEPARATOR . $type;

            $this->mkdir($db_backup_path);

            $all_backups = scandir($db_backup_path);
            foreach (array_diff(array_slice($all_backups, 0, -$count + 1), ['..', '.']) as $single_backup) {
                unlink($db_backup_path . DIRECTORY_SEPARATOR . $single_backup);
            };

            $now = new Carbon();
            $db_backup_full_path = $db_backup_path . DIRECTORY_SEPARATOR . $now . '.sql.gzip';
            $dump->start($db_backup_full_path);
        } catch (\Exception $e) {
            die('mysqldump-php error: ' . $e->getMessage());
        }
    }

    private function mkdir($path, $add_git_ignore = false)
    {
        if (!file_exists($path)) {
            if (!mkdir($path, 0777, true)) {
                die('Не удалось создать директорию');
            };
        }

        if ($add_git_ignore && !file_exists($git_ignore = $path . DIRECTORY_SEPARATOR . '.gitignore')) {
            $fd = fopen($path . DIRECTORY_SEPARATOR . '.gitignore', 'w');
            fwrite($fd, '*' . PHP_EOL . '!.gitignore');
            fclose($fd);
        }
    }
}
