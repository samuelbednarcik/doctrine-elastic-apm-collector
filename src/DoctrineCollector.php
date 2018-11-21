<?php

namespace SamuelBednarcik\ElasticAPMAgent\Collectors\Doctrine;

use SamuelBednarcik\ElasticAPMAgent\CollectorInterface;
use SamuelBednarcik\ElasticAPMAgent\Events\Span;

class DoctrineCollector implements CollectorInterface
{
    const NO_SUMMARY_QUERIES = [
        '"START TRANSACTION"',
        '"COMMIT"',
        '"END TRANSACTION"'
    ];

    /**
     * @var DoctrineAPMProfiler[]
     */
    private $loggers;

    /**
     * @param DoctrineAPMProfiler[] $loggers
     */
    public function __construct(array $loggers)
    {
        $this->loggers = $loggers;
    }

    /**
     * Create spans from queries
     *
     * @return array
     */
    private function createSpans(): array
    {
        $spans = [];

        foreach ($this->loggers as $logger) {
            foreach ($logger->queries as $query) {
                $span = new Span();
                $span->setType('DB');
                $span->setDuration(round($query['executionMS'] * 1000, 3));
                $span->setTimestamp(intval(round($query['start'] * 1000000)));

                if (array_search($query['sql'], self::NO_SUMMARY_QUERIES) !== false) {
                    $span->setName($query['sql']);
                } else {
                    $span->setName((new SQLSummary($query['sql']))->__toString());

                    $span->setContext([
                        'db' => [
                            'type' => 'sql',
                            'statement' => $query['sql']
                        ]
                    ]);
                }

                $spans[] = $span;
            }
        }

        return $spans;
    }

    /**
     * @return Span[]
     */
    public function getSpans(): array
    {
        return $this->createSpans();
    }
}
