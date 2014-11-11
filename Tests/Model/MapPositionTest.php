<?php
namespace Yoanm\DataMappingBundle\Tests\Model;

use Yoanm\DataMappingBundle\Model\Map;
use Yoanm\DataMappingBundle\Tests\ProphecyTestCase;

class MapPositionTest extends ProphecyTestCase
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

    public function testMovingBehaviour()
    {
        $this->assertSame($this->map->exist(), false);
        $this->map->row();
        $this->assertSame($this->map->getRow(), 0);
        $this->assertSame($this->map->exist(), false);
        $this->map->slot();
        $this->assertSame($this->map->getRow(), 0);
        $this->assertSame($this->map->exist(), true);
        $this->assertSame($this->map->getColumn(), 0);
        $this->assertSame($this->map->isDefined(), false);


        $this->map
            ->slot()
            ->slot()
            ->slot()
            ->slot()
            ->slot('test');
        $this->assertSame($this->map->getRow(), 0);
        $this->assertSame($this->map->getColumn(), 5);
        $this->assertSame($this->map->exist(), true);
        $this->assertSame($this->map->isDefined(), true);
        $this->map->slot();
        $this->assertSame($this->map->isDefined(), false);
        $this->map->set('test');
        $this->assertSame($this->map->isDefined(), true);

        $this->map
            ->row()
            ->row()
            ->row();
        $this->assertSame($this->map->exist(), false);
        $this->assertSame($this->map->getRow(), 3);
    }

    public function testPositionBehaviour()
    {
        $this->map
            ->row()
                ->slot();
        $this->assertSame($this->map->getRow(), 0);
        $this->assertSame($this->map->getColumn(), 0);

        $this->map->goColumn();
        $this->assertSame($this->map->getRow(), 0);
        $this->assertSame($this->map->getColumn(), 1);

        $this->map->goColumn('+2');
        $this->assertSame($this->map->getRow(), 0);
        $this->assertSame($this->map->getColumn(), 3);

        $this->map->goColumn('-1');
        $this->assertSame($this->map->getRow(), 0);
        $this->assertSame($this->map->getColumn(), 2);

        $this->map->goRow();
        $this->assertSame($this->map->getRow(), 1);
        $this->assertSame($this->map->getColumn(), 2);

        $this->map->goRow('+4');
        $this->assertSame($this->map->getRow(), 5);
        $this->assertSame($this->map->getColumn(), 2);

        $this->map->goRow('-2');
        $this->assertSame($this->map->getRow(), 3);
        $this->assertSame($this->map->getColumn(), 2);

        $this->map->go(4, 1);
        $this->assertSame($this->map->getRow(), 4);
        $this->assertSame($this->map->getColumn(), 1);

        $this->map->go('-2', '+3');
        $this->assertSame($this->map->getRow(), 2);
        $this->assertSame($this->map->getColumn(), 4);


    }

    /**
     * @dataProvider getBaseData
     *
     * @param mixed[] $data
     */
    public function testSetPosition($data)
    {
        foreach ($data as $row=>$rowData) {
            $this->map->goRow($row);
            foreach ($rowData as $column=>$slotData) {
                $this->map
                    ->goColumn($column)
                    ->set($slotData);
            }
        }

        foreach ($data as $row=>$rowData) {
            $this->map->goRow($row);
            foreach ($rowData as $column=>$slotData) {
                $this->map->goColumn($column);
                $this->assertSame($this->map->get(), $slotData, 'assert get for ' . $row . ':' . $column);
            }
        }
    }

    /**
     * @abstract test to use row/slot then set|get and then row/slot (==> test basic hack for the first cell of the row)
     */
    public function testSetAndNext()
    {
        $firstRow = array(
            0 => array(
                0 => 'A',
                1 => 'B',
                2 => 'C',
                3 => 'D',
            )
        );
        $othersRows = array(
            3 => array(
                0 => 'A',
                4 => 'B',
                5 => 'C',
                7 => 'D',
            ),
            5 => array(
                0 => 'A',
                1 => 'B',
                2 => 'C',
                3 => 'D',
            ),
        );
        $expected = $firstRow + $othersRows;
        $expected[6] = $firstRow[0];

        $this->map
            ->row()
                ->slot('A')
                ->slot('B')
                ->slot('C')
                ->slot('D')
        ;

        $this->map
            ->row()
            ->row()
            ->row();
        foreach ($othersRows[3] as $column=>$slotData) {
            $this->map
                ->goColumn($column)
                ->set($slotData);
        }

        $this->map->goRow(5);
        foreach ($othersRows[5] as $column=>$slotData) {
            $this->map
                ->goColumn($column)
                ->set($slotData);
        }


        $this->map->row();

        foreach ($firstRow[0] as $slotData) {
            $this->map->slot($slotData);
        }

        foreach ($expected as $row=>$rowData) {
            $this->map->goRow($row);
            foreach ($rowData as $column=>$slotData) {
                $this->map->goColumn($column);
                $this->assertSame($this->map->get(), $slotData, 'assert get for ' . $row . ':' . $column);
            }
        }
    }


    public function getBaseData()
    {
        return array(
            array(
                array(
                    0 => array(
                        0 => "A",
                        4 => "B",
                        5 => "C",
                        10 => "D",
                    ),
                    2 => array(
                        2 => "E",
                        3 => "F",
                        4 => "G",
                        6 => "H",
                    )
                )
            )
        );
    }
}
