DataMappingBundle
=================

Allows data mapping into a table structure

# Usage
## Basic usage
### Position
First you must select a slot in a row. Two way to do it : 
 - One call using `$map->go(COLUMN, ROW)`
 - Two calls by first defining column or row and then the other one 
```php
$map
  ->goColumn(COLUMN)
  ->goRow(ROW)
```
**Warning : column and row position start at 0**
### Slot content
When a slot is selected you can modify it with following functions : 
   - `set($content)` : set the slot content
   - `get()` : get the slot content
   - `isDefined()` : will return true if a content is set to the current selected slot else will return false
   - `reset()` : will unset the slot (i.e. remove the content). Calling `isDefined()`after a `reset()` will return false
   - `exist()` : will return true if the current position is a valid slot position. Will return false if it's called just after object instanciation because no position is defined.

**Warning : Function `set($content)`, `get()`, `isDefined()` and `reset()` will throw an exception if no valid slot position is defined, i.e. if `exist()` return false calling this functions will throw an exception.**

## Relative move
Function `go(COLUMN, [ROW])`, `goColumn(COLUMN)` and `goRow(ROW)` accept column/row index but also accept a moving step count relative to the current slot position.

Example : 
Current position is 2nd column, 4th row (column index 1, row index 3). 
 - Moving to 5th column, same row : ```$map->goColumn('+3')```
 - Moving to 2nd row, same column : ```$map->goRow('-2')```
 - Moving to 5th column, 2nd row  : ```$map->go('+3', '-2')```

## HTML Table behavior
Map can be use like an HTML table with the helper functions `row()` and `slot([SLOT_CONTENT])`.

**Warning : `slot([SLOT_CONTENT])` will throw an exception if a row position has not been defined, i.e. if `row()` or `go(COLUMN, ROW)`, `goRow(ROW)` functions are not called before.**

Calling `row()` will set row position to the next row. Basically, after an instanciation `row()` will set row position to the first row, other call will increase the row position.

Calling `slot([SLOT_CONTENT])` will set column position to the next column. Basically, after an instanciation `slot([SLOT_CONTENT])` will set column position to the first column (first slot of the row), other call will increase the column position on the current row.

Example to reflect this table : 

| A  | B | C |
| ------------- | ------------- | ------------- |
| D  | E  | F  |

```php
/** @var Yoanm\DataMapping\Model\Map $map */
$map->
  ->row()
    ->slot()
      ->set('A')
    ->slot()
      ->set('B')
    ->slot()
      ->set('C')
  ->row()
    ->slot()
      ->set('D')
    ->slot()
      ->set('E')
    ->slot()
      ->set('F')
```
And can be simplify by : 
```php
/** @var Yoanm\DataMapping\Model\Map $map */
$map->
  ->row()
    ->slot('A')
    ->slot('B')
    ->slot('C')
  ->row()
    ->slot('D')
    ->slot('E')
    ->slot('F')
```
If table data are stored in an array : 
```php
$data = array(
  array('A', 'B', 'C'),
  array('D', 'E', 'F')
);
/** @var Yoanm\DataMapping\Model\Map $map */
foreach ($data as $rowData) {
  $map->row();
  foreach ($rowData as $slotData) {
    $map->slot($slotData);//Or $map->slot()->set($slotData)
  }
}
```