<?php

namespace SamuelBednarcik\ElasticAPMAgent\Collectors\Doctrine;

use Doctrine\DBAL\Logging\DebugStack;

class DoctrineAPMProfiler extends DebugStack
{
    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        if ($this->enabled) {
            $this->start = microtime(true);
            $this->queries[++$this->currentQuery] = [
                'sql' => $sql,
                'params' => $params,
                'types' => $types,
                'executionMS' => 0,
                'start' => $this->start
            ];
        }
    }
}
