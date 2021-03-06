<?php
namespace Yoanm\DataMappingBundle\Model;


class Map
{
    const ROOT_SLOT_TO_MAP_ROOT_KEY = 'root';
    const ROOT_SLOT_TO_MAP_CHILDS_KEY = 'childs';
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
     * An object defined in this array for current position mean that current position in on an spanned slot.
     * The object is root slot the top left slot of the spanned area
     * @var array
     */
    private $mapToRootSlot = array();

    /**
     * An array to keep a reference on the child slots (e.g spanned slots) for a root slot
     * Structure : [
     *      OBJECT_HASH: [
     *          Map::ROOT_SLOT_TO_MAP_ROOT_KEY: [ROW_NUMBER, COLUMN_NUMBER],
     *          Map::ROOT_SLOT_TO_MAP_CHILDS_KEY: [
     *              ROW_NUMBER . ':' . COLUMN_NUMBER: [
     *                  ROW_NUMBER,
     *                  COL_NUMBER
     *              ],
     *              ...
     *          ]
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
     * @abstract set current slot content
     *
     * @throws \BadMethodCallException if slot position is not valid
     * @throws \InvalidArgumentException is $content is not a string or a numeric
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
     * @param int $columnCount column count to span from root slot column, can be 1 to unspan
     *
     * @throws \BadMethodCallException if slot position is not valid
     * @throws \InvalidArgumentException if $count is not a positive integer
     *
     * @return Map
     */
    public function colspan($columnCount) {
        $this->exist(true);

        if ($columnCount <= 0) {
            throw new \InvalidArgumentException('$targetPosition must be a positive integer');
        }

        $columnCount = (int)$columnCount;

        if($columnCount > 1) {
            $this->spanColumn($columnCount);
        } else {
            $this->unspanColumn();
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

        if ($this->isDefined()) {
            if ($this->isSpan()) {
                throw new \BadMethodCallException('You cannot reset a spanned slot, unspan it instead');
            }
            $this->colspan(0);//unspan
            $this->unsetSlotAt($this->getRow(), $this->getColumn());

        }


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
     * @throws \BadMethodCallException if slot position is not valid
     *
     * @return bool
     */
    public function isSpan()
    {
        $this->exist(true);

        $row = $this->getRow();
        $column = $this->getColumn();
        return (
            $this->isDefined()
            && array_key_exists($row, $this->mapToRootSlot)
            && array_key_exists($column, $this->mapToRootSlot[$row])
        );
    }

    /**
     * @return array
     */
    public function getRootPosition()
    {
        if (!$this->hasSpan()) {
            //Is not a span, is a simple slot
            return array($this->getRow(), $this->getColumn());
        }

        $hash = spl_object_hash($this->getSlot());

        return $this->rootSlotToMap[$hash][self::ROOT_SLOT_TO_MAP_ROOT_KEY];
    }

    /**
     * @return array
     */
    public function getChildsPosition()
    {
        if (!$this->hasSpan()) {
            return array();
        }

        $hash = spl_object_hash($this->getSlot());

        return $this->rootSlotToMap[$hash][self::ROOT_SLOT_TO_MAP_CHILDS_KEY];
    }

    /**
     * @throws \BadMethodCallException if slot position is not valid
     *
     * @return bool
     */
    public function hasSpan()
    {
        $this->exist(true);

        if ( !$this->isDefined()) {
            return false;
        }
        $hash = spl_object_hash($this->getSlot());
        return (
            array_key_exists($hash, $this->rootSlotToMap)
            && sizeof(($this->rootSlotToMap[$hash][self::ROOT_SLOT_TO_MAP_CHILDS_KEY]))
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
        if(!$this->isDefined()) {
            return false;
        }

        return $this->getSlot()->getContent();
    }



    /**** slot/row management helpers ***/

    /**
     * @abstract helper function set position on next slot, function will check if
     *
     * @param null|string|numeric $content content for the slot [OPTIONAL]
     *
     * @throws \InvalidArgumentException is $content is not null and not a string or a numeric
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
        while($this->isSpan()) {
            $this->goColumn('+1');
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

    /**
     * @deprecated will be removed in 2.0.0
     *
     * @return array
     */
    public function debug()
    {
        return $this->map;
    }

    /**** private ****/

    /**
     * @throws \BadMethodCallException if slot position is not valid
     *
     * @return Slot
     */
    private function getSlot()
    {
        $this->exist(true);

        return $this->getSlotAt($this->getRow(), $this->getColumn());
    }

    /**
     * @param Slot $slot
     *
     * @throws \BadMethodCallException if slot position is not valid
     *
     * @return Map
     */
    private function setSlot(Slot $slot)
    {
        $this->exist(true);

        $this->setSlotAt($slot, $this->getRow(), $this->getColumn());

        return $this;
    }

    /**
     * @return Slot
     */
    private function getSlotAt($row, $column)
    {
        return $this->map[$row][$column];
    }

    /**
     * @param Slot $slot
     *
     * @return Map
     */
    private function setSlotAt(Slot $slot, $row, $column)
    {
        $this->map[$row][$column] = $slot;

        return $this;
    }

    /**
     * @param int $row
     * @param int $column
     *
     * @return Map
     */
    private function unsetSlotAt($row, $column)
    {
        unset($this->map[$row][$column]);

        return $this;
    }

    /**
     * @param Slot  $rootSlot
     * @param int   $row
     * @param int   $column
     *
     * @return Map
     */
    private function setSpanSlotAt(Slot $rootSlot, $row, $column)
    {
        $this->setSlotAt($rootSlot, $row, $column);//just a reference because $rootSlot is an object ;)
        $this->mapToRootSlot[$row][$column] = $rootSlot;//just a reference because $rootSlot is an object ;)

        $this->rootSlotToMap[spl_object_hash($rootSlot)][self::ROOT_SLOT_TO_MAP_CHILDS_KEY][$row . ':' . $column] = array($row, $column);

        return $this;
    }

    /**
     * @param Slot  $rootSlot
     * @param int   $row
     * @param int   $column
     *
     * @return Map
     */
    private function unsetSpanSlotAt(Slot $rootSlot, $row, $column)
    {
        $this->unsetSlotAt($row, $column);
        unset($this->mapToRootSlot[$row][$column]);
        unset($this->rootSlotToMap[spl_object_hash($rootSlot)][self::ROOT_SLOT_TO_MAP_CHILDS_KEY][$row . ':' . $column]);

        return $this;
    }

    /**
     * @param int $columnCount
     *
     * @throws \BadMethodCallException if slot position is not valid
     *
     * @return Map
     */
    private function spanColumn($columnCount)
    {
        $this->exist(true);

        if ($columnCount > 0) {
            if (!$this->isDefined()) {
                //Slot not already defined
                $this->setSlot(new Slot());
            }
            $rootSlot = $this->getSlot();
            $currentRow = $this->getRow();
            $rootPosition = $this->getRootPosition();
            $startColumn = $rootPosition[1];

            $firstColumnToSpan = ($startColumn + 1);
            $lastColumnToSpan = ($startColumn + $columnCount - 1);
            if ($this->hasSpan()) {
                //unspan all columns to span it again. Easier to make difference between existing and what user want
                $this->unspanColumn();
            }

            if ($columnCount > 1) {
                $hash = spl_object_hash($rootSlot);

                if (!array_key_exists($currentRow, $this->mapToRootSlot)) {
                    $this->mapToRootSlot[$currentRow] = array();
                }

                if (!array_key_exists($hash, $this->rootSlotToMap)) {
                    $this->rootSlotToMap[$hash] = array(
                        self::ROOT_SLOT_TO_MAP_ROOT_KEY => array($currentRow, $this->getColumn()),
                        self::ROOT_SLOT_TO_MAP_CHILDS_KEY => array()
                    );
                }

                for ($column = $firstColumnToSpan; $column <= $lastColumnToSpan; $column += 1) {
                    $this->setSpanSlotAt($rootSlot, $currentRow, $column);
                }
            }
        }

        return $this;
    }

    /**
     * @throws \BadMethodCallException if slot position is not valid
     *
     * @return Map
     */
    private function unspanColumn()
    {
        $this->exist(true);

        if (!$this->hasSpan()) {
            //nothing to unspan
            return $this;
        }
        //unspan all spanned columns
        $childsPosition = $this->getChildsPosition();
        $rootPosition = $this->getRootPosition();
        $rootSlot = $this->getSlot();

        $firstSpandColumn = ($rootPosition[1] + 1);
        foreach ($childsPosition as $aChildPosition) {
            if ($aChildPosition[1] < $firstSpandColumn) {
                //do not unspan column equal to root column because root slot can have a rowspan
                continue;
            }
            $this->unsetSpanSlotAt($rootSlot, $aChildPosition[0], $aChildPosition[1]);
        }
        if (!$this->hasSpan()) {
            $this->unsetSpanFor($rootSlot);
        }

        return $this;
    }

    /**
     * @param Slot $rootSlot
     *
     * @return Map
     */
    private function unsetSpanFor(Slot $rootSlot)
    {
        $hash = spl_object_hash($rootSlot);
        unset($this->rootSlotToMap[$hash]);

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