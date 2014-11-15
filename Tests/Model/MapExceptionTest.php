<?php
namespace Yoanm\DataMappingBundle\Tests\Model;

use Yoanm\DataMappingBundle\Model\Map;
use Yoanm\DataMappingBundle\Tests\ProphecyTestCase;

class MapExceptionTest extends ProphecyTestCase
{
    /**
     * @var Map
     */
    protected $map;

    protected function setup()
    {
        parent::setup();

        $this->map = new Map();
    }

    public function getGoData()
    {
        return array(
            array(true),
            array(null),
            array('A'),
            array('1&'),
            array('+&'),
            array('+1.2'),
            array('-f'),
        );
    }

    /**
     * @dataProvider getGoData
     *
     * @param mixed $data
     */
    public function testGoColumnException($data)
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->map->goColumn($data);
    }

    /**
     * @dataProvider getGoData
     *
     * @param mixed $data
     */
    public function testGoRowException($data)
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->map->goRow($data);
    }

    public function testExistException()
    {
        $this->map
            ->row()
                ->slot()
        ;
        $this->assertSame($this->map->exist(), true);
        $this->map->row();
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map->exist(true);
    }

    public function testSlotException()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map->slot();
    }

    public function testSetException()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map->set('test');
    }

    public function testSetException2()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map
            ->row()
                ->slot()
                    ->set('test')
            ->row()
                ->set('test2');
    }

    public function testResetException()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map->reset();
    }

    public function testResetException2()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map
            ->row()
                ->slot()
                    ->set('test')
            ->row()
                ->reset();
    }

    public function testIsDefinedException()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map->isDefined();
    }

    public function testIsDefinedException2()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map
            ->row()
                ->slot()
                    ->set('test')
            ->row()
                ->isDefined();
    }

    public function testGetException()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map->get();
    }

    public function testGetException2()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map
            ->row()
                ->slot()
                    ->set('test')
            ->row()
                ->get();
    }

    public function testColspanException()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map->colspan(1);
    }

    public function testColspanException2()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map
            ->row()
            ->slot()
            ->set('test')
            ->row()
            ->colspan(1);
    }

    public function testColspanException3()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\InvalidArgumentException');
        $this->map->go(0,0)->colspan(0);
    }

    public function testisSpanException()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map->isSpan();
    }

    public function testisSpanException2()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map
            ->row()
            ->slot()
            ->set('test')
            ->row()
            ->isSpan();
    }

    public function testhasSpanException()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map->hasSpan();
    }

    public function testhasSpanException2()
    {
        $this->assertSame($this->map->exist(), false);
        $this->setExpectedException('\BadMethodCallException');
        $this->map
            ->row()
            ->slot()
            ->set('test')
            ->row()
            ->hasSpan();
    }
}
