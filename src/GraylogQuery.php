<?php

namespace GraylogQueryBuilder;

/**
 * Graylog Query Builder
 *
 * See the Graylog documentation article on
 * https://docs.graylog.org/en/latest/pages/queries.html
 *
 * @author debugrammer
 * @since 1.0.0
 */
class GraylogQuery
{
    const EXISTS = '_exists_';

    const COLON = ':';

    const TO = 'TO';

    const NOT = 'NOT';

    const AND = 'AND';

    const OR = 'OR';

    const OPEN_PARENTHESIS = '(';

    const CLOSE_PARENTHESIS = ')';

    const TILDE = '~';

    const SPACE = ' ';

    private $queries;

    private function __construct()
    {
        $this->queries = [];
    }

    public static function builder()
    {
        return new GraylogQuery();
    }

    /**
     * Messages that include the term or phrase.
     * @param string $value term or phrase
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function term($value)
    {
        if (!$value) {
            return $this;
        }

        array_push($this->queries, $this->sanitize($value));

        return $this;
    }

    /**
     * AND expression.
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function and()
    {
        array_push($this->queries, self::AND);

        return $this;
    }

    /**
     * Completed Graylog query.
     * @return GraylogQuery completed Graylog query
     * @since 1.0.0
     */
    public function build()
    {
        return implode(self::SPACE, $this->queries);
    }

    /**
     * Sanitize string value.
     * @param string $value
     * @return string
     * @since 1.0.0
     */
    private function sanitize($value)
    {
        if (!$value) {
            return $value;
        }

        return '"' . $this->escape($value) . '"';
    }

    /**
     * Escape input text as specified on Graylog docs.
     * @param string $input
     * @return string
     * @since 1.0.0
     */
    private function escape($input)
    {
        $metaCharacters = [
            '&', '|', ':', '\\', '/', '+', '-', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?'
        ];

        foreach ($metaCharacters as $meta) {
            $input = str_replace($meta, '\\' . $meta, $input);
        }

        return $input;
    }
}
