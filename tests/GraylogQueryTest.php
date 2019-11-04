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
    public function TC_003_AND()
    {
        $query = GraylogQuery::builder()
            ->term('cat')
            ->and()
            ->term('dog');

        $expect = '"cat" AND "dog"';

        $this->assertEquals($expect, $query->build());
    }
}
