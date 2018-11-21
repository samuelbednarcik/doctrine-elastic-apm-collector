<?php

namespace SamuelBednarcik\ElasticAPMAgent\Collectors\Doctrine;

use SamuelBednarcik\ElasticAPMAgent\CollectorInterface;
use SamuelBednarcik\ElasticAPMAgent\Events\Span;

class DoctrineCollector implements CollectorInterface
{
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
                $span->setName($query['sql']);
                $span->setContext([
                    'params' => $query['params'],
                    'types' => $query['types']
                ]);

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
