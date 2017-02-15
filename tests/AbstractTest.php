<?php

namespace solutionDrive\MySqlDiff;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $filename
     *
     * @return string
     */
    protected function getDatabaseFixture($filename)
    {
        return file_get_contents(__DIR__ . '/fixtures/' . $filename);
    }
}