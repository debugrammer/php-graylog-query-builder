<?php

use PHPUnit\Framework\TestCase;
use GraylogQueryBuilder\GraylogQuery as GraylogQuery;

final class GraylogQueryTest extends TestCase
{
    /**
     * @test
     */
    public function TC_001_INIT()
    {
        $query = GraylogQuery::builder();

        $expect = '';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_002_TERM()
    {
        $query = GraylogQuery::builder()
            ->term('ssh');

        $expect = '"ssh"';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_003_FUZZ_TERM()
    {
        $query = GraylogQuery::builder()
            ->fuzzTerm('ssh');

        $expect = '"ssh"~';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_004_FUZZ_TERM_WITH_DISTANCE()
    {
        $query = GraylogQuery::builder()
            ->fuzzTerm('ssh', 3);

        $expect = '"ssh"~3';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_005_EXISTS()
    {
        $query = GraylogQuery::builder()
            ->exists('type');

        $expect = '_exists_:type';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_006_FIELD()
    {
        $query = GraylogQuery::builder()
            ->field('type', 'ssh');

        $expect = 'type:"ssh"';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_007_NUMERIC_FIELD()
    {
        $query = GraylogQuery::builder()
            ->field('http_response_code', 500);

        $expect = 'http_response_code:500';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_008_RANGE_FIELD()
    {
        $query = GraylogQuery::builder()
            ->opField('http_response_code', '>', 500);

        $expect = 'http_response_code:>500';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_009_FUZZ_FIELD()
    {
        $query = GraylogQuery::builder()
            ->fuzzField('type', 'ssh');

        $expect = 'type:"ssh"~';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_010_FUZZ_FIELD_WITH_DISTANCE()
    {
        $query = GraylogQuery::builder()
            ->fuzzField('type', 'ssh', 3);

        $expect = 'type:"ssh"~3';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_011_RANGE()
    {
        $query = GraylogQuery::builder()
            ->range('http_response_code', '[', 500, 504, '}');

        $expect = 'http_response_code:[500 TO 504}';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_012_DATE_RANGE()
    {
        $query = GraylogQuery::builder()
            ->range('timestamp', '{', '2019-07-23 09:53:08.175', '2019-07-23 09:53:08.575', ']');

        $expect = 'timestamp:{"2019-07-23 09:53:08.175" TO "2019-07-23 09:53:08.575"]';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_013_RAW()
    {
        $query = GraylogQuery::builder()
            ->raw('/ethernet[0-9]+/');

        $expect = '/ethernet[0-9]+/';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_014_NOT()
    {
        $query = GraylogQuery::builder()
            ->not()->exists('type');

        $expect = 'NOT _exists_:type';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_015_AND()
    {
        $query = GraylogQuery::builder()
            ->term('cat')
            ->and()
            ->term('dog');

        $expect = '"cat" AND "dog"';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_016_OR()
    {
        $query = GraylogQuery::builder()
            ->term('cat')
            ->or()
            ->term('dog');

        $expect = '"cat" OR "dog"';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_017_PARENTHESES()
    {
        $query = GraylogQuery::builder()
            ->openParen()
                ->term('ssh login')
                ->and()
                ->openParen()
                    ->field('source', 'example.org')
                    ->or()
                    ->field('source', 'another.example.org')
                ->closeParen()
            ->closeParen()
            ->or()
            ->exists('always_find_me');

        $expect = '( "ssh login" AND ( source:"example.org" OR source:"another.example.org" ) ) OR _exists_:always_find_me';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_018_PREPEND()
    {
        $prepend = GraylogQuery::builder()
            ->not()->exists('type');

        $query = GraylogQuery::builder($prepend)
            ->and()->term("ssh");

        $expect = "NOT _exists_:type AND \"ssh\"";

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_019_APPEND()
    {
        $append = GraylogQuery::builder()
            ->or()->exists('type');

        $query = GraylogQuery::builder()
            ->term('ssh')
            ->append($append);

        $expect = '"ssh" OR _exists_:type';

        $this->assertEquals($expect, $query->build());
    }

    /**
     * @test
     */
    public function TC_020_ESCAPING()
    {
        $query = GraylogQuery::builder()
            ->field('content_type', 'application/json')
            ->and()
            ->field('response_body', '{"nickname": "[*test] John Doe", "message": "hello?"}');

        $expect = 'content_type:"application\/json" AND response_body:"\{\"nickname\"\: \"\[\*test\] John Doe\", \"message\"\: \"hello\?\"\}"';

        $this->assertEquals($expect, $query->build());
    }
}
