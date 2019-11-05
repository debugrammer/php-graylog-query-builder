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
    /**
     * @var string
     */
    const EXISTS = '_exists_';

    /**
     * @var string
     */
    const COLON = ':';

    /**
     * @var string
     */
    const TO = 'TO';

    /**
     * @var string
     */
    const NOT = 'NOT';

    /**
     * @var string
     */
    const AND = 'AND';

    /**
     * @var string
     */
    const OR = 'OR';

    /**
     * @var string
     */
    const OPEN_PARENTHESIS = '(';

    /**
     * @var string
     */
    const CLOSE_PARENTHESIS = ')';

    /**
     * @var string
     */
    const TILDE = '~';

    /**
     * @var string
     */
    const SPACE = ' ';

    /**
     * @var array
     */
    private $queries;

    /**
     * @param array $queries
     */
    private function __construct($queries)
    {
        $this->queries = $queries;
    }

    /**
     * @param GraylogQuery $query
     * @return GraylogQuery
     * @static
     */
    public static function builder($query = null)
    {
        if ($query === null) {
            return new GraylogQuery([]);
        }

        return new GraylogQuery($query->queries);
    }

    /**
     * @param GraylogQuery $query
     * @return GraylogQuery
     */
    public function append($query) {
        $this->queries = array_merge($this->queries, $query->queries);

        return $this;
    }

    /**
     * Messages that include the term or phrase.
     * @param int|string $value term or phrase
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function term($value)
    {
        if (!$value) {
            $this->removeEndingConj();

            return $this;
        }

        if (!is_numeric($value)) {
            $value = $this->sanitize($value);
        }

        array_push($this->queries, $value);

        return $this;
    }

    /**
     * Fuzziness with distance.
     * Messages that include similar term or phrase.
     * @param string $value term or phrase
     * @param int|string $distance Damerau-Levenshtein distance
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function fuzzTerm($value, $distance = '')
    {
        if ($distance && !is_numeric($distance)) {
            $distance = '';
        }

        if (!$value) {
            $this->removeEndingConj();

            return $this;
        }

        array_push($this->queries, $this->sanitize($value) . self::TILDE . $distance);

        return $this;
    }

    /**
     * Messages that have the field.
     * @param string $field field name
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function exists($field)
    {
        array_push($this->queries, self::EXISTS . self::COLON . $field);

        return $this;
    }

    /**
     * Messages where the field includes the term or phrase.
     * @param string $field field name
     * @param int|string $value term or phrase
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function field($field, $value)
    {
        if (!$value) {
            $this->removeEndingConj();

            return $this;
        }

        if (!is_numeric($value)) {
            $value = $this->sanitize($value);
        }

        array_push($this->queries, $field . self::COLON . $value);

        return $this;
    }

    /**
     * One side unbounded range query.
     * Messages where the field satisfies the condition.
     * @param string $field field name
     * @param string $operator range operator
     * @param int $value number
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function opField($field, $operator, $value)
    {
        array_push($this->queries, $field . self::COLON . $operator . $value);

        return $this;
    }

    /**
     * Fuzziness with distance.
     * Messages where the field includes similar term or phrase.
     * @param string $field field name
     * @param string $value term or phrase
     * @param int|string $distance Damerau-Levenshtein distance
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function fuzzField($field, $value, $distance = '')
    {
        if ($distance && !is_numeric($distance)) {
            $distance = '';
        }

        if (!$value) {
            $this->removeEndingConj();

            return $this;
        }

        array_push($this->queries, $field . self::COLON . $this->sanitize($value) . self::TILDE . $distance);

        return $this;
    }

    /**
     * Range query.
     * Ranges in square brackets are inclusive, curly brackets are exclusive and can even be combined.
     * @param string $field field name
     * @param string $fromBracket from bracket
     * @param int|string $from from number/date
     * @param int|string $to to number/date
     * @param string $toBracket to bracket
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function range($field, $fromBracket, $from, $to, $toBracket)
    {
        if (is_string($from) && is_string($to)) {
            $from = '"' . $from . '"';
            $to = '"' . $to . '"';
        }

        $query = $field . self::COLON . $fromBracket . $from . self::SPACE . self::TO . self::SPACE . $to . $toBracket;
        array_push($this->queries, $query);

        return $this;
    }

    /**
     * Raw query.
     * @param string $raw raw Graylog query
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function raw($raw)
    {
        array_push($this->queries, $raw);

        return $this;
    }

    /**
     * NOT expression.
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function not()
    {
        array_push($this->queries, self::NOT);

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
     * OR expression.
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function or()
    {
        array_push($this->queries, self::OR);

        return $this;
    }

    /**
     * Open parenthesis.
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function openParen()
    {
        array_push($this->queries, self::OPEN_PARENTHESIS);

        return $this;
    }

    /**
     * Close parenthesis.
     * @return GraylogQuery used to chain calls
     * @since 1.0.0
     */
    public function closeParen()
    {
        array_push($this->queries, self::CLOSE_PARENTHESIS);

        return $this;
    }

    /**
     * Completed Graylog query.
     * @return string completed Graylog query
     * @since 1.0.0
     */
    public function build()
    {
        $this->removeStartingConj();

        return implode(self::SPACE, $this->queries);
    }

    /**
     * Remove the conjunction at the end.
     * @since 1.0.0
     */
    private function removeEndingConj()
    {
        if (empty($this->queries)) {
            return;
        }

        $conjunctions = [self::AND, self::OR, self::NOT];
        $lastIndex = count($this->queries) - 1;
        $lastQuery = $this->queries[$lastIndex];

        if (in_array($lastQuery, $conjunctions)) {
            unset($this->queries[$lastIndex]);
        }
    }

    /**
     * Remove the starting conjunction.
     * @since 1.0.0
     */
    private function removeStartingConj()
    {
        if (empty($this->queries)) {
            return;
        }

        $conjunctions = [self::AND, self::OR];
        $firstIndex = 0;
        $firstQuery = $this->queries[$firstIndex];

        if (in_array($firstQuery, $conjunctions)) {
            unset($this->queries[$firstIndex]);
        }

        if (count($this->queries) === 1 && $firstQuery === self::NOT) {
            unset($this->queries[$firstIndex]);
        }
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
            '\\', '&', '|', ':', '/', '+', '-', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?'
        ];

        foreach ($metaCharacters as $meta) {
            $input = str_replace($meta, '\\' . $meta, $input);
        }

        return $input;
    }
}
