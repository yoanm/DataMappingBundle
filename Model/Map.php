<?php
namespace Yoanm\DataMappingBundle\Model;


class Map
{

    /**
     * Array containing slots objects
     * Structure : [
     *      ROW_NUMBER: [
     *          COLUMN_NUMBER: Slot object,
     *          ...
     *      ],
     *      ...
     * ]
     * @var array
     */
    private $map = array();

    /**
     * Array with the same structure a $map but slots present in this array are root slot of the current slot.
     * An object defined in this array for current position mean that current position in on an merged slot.
     * The object is root slot the top left slot of the merged area
     * @var array
     */
    private $mapToRootSlot = array();

    /**
     * An array to keep a reference on the child slots (e.g merged slots) for a root slot
     * Structure : [
     *      OBJECT_HASH: [
     *          ROW_NUMBER . ':' . COLUMN_NUMBER: [
     *              ROW_NUMBER,
     *              COL_NUMBER
     *          ],
     *          ...
     *      ],
     *      ...
     * ]
     * @var array
     */
    private $rootSlotToMap = array();

    /**
     * @var int
     */
    private $column = null;
    /**
     * @var int
     */
    private $row = null;
    
    /**** GET/SET content helpers for current slot ***/

    /**
     * @abstract set current slot content or a slot object
     *
     * @throws \BadMethodCallException if slot position is not valid
     * @throws \InvalidArgumentException is $content is not a string, a numeric or Slot object
     *
     * @param string|numeric $content
     *
     * @return Map
     */
    public function set($content)
    {

        $this->exist(true);

        if(!is_numeric($content) && !is_string($content))
        {
            throw new \InvalidArgumentException(sprintf('$content must be a string or a numeric, \'%s\' given', gettype($content)));
        }

        if (!$this->isDefined()) {
            $this->setSlot(new Slot($content));
        } else {
            $this->getSlot()->setContent($content);
        }

        return $this;
    }

    /**
     * @param int $count a positive integer
     *
     * @throws \BadMethodCallException if slot position is not valid
     * @throws \InvalidArgumentException if $count is not a positive integer
     *
     * @return Map
     */
    public function colspan($count)
    {
        $this->merge((string)$count);

        return $this;
    }

    /**
     *
     * @throws \BadMethodCallException if slot position is not valid
     * @throws \InvalidArgumentException if $count is not a positive integer
     *
     * @param string $targetPosition
     *  - 'X' : X is a positive integer
     *
     * @return Map
     */
    public function merge($targetPosition)
    {
        $this->exist(true);

        $columnCount = abs((int)$targetPosition);
        if ($targetPosition <= 0) {
            throw new \InvalidArgumentException('$targetPosition must be a positive integer');
        }

        $startColumn = ($this->getColumn() + 1);
        $endColumn = ($this->getColumn() + $columnCount);

        if (!$this->isDefined()) {
            //Slot not already defined
            $this->setSlot(new Slot());
        }
        $rootSlot = $this->getSlot();
        $currentRow = $this->getRow();


        $hash = spl_object_hash($rootSlot);

        if (!array_key_exists($currentRow, $this->mapToRootSlot[$hash])) {
            $this->mapToRootSlot[$currentRow] = array();
        }

        if (!array_key_exists($hash, $this->rootSlotToMap)) {
            $this->rootSlotToMap[$hash] = array();
        }

        for ($column = $startColumn; $column < $endColumn ; $column++) {
            $this->map[$currentRow][$startColumn] = $rootSlot;//just a reference because $rootSlot is an object ;)
            $this->mapToRootSlot[$currentRow][$column] = $rootSlot;//just a reference because $rootSlot is an object ;)

            $this->rootSlotToMap[$hash][$currentRow . ':' . $column] = array($currentRow, $column);
        }

        return $this;
    }

    /**
     * @abstract unset current slot content
     *
     * @throws \BadMethodCallException if slot position is not valid
     *
     * @return Map
     */
    public function reset()
    {

        $this->exist(true);

        unset($this->map[$this->getRow()][$this->getColumn()]);

        return $this;
    }

    /**
     * @throws \BadMethodCallException if row or column are not defined
     *
     * @param bool $throw if true and slot position is not define, an exception will be throw
     *
     * @return Map
     */
    public function exist($throw=false)
    {
        if ( $throw === false) {
            try {
                $this->exist(true);
                return true;

            } catch(\BadMethodCallException $e) {
                return false;
            }
        }
        $this
            ->checkRowDefined()
            ->checkColumnDefined();

        return true;
    }

    /**
     *
     * @throws \BadMethodCallException if slot position is not valid
     *
     * @return bool
     */
    public function isDefined()
    {
        $this->exist(true);

        $column = $this->getColumn();
        $row    = $this->getRow();

        return (
            array_key_exists($row, $this->map)
            && array_key_exists($column, $this->map[$row])
            && $this->map[$row][$column] instanceof Slot
        );
    }

