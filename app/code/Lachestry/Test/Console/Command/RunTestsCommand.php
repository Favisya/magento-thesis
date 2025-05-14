<?php
declare(strict_types=1);

namespace Lachestry\Test\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Magento\Framework\App\Filesystem\DirectoryList;

class RunTestsCommand extends Command
{
    private const OPTION_MODULE   = 'module';
    private const OPTION_TEST     = 'test';
    private const OPTION_COVERAGE = 'coverage';
    private const OPTION_ALL      = 'all';
    private const OPTION_DEBUG    = 'debug';
    private const OPTION_TESTDOX  = 'testdox';

    private DirectoryList $directoryList;

    public function __construct(
        DirectoryList $directoryList,
        string $name = null
    ) {
        parent::__construct($name);
        $this->directoryList = $directoryList;
    }

    protected function configure(): void
    {
        $this->setName('lachestry:test:run')
            ->setDescription('Запуск модульных тестов')
            ->addOption(
                self::OPTION_MODULE,
                'm',
                InputOption::VALUE_OPTIONAL,
                'Имя модуля для тестирования'
            )
            ->addOption(
                self::OPTION_TEST,
                't',
                InputOption::VALUE_OPTIONAL,
                'Имя конкретного теста'
            )
            ->addOption(
                self::OPTION_COVERAGE,
                'c',
                InputOption::VALUE_NONE,
                'Включить отчет о покрытии кода'
            )
            ->addOption(
                self::OPTION_ALL,
                'a',
                InputOption::VALUE_NONE,
                'Запустить все юнит-тесты'
            )
            ->addOption(
                self::OPTION_DEBUG,
                'd',
                InputOption::VALUE_NONE,
                'Подробный вывод с отладочной информацией'
            )
            ->addOption(
                self::OPTION_TESTDOX,
                null,
                InputOption::VALUE_NONE,
                'Вывести тесты в читаемом формате с их описаниями'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $module = $input->getOption(self::OPTION_MODULE);
        $test = $input->getOption(self::OPTION_TEST);
        $coverage = $input->getOption(self::OPTION_COVERAGE);
        $all = $input->getOption(self::OPTION_ALL);
        $debug = $input->getOption(self::OPTION_DEBUG);
        $testdox = $input->getOption(self::OPTION_TESTDOX);

        $output->writeln('<info>Запуск юнит-тестов Lachestry</info>');
        
        if ($all) {
            $output->writeln('<comment>Запускаем все тесты</comment>');
        } elseif ($module) {
            $output->writeln("<comment>Запускаем тесты для модуля {$module}</comment>");
        } elseif ($test) {
            $output->writeln("<comment>Запускаем тест {$test}</comment>");
        }

        $rootDir = $this->directoryList->getRoot();
        $phpunitConfig = $rootDir . '/phpunit.xml';

        $command = [
            'vendor/bin/phpunit',
            '--configuration',
            $phpunitConfig,
            '--testdox'
        ];

        if ($debug) {
            $command[] = '--debug';
            $command[] = '--verbose';
        }

        if ($coverage) {
            $command[] = '--coverage-html';
            $command[] = $rootDir . '/dev/tests/coverage';
        }

        if ($all) {
            // Используем тестовую сюиту "Unit Tests", определенную в phpunit.xml
            $command[] = '--testsuite';
            $command[] = 'Unit Tests';
        } elseif ($module) {
            $command[] = '--filter';
            $command[] = $this->getModuleFilter($module);
        } elseif ($test) {
            $command[] = '--filter';
            $command[] = $test;
        }

        if ($debug) {
            $output->writeln('Executing command: ' . implode(' ', $command));
        }

        $process = new Process($command, $rootDir);
        $process->setTty(true);
        
        // Устанавливаем переменную окружения для отладки
        if ($debug) {
            $process->setEnv(['PHPUNIT_DEBUG' => '1']);
        }
        
        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        return $process->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
    }

    private function getModuleFilter(string $module): string
    {
        return sprintf('Lachestry\\\\%s\\\\Test', $module);
    }
}
