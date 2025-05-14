<?php
/**
 * Команда для запуска юнит-тестов
 */
namespace Lachestry\TestRunner\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunTestsCommand extends Command
{
    /**
     * @var \Magento\Framework\Shell
     */
    private $shell;

    /**
     * @param \Magento\Framework\Shell $shell
     */
    public function __construct(
        \Magento\Framework\Shell $shell
    ) {
        $this->shell = $shell;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('test:run')
            ->setDescription('Run unit tests')
            ->addOption(
                'module',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Specific module to test'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getOption('module');
        $command = 'vendor/bin/phpunit';

        if ($module) {
            $command .= ' --filter ' . escapeshellarg($module);
        }

        try {
            $result = $this->shell->execute($command);
            $output->writeln($result);
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
} 