    /**
     * @abstract return the current Slot
     *
     * @throws \BadMethodCallException if slot position is not valid
     *
     * @return mixed false if not defined
     */
    public function get()
    {
        $slot = $this->getSlot();

        if(!$slot) {
            return false;
        }

        return $slot->getContent();
    }



    /**** slot/row management helpers ***/

    /**
     * @abstract helper function set position on next slot
     *
     * @param null|string|numeric $content content for the slot [OPTIONAL]
     *
     * @throws \InvalidArgumentException is $content is not null and not a string, a numeric or Slot object
     *
     * @return Map
     */
    public function slot($content=null)
    {
        $this->checkRowDefined();
        if ($this->column !== null) {
            $this->goColumn('+1');
        } else {
            $this->setColumn(0);
        }
        if (null !== $content) {
            $this->set($content);
        }
        return $this;

    }

    /**
     * @abstract helper function to go to next row, first column.
     *
     * @return Map
     */
    public function row()
    {
        if ($this->row !== null) {
            $this->goRow('+1');
        } else {
            $this->setRow(0);
        }
        $this->column = null;

        return $this;
    }

    /**** navigation helpers ****/

    /**
     * @abstract helper function to navigate on the map
     *
     * @throws \InvalidArgumentException if column or row are not good
     *
     * @param int|string        $column the column number (from 0) or a string starting with + or - sign followed by an integer
     * @param int|string   $row the row number (from 0) or a string starting with + or - sign followed by an integer
     *
     * @return Map
     */
    public function go($row, $column)
    {
        return $this
            ->goColumn($column)
            ->goRow($row);

    }

    /**
     * @abstract helper to navigate on the row columns
     *
     * @throws \InvalidArgumentException if column is not good
     *
     * @param null|int|string $column the column number (from 0) or a string starting with + or - sign followed by an integer. default is +1
     *
     * @return Map
     */
    public function goColumn($column='+1')
    {
        $exception = false;
        if (is_string($column)) {
            $sign = $column[0];
            if ($sign === '+' || $sign === '-') {
                $number = substr($column, 1);
                if ((string)((int)$number) !== $number) {
                    $exception = true;
                } else {
                    $column = $this->getColumn() + (int)$column;
                }
            } else {
                $exception = true;
            }
        } elseif (!is_int($column) && (string)((int)$column) !== $column) {
            $exception = true;
        }
        if($exception === true) {
            throw new \InvalidArgumentException(sprintf('column must be an integer or a string starting with + or - sign followed by an integer, \'%s\' given', $column));
        }

        return $this->setColumn($column);
    }

    /**
     * @abstract helper to navigate on the row columns
     *
     * @throws \InvalidArgumentException if  row is not good
     *
     * @param null|int|string   $row the row number (from 0) or a string starting with + or - sign followed by an integer. default is +1
     *
     * @return Map
     */
    public function goRow($row='+1')
    {
        $exception = false;
        if (is_string($row)) {
            $sign = $row[0];
            if ($sign === '+' || $sign === '-') {
                $number = substr($row, 1);
                if ((string)((int)$number) !== $number) {
                    $exception = true;
                } else {
                    $row = $this->getRow() + (int) $row;
                }
            } else {
                $exception = true;
            }
        } elseif (!is_int($row) && (string)((int)$row) !== $row) {
            $exception = true;
        }
        if($exception === true) {
            throw new \InvalidArgumentException(sprintf('row must be an integer or a string starting with + or - sign followed by an integer, \'%s\' given', $row));
        }

        return $this->setRow($row);
    }

    /**
     * @return int
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @return int
     */
    public function getColumn()
    {
        return $this->column;
    }


    /**** debug ****/

    public function debug()
    {
        return $this->map;
    }

    /**** private ****/

    /**
     * @return Slot
     */
    private function getSlot()
    {
        $this->exist(true);

        return $this->map[$this->getRow()][$this->getColumn()];
    }

    /**
     * @param Slot $slot
     *
     * @return Map
     */
    private function setSlot(Slot $slot)
    {
        $this->exist(true);

        $this->map[$this->getRow()][$this->getColumn()] = $slot;

        return $this;
    }

    /**
     * @param int $row
     *
     * @return Map
     */
    private function setRow($row)
    {
        $this->row = abs((int)$row);
        return $this;

    }

    /**
     * @param int $column
     *
     * @return Map
     */
    private function setColumn($column)
    {
        $this->column = abs((int)$column);
        return $this;

    }



    /**
     * @throws \BadMethodCallException if row is not defined
     *
     * @return Map
     */
    private function checkRowDefined()
    {
        if ($this->row === null) {
            throw new \BadMethodCallException('no row set');
        }

        return $this;
    }

    /**
     * @throws \BadMethodCallException if column is not defined
     *
     * @return Map
     */
    private function checkColumnDefined()
    {
        if ($this->column === null) {
            throw new \BadMethodCallException('no column set');
        }

        return $this;
    }
}
