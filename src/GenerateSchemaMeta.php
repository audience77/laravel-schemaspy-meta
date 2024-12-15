<?php
declare(strict_types = 1);

namespace Audience77\LaravelSchemaspyMeta;

use Illuminate\Console\Command;

class GenerateSchemaMeta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:schemaspy-meta
                            {--modelRootDir=app/Models : Eloquent Model files under the specified root directory (including subdirectories) will be processed.}
                            {--excludeModelFiles=* : The specified files are ignored.}
                            {--xmlFile=schemaspy/schemaspy-meta.xml : The output file path.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate schemaspy-meta.xml from Eloquent Models.';

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
        $modelRootDir = $this->option('modelRootDir');
        $excludeModelFiles = $this->option('excludeModelFiles');
        $xmlFile = $this->option('xmlFile');

        $phpFiles = $this->getPhpFiles($modelRootDir, $excludeModelFiles);
        if (empty($phpFiles)) {
            return;
        }
        $phpFileAsts = $this->getPhpFileAsts($phpFiles);
        if (empty($phpFileAsts)) {
            return;
        }

        SchemaMeta::generate($phpFileAsts, base_path() . '/' . $xmlFile);
        $this->info('Successfully generated schemaspy meta.');
    }

    /**
     * @param string $modelRootDir
     * @param string[] $excludeModelFiles
     * @return string[]
     */
    private function getPhpFiles(string $modelRootDir, array $excludeModelFiles): array
    {
        $phpFiles = $this->getPhpFilesRecursive($modelRootDir);
        $phpFiles = array_diff($phpFiles, $excludeModelFiles);
        if (empty($phpFiles)) {
            $this->info('Not found PHP files under the specified root directory.');
        }

        return $phpFiles;
    }

    /**
     * @param string[] $phpFiles
     * @return PhpAstParser[]
     */
    private function getPhpFileAsts(array $phpFiles): array
    {
        $asts = array_filter(array_map(function ($phpFile) {
            $ast = new PhpAstParser($phpFile);
            if (is_null($ast->class)) {
                return null;
            }

            $ref = new \ReflectionClass($ast->class);
            return $this->isModelClass($ref) ? $ast : null;
        }, $phpFiles));

        if (empty($asts)) {
            $this->info('Not found Eloquent Model files under the specified root directory.');
        }

        return $asts;
    }

    /**
     * @param string $rootDir
     * @return string[]
     */
    private function getPhpFilesRecursive(string $rootDir): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootDir));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * @param \ReflectionClass $ref
     * @return bool
     */
    private function isModelClass(\ReflectionClass $ref): bool
    {
        while ($ref) {
            if ($ref->getName() === \Illuminate\Database\Eloquent\Model::class) {
                return true;
            }

            $ref = $ref->getParentClass();
        }

        return false;
    }
}
