<?php
declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Lachestry\RabbitMQMonitor\Model\TopicList;

class ListTopics extends Command
{
    const OPTION_SHOW_CONSUMERS = 'show-consumers';

    protected TopicList $topicList;

    public function __construct(
        TopicList $topicList,
        string $name = null
    ) {
        $this->topicList = $topicList;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('rabbitmq:topics:list')
            ->setDescription('List all available RabbitMQ topics')
            ->addOption(
                self::OPTION_SHOW_CONSUMERS,
                'c',
                InputOption::VALUE_NONE,
                'Show topic-consumer mapping'
            );
        
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $showConsumers = $input->getOption(self::OPTION_SHOW_CONSUMERS);
        
        if ($showConsumers) {
            return $this->executeShowConsumers($output);
        } else {
            return $this->executeShowTopics($output);
        }
    }
    
    protected function executeShowTopics(OutputInterface $output)
    {
        $topics = $this->topicList->getTopics();
        
        if (empty($topics)) {
            $output->writeln('<info>No RabbitMQ topics found.</info>');
            return 0;
        }
        
        $output->writeln('<info>List of available RabbitMQ topics:</info>');
        
        $table = new Table($output);
        $table->setHeaders(['#', 'Topic Name']);
        
        $i = 1;
        foreach ($topics as $topic) {
            $table->addRow([$i++, $topic]);
        }
        
        $table->render();
        
        $output->writeln(sprintf('<info>Total topics: %d</info>', count($topics)));
        $output->writeln('<info>Use --show-consumers option to view topic-consumer mapping</info>');
        
        return 0;
    }
    
    protected function executeShowConsumers(OutputInterface $output)
    {
        $consumerTopicMap = $this->topicList->getConsumerTopicMap();
        
        if (empty($consumerTopicMap)) {
            $output->writeln('<info>No consumer-topic mappings found.</info>');
            return 0;
        }
        
        $output->writeln('<info>Consumer-Topic mapping:</info>');
        
        $table = new Table($output);
        $table->setHeaders(['#', 'Consumer Name', 'Topic Name']);
        
        $i = 1;
        foreach ($consumerTopicMap as $consumer => $topic) {
            $table->addRow([$i++, $consumer, $topic]);
        }
        
        $table->render();
        
        $output->writeln(sprintf('<info>Total mappings: %d</info>', count($consumerTopicMap)));
        
        return 0;
    }
} 