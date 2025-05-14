<?php
declare(strict_types=1);

namespace Lachestry\ProcessMonitor\Model;

use Lachestry\Configuration\Model\Config;
use Magento\Framework\Shell;
use Magento\Framework\Shell\CommandRendererInterface;

class ProcessMonitor
{
    private Config $config;
    private Shell $shell;
    private CommandRendererInterface $commandRenderer;

    public function __construct(
        Config $config,
        Shell $shell,
        CommandRendererInterface $commandRenderer
    ) {
        $this->config = $config;
        $this->shell = $shell;
        $this->commandRenderer = $commandRenderer;
    }

    public function checkProcess(string $processName): bool
    {
        try {
            $command = $this->commandRenderer->render('pgrep', ['-f', $processName]);
            $output = $this->shell->execute($command);
            return !empty(trim($output));
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getProcessInfo(string $processName): array
    {
        try {
            $command = $this->commandRenderer->render('ps', ['aux', '|', 'grep', $processName, '|', 'grep', '-v', 'grep']);
            $output = $this->shell->execute($command);
            $lines = explode(PHP_EOL, trim($output));
            
            $processes = [];
            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }
                
                $parts = preg_split('/\s+/', trim($line));
                if (count($parts) >= 11) {
                    $processes[] = [
                        'pid' => $parts[1],
                        'user' => $parts[0],
                        'cpu' => $parts[2],
                        'memory' => $parts[3],
                        'start_time' => implode(' ', array_slice($parts, 8, 2)),
                        'command' => implode(' ', array_slice($parts, 10))
                    ];
                }
            }
            
            return $processes;
        } catch (\Exception $e) {
            return [];
        }
    }
} 