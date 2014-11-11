<?php
namespace Yoanm\DataMappingBundle\Tests\Model;

use Yoanm\DataMappingBundle\Model\Map;
use Yoanm\DataMappingBundle\Tests\ProphecyTestCase;

class MapColspanTest extends ProphecyTestCase
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

    public function testWithGoFunctions()
    {
        $this->map->go(0, 0);
        $this->assertSame($this->map->hasSpan(), false);
        $this->assertSame($this->map->isSpan(), false);

        $testString = 'test';
        $this->map
            ->set($testString)
            ->colspan(2);

        for ($cpt = 0 ; $cpt < 3 ; $cpt++) {
            $this->assertSame($this->map->getRow(),     0,                              'assert getRow for column '.$cpt);
            $this->assertSame($this->map->getColumn(),  $cpt,                           'assert getColumn for column '.$cpt);
            $this->assertSame($this->map->isSpan(),     ($cpt > 0 && $cpt < 2),         'assert isSpan for column '.$cpt);
            $this->assertSame($this->map->hasSpan(),    ($cpt < 2),                     'assert hasSpan for column '.$cpt);
            $this->assertSame($this->map->get(),        ($cpt < 2)?$testString:false,   'assert get for column '.$cpt);

            $this->map->goColumn();
        }
    }

    public function testWithSlot()
    {
        $testString = 'test';
        $this->map
            ->row()
                ->slot()
                ->slot($testString)
                    ->colspan(2)
                ->slot()
        ;

        $this->assertSame($this->map->getRow(), 0);
        $this->assertSame($this->map->getColumn(), 3);

        $this->map->go(0, 0);
        for ($cpt = 0 ; $cpt < 4 ; $cpt++) {
            $this->assertSame($this->map->getRow(),     0,                                          'assert getRow for column '.$cpt);
            $this->assertSame($this->map->getColumn(),  $cpt,                                       'assert getColumn for column '.$cpt);
            $this->assertSame($this->map->isSpan(),     ($cpt > 1 && $cpt <= 2),                    'assert isSpan for column '.$cpt);
            $this->assertSame($this->map->hasSpan(),    ($cpt >= 1 && $cpt <= 2),                   'assert hasSpan for column '.$cpt);
            $this->assertSame($this->map->get(),        ($cpt > 0 && $cpt <= 2)?$testString:false,  'assert get for column '.$cpt);

            $this->map->goColumn();
        }
    }

    public function testIncreaseColspan()
    {
        $testString = 'test';
        $this->map
                ->go(0, 1)
                    ->colspan(2)
                    ->set($testString)
        ;

        for ($cpt = 1 ; $cpt < 4 ; $cpt++) {
            $this->assertSame($this->map->getRow(),     0,                                          'assert getRow for column '.$cpt);
            $this->assertSame($this->map->getColumn(),  $cpt,                                       'assert getColumn for column '.$cpt);
            $this->assertSame($this->map->isSpan(),     ($cpt > 1 && $cpt <= 2),                    'assert isSpan for column '.$cpt);
            $this->assertSame($this->map->hasSpan(),    ($cpt >= 1 && $cpt <= 2),                   'assert hasSpan for column '.$cpt);
            $this->assertSame($this->map->get(),        ($cpt > 0 && $cpt <= 2)?$testString:false,  'assert get for column '.$cpt);

            $this->map->goColumn();
        }
        $this->map->go(0, 1)->colspan(4);

        for ($cpt = 1 ; $cpt < 6 ; $cpt++) {
            $this->assertSame($this->map->getRow(),     0,                                          'assert getRow for column '.$cpt);
            $this->assertSame($this->map->getColumn(),  $cpt,                                       'assert getColumn for column '.$cpt);
            $this->assertSame($this->map->isSpan(),     ($cpt > 1 && $cpt <= 4),                    'assert isSpan for column '.$cpt);
            $this->assertSame($this->map->hasSpan(),    ($cpt >= 1 && $cpt <= 4),                   'assert hasSpan for column '.$cpt);
            $this->assertSame($this->map->get(),        ($cpt > 0 && $cpt <= 4)?$testString:false,  'assert get for column '.$cpt);

            $this->map->goColumn();
        }
    }

    public function testDecreaseColspan()
    {
        $testString = 'test';
        $this->map
                ->go(0, 1)
                    ->colspan(4)
                    ->set($testString)
        ;

        for ($cpt = 1 ; $cpt < 6 ; $cpt++) {
            $this->assertSame($this->map->getRow(),     0,                                          'assert getRow for column '.$cpt);
            $this->assertSame($this->map->getColumn(),  $cpt,                                       'assert getColumn for column '.$cpt);
            $this->assertSame($this->map->isSpan(),     ($cpt > 1 && $cpt <= 4),                    'assert isSpan for column '.$cpt);
            $this->assertSame($this->map->hasSpan(),    ($cpt >= 1 && $cpt <= 4),                   'assert hasSpan for column '.$cpt);
            $this->assertSame($this->map->get(),        ($cpt > 0 && $cpt <= 4)?$testString:false,  'assert get for column '.$cpt);

            $this->map->goColumn();
        }

        $this->map
            ->go(0, 1)
                ->colspan(2)
        ;

        for ($cpt = 1 ; $cpt < 4 ; $cpt++) {
            $this->assertSame($this->map->getRow(),     0,                                          'assert getRow for column '.$cpt);
            $this->assertSame($this->map->getColumn(),  $cpt,                                       'assert getColumn for column '.$cpt);
            $this->assertSame($this->map->isSpan(),     ($cpt > 1 && $cpt <= 2),                    'assert isSpan for column '.$cpt);
            $this->assertSame($this->map->hasSpan(),    ($cpt >= 1 && $cpt <= 2),                   'assert hasSpan for column '.$cpt);
            $this->assertSame($this->map->get(),        ($cpt > 0 && $cpt <= 2)?$testString:false,  'assert get for column '.$cpt);

            $this->map->goColumn();
        }
    }

    public function testUnspan()
    {
        $testString = 'test';
        $this->map
            ->go(0, 1)
            ->colspan(4)
            ->set($testString)
        ;

        for ($cpt = 1 ; $cpt < 6 ; $cpt++) {
            $this->assertSame($this->map->getRow(),     0,                                          'assert getRow for column '.$cpt);
            $this->assertSame($this->map->getColumn(),  $cpt,                                       'assert getColumn for column '.$cpt);
            $this->assertSame($this->map->isSpan(),     ($cpt > 1 && $cpt <= 4),                    'assert isSpan for column '.$cpt);
            $this->assertSame($this->map->hasSpan(),    ($cpt >= 1 && $cpt <= 4),                   'assert hasSpan for column '.$cpt);
            $this->assertSame($this->map->get(),        ($cpt > 0 && $cpt <= 4)?$testString:false,  'assert get for column '.$cpt);

            $this->map->goColumn();
        }

        $this->map
            ->go(0, 1)
            ->colspan(1)
        ;

        for ($cpt = 1 ; $cpt < 4 ; $cpt++) {
            $this->assertSame($this->map->getRow(),     0,                                          'assert getRow for column '.$cpt);
            $this->assertSame($this->map->getColumn(),  $cpt,                                       'assert getColumn for column '.$cpt);
            $this->assertSame($this->map->isSpan(),     false,                                      'assert isSpan for column '.$cpt);
            $this->assertSame($this->map->hasSpan(),    false,                                      'assert hasSpan for column '.$cpt);
            $this->assertSame($this->map->get(),        ($cpt == 1 )?$testString:false,             'assert get for column '.$cpt);

            $this->map->goColumn();
        }
    }

}
