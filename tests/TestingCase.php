<?php

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestingCase extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
      return [
        'Calhoun\OTF\OTFServiceProvider'
      ];
    }

}
