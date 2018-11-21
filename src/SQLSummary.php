<?php

namespace SamuelBednarcik\ElasticAPMAgent\Collectors\Doctrine;

class SQLSummary
{
    const WHITESPACE = '/[\s:]+/';
    const BORDER_CHARS = '/([;()])/';

    /**
     * @var string
     */
    private $sql;

    /**
     * @param string $sql
     */
    public function __construct(string $sql)
    {
        $this->sql = $sql;
    }

    /**
     * @param array $tokens
     * @return array
     */
    private function stripTokens(array $tokens): array
    {
        $verb = strtoupper($tokens[0]);

        $result = array_merge([$verb], $this->afterVerb($tokens));

        return array_filter($result, function ($token) {
            return !!$token;
        });
    }

    /**
     * @param array $tokens
     * @return array
     */
    private function afterVerb(array $tokens): array
    {
        switch (strtoupper($tokens[0])) {
            case 'SELECT':
                return $this->afterToken('FROM', $tokens);
                break;
            case 'INSERT':
                return $this->afterToken('INTO', $tokens);
                break;
            case 'UPDATE':
                return [$tokens[1]];
                break;
            case 'DELETE':
                return $this->afterToken('FROM', $tokens);
                break;
            case 'CREATE':
                return $this->afterToken(['DATABASE', 'TABLE', 'INDEX'], $tokens);
                break;
            case 'DROP':
                return $this->afterToken(['DATABASE', 'TABLE'], $tokens);
                break;
            case 'ALTER':
                return $this->afterToken('TABLE', $tokens);
                break;
            case 'DESC':
                return [$tokens[1]];
                break;
            case 'TRUNCATE':
                return $this->afterToken('TABLE', $tokens);
                break;
            case 'USE':
                return [$tokens[1]];
                break;
            default:
                return ['UNKNOWN'];
                break;
        }
    }

    /**
     * @param string|array $find
     * @param array $tokens
     * @return array
     */
    private function afterToken($find, array $tokens): array
    {
        $index = null;

        if (!is_array($find)) {
            $find = [$find];
        }

        for ($i = 0; $i < count($tokens); $i++) {
            $index = array_search(strtoupper($tokens[$i]), $find);
            if ($index !== false) {
                return [$find[$index], $tokens[$i+1]];
            }
        }

        return [];
    }

    /**
     * @param string $sql
     * @return array
     */
    private function tokenize(string $sql): array
    {
        return explode(' ', $this->normalize($sql));
    }

    /**
     * @param string $sql
     * @return string
     */
    private function normalize(string $sql): string
    {
        $result = preg_replace(self::BORDER_CHARS, ' $1 ', $sql);
        $result = preg_replace(self::WHITESPACE, ' ', $result);
        return trim($result);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $tokens = $this->tokenize($this->sql);
        return implode(' ', $this->stripTokens($tokens));
    }
}
