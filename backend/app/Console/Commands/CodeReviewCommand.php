<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class CodeReviewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:review
                            {--fix : Automatically fix code style issues}
                            {--memory=512M : Memory limit for PHPStan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run comprehensive code quality checks (PHPStan + Laravel Pint)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting code review...');
        $this->newLine();

        $hasErrors = false;

        // Step 1: PHPStan
        if (!$this->runPhpStan()) {
            $hasErrors = true;
        }

        $this->newLine();

        // Step 2: Laravel Pint
        if (!$this->runPint()) {
            $hasErrors = true;
        }

        $this->newLine();

        // Step 3: Custom checks
        $this->runCustomChecks();

        $this->newLine();

        if ($hasErrors) {
            $this->error('❌ Code review completed with errors. Please fix the issues above.');
            return Command::FAILURE;
        }

        $this->info('✅ Code review complete! All checks passed.');
        return Command::SUCCESS;
    }

    /**
     * Run PHPStan static analysis
     */
    private function runPhpStan(): bool
    {
        $this->info('1️⃣  Running PHPStan (Static Analysis)...');

        $memoryLimit = $this->option('memory');

        $process = new Process([
            'vendor/bin/phpstan',
            'analyse',
            '--memory-limit=' . $memoryLimit,
            '--error-format=table'
        ]);

        $process->setTimeout(300); // 5 minutes
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if ($process->isSuccessful()) {
            $this->info('   ✓ PHPStan: No errors found');
            return true;
        } else {
            $this->warn('   ⚠ PHPStan: Issues found (see above)');
            return false;
        }
    }

    /**
     * Run Laravel Pint for code style
     */
    private function runPint(): bool
    {
        $this->info('2️⃣  Running Laravel Pint (Code Style)...');

        $args = ['vendor/bin/pint'];

        if (!$this->option('fix')) {
            $args[] = '--test';
        }

        $process = new Process($args);
        $process->setTimeout(120); // 2 minutes
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if ($process->isSuccessful()) {
            if ($this->option('fix')) {
                $this->info('   ✓ Pint: Code style fixed automatically');
            } else {
                $this->info('   ✓ Pint: Code style check passed');
            }
            return true;
        } else {
            if ($this->option('fix')) {
                $this->warn('   ⚠ Pint: Some issues could not be fixed automatically');
            } else {
                $this->warn('   ⚠ Pint: Code style issues found. Run with --fix to auto-fix');
            }
            return false;
        }
    }

    /**
     * Run custom security and quality checks
     */
    private function runCustomChecks(): void
    {
        $this->info('3️⃣  Running custom checks...');

        $issues = [];

        // Check for TODO comments
        $todos = $this->findTodoComments();
        if (!empty($todos)) {
            $issues[] = "Found " . count($todos) . " TODO comment(s)";
            foreach (array_slice($todos, 0, 5) as $todo) {
                $this->line("   • {$todo}");
            }
            if (count($todos) > 5) {
                $this->line("   • ... and " . (count($todos) - 5) . " more");
            }
        }

        // Check for potential security issues
        $securityIssues = $this->findSecurityIssues();
        if (!empty($securityIssues)) {
            $this->warn('   ⚠ Potential security issues found:');
            foreach ($securityIssues as $issue) {
                $this->line("   • {$issue}");
            }
        }

        if (empty($issues) && empty($securityIssues)) {
            $this->info('   ✓ No issues found in custom checks');
        }
    }

    /**
     * Find TODO comments in code
     */
    private function findTodoComments(): array
    {
        $process = new Process(['grep', '-rn', '--include=*.php', 'TODO', 'app/', 'routes/']);
        $process->run();

        if ($process->isSuccessful()) {
            return array_filter(explode("\n", $process->getOutput()));
        }

        return [];
    }

    /**
     * Find potential security issues
     */
    private function findSecurityIssues(): array
    {
        $issues = [];
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(app_path())
        );

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());

                // Check for hardcoded passwords (simple pattern)
                if (preg_match('/password\s*=\s*["\'](?!.*\$)(?!.*env\()[\w]+["\']/', $content)) {
                    $issues[] = "Potential hardcoded password in: {$file->getPathname()}";
                }

                // Check for potential SQL injection with DB::raw
                if (preg_match('/DB::raw\([^)]*\$/', $content)) {
                    $issues[] = "Potential SQL injection risk (DB::raw with variable) in: {$file->getPathname()}";
                }

                // Check for eval() usage
                if (preg_match('/\beval\s*\(/', $content)) {
                    $issues[] = "Dangerous eval() usage in: {$file->getPathname()}";
                }
            }
        }

        return $issues;
    }
}
