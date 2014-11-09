<?php
namespace Yoanm\DataMappingBundle\Tests;

use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

class ProphecyTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Prophet
     */
    protected  $prophet;

    protected function setup()
    {
        $this->prophet = new Prophet();
    }

    protected function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    /**
     * @param null $className
     * @return ObjectProphecy
     */
    protected function getProphecy($className = null)
    {
        return $this->prophet->prophesize($className);
    }

}
