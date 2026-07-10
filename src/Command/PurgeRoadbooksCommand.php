<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:roadbook:purge',
    description: 'Delete generated roadbooks older than the retention period (30 days by default)',
)]
class PurgeRoadbooksCommand extends Command
{
    private const DEFAULT_RETENTION_DAYS = 30;

    public function __construct(
        #[Autowire('%app.roadbook_dir%')]
        private readonly string $roadbookDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('days', null, InputOption::VALUE_REQUIRED, 'Retention period in days', (string) self::DEFAULT_RETENTION_DAYS)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'List the files that would be deleted without deleting them');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = max(1, (int) $input->getOption('days'));
        $dryRun = (bool) $input->getOption('dry-run');
        $threshold = time() - $days * 86400;

        $files = array_merge(
            glob($this->roadbookDir . '/*.{gpx,html,json}', GLOB_BRACE) ?: [],
            glob($this->roadbookDir . '/pdf/*.pdf') ?: [],
        );

        $deleted = 0;
        foreach ($files as $file) {
            if (filemtime($file) >= $threshold) {
                continue;
            }
            if ($dryRun) {
                $io->writeln('Would delete ' . $file);
            } else {
                @unlink($file);
            }
            ++$deleted;
        }

        $io->success(sprintf('%s%d roadbook file(s) older than %d days.', $dryRun ? '[dry-run] ' : 'Deleted ', $deleted, $days));

        return Command::SUCCESS;
    }
}
