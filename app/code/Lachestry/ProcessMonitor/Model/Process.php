<?php

declare(strict_types=1);

namespace Lachestry\ProcessMonitor\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Shell;
use Magento\Framework\Shell\CommandRendererInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Exception\LocalizedException;

class Process extends DataObject
{
    /**
     * @var Shell
     */
    private $shell;

    /**
     * @var CommandRendererInterface
     */
    private $commandRenderer;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param Shell $shell
     * @param CommandRendererInterface $commandRenderer
     * @param DirectoryList $directoryList
     * @param array $data
     */
    public function __construct(
        Shell $shell,
        CommandRendererInterface $commandRenderer,
        DirectoryList $directoryList,
        array $data = []
    ) {
        parent::__construct($data);
        $this->shell = $shell;
        $this->commandRenderer = $commandRenderer;
        $this->directoryList = $directoryList;
    }

    /**
     * Get list of magento processes
     *
     * @return array
     */
    public function getProcessList()
    {
        $processes = [];
        try {
            $output = $this->shell->execute('ps aux | grep -i "magento" | grep -v grep');
            $lines = explode(PHP_EOL, $output);
            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }
                $parts = preg_split('/\s+/', trim($line));
                if (count($parts) >= 11) {
                    // Получаем время запуска процесса в минутах
                    $startTime = implode(' ', array_slice($parts, 8, 2));
                    
                    // Рассчитываем время выполнения в минутах
                    $executionTimeMinutes = 0;
                    if (preg_match('/(\d+):(\d+)/', $parts[9], $matches)) {
                        // Если формат ЧЧ:ММ, конвертируем в минуты
                        $executionTimeMinutes = (int)$matches[1] * 60 + (int)$matches[2];
                    } elseif (preg_match('/(\d+)-(\d+):(\d+)/', $startTime, $matches)) {
                        // Если формат ДД-ЧЧ:ММ (для долгих процессов)
                        $executionTimeMinutes = (int)$matches[1] * 24 * 60 + (int)$matches[2] * 60 + (int)$matches[3];
                    }
                    
                    $processes[] = [
                        'pid'           => $parts[1],
                        'user'          => $parts[0],
                        'cpu'           => $parts[2],
                        'memory'        => $parts[3],
                        'execution_time' => $executionTimeMinutes,
                        'command'       => implode(' ', array_slice($parts, 10)),
                    ];
                }
            }
        } catch (\Exception $e) {
            // Логирование ошибки можно добавить здесь при необходимости
        }

        return $processes;
    }
}
