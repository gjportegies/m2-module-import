<?php

namespace Jh\ImportTest\Import;

use Jh\Import\Import\Record;
use PHPUnit\Framework\TestCase;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class RecordTest extends TestCase
{
    public function testGetRowNumber()
    {
        $record = new Record(10, []);
        self::assertEquals(10, $record->getRowNumber());
    }

    public function testSetColumnValue()
    {
        $record = new Record(10, []);
        self::assertEquals(null, $record->getColumnValue('column1'));

        $record->setColumnValue('column1', 'value');
        self::assertEquals(null, $record->getColumnValue('value'));
    }

    public function testUnset()
    {
        $record = new Record(10, ['column1' => 'value']);

        $record->unset('column1');
        self::assertNull($record->getColumnValue('column1'));
    }

    public function testUnsetMany()
    {
        $record = new Record(10, ['column1' => 'value1', 'column2' => 'value2']);

        $record->unsetMany('column1', 'column2');
        self::assertNull($record->getColumnValue('column1'));
        self::assertNull($record->getColumnValue('column2'));
    }

    public function testGetColumnValue()
    {
        $record = new Record(10, ['column1' => 'value1', 'column2' => 'value2']);

        self::assertEquals('value1', $record->getColumnValue('column1'));
        self::assertEquals('value2', $record->getColumnValue('column2'));
    }

    public function testGetColumnValueReturnsDefaultIfNotSet()
    {
        $record = new Record(10, []);

        self::assertNull($record->getColumnValue('column1'));
        self::assertEquals([], $record->getColumnValue('column1', []));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Value of "column1" data type: "string" does not match expected: "array"
     */
    public function testGetColumnValueThrowsExceptionIfGivenExpectedTypeDoesNotMatch()
    {
        $record = new Record(10, ['column1' => 'some value'], 'array');
        $record->getColumnValue('column1', null, 'array');
    }

    public function testGetColumnValuePerformsTypeCheckIfSpecified()
    {
        $record = new Record(10, ['column1' => ['key' => 'value']], 'array');
        self::assertEquals(['key' => 'value'], $record->getColumnValue('column1', null, 'array'));
    }

    public function testGetColumnAndUnset()
    {
        $record = new Record(10, ['column1' => 'value1', 'column2' => 'value2']);

        self::assertEquals('value1', $record->getColumnValueAndUnset('column1'));
        self::assertEquals('value2', $record->getColumnValueAndUnset('column2'));
        self::assertNull($record->getColumnValue('column1'));
        self::assertNull($record->getColumnValue('column2'));
    }

    public function testGetColumnAndUnsetReturnsDefaultIfNotSet()
    {
        $record = new Record(10, []);

        self::assertNull($record->getColumnValueAndUnset('column1'));
        self::assertEquals([], $record->getColumnValueAndUnset('column1', []));
    }

    public function testGetColumnAndUnsetThrowsExceptionIfGivenExpectedTypeDoesNotMatchAndDoesNotUnsetValue()
    {
        $record = new Record(10, ['column1' => 'some value']);
        try {
            $record->getColumnValueAndUnset('column1', null, 'array');
        } catch (\RuntimeException $e) {
            self::assertEquals(
                'Value of "column1" data type: "string" does not match expected: "array"',
                $e->getMessage()
            );
            self::assertEquals('some value', $record->getColumnValue('column1'));
            return;
        }

        self::fail('RuntimeException not thrown');
    }

    public function testGetColumnAndUnsetPerformsTypeCheckIfSpecified()
    {
        $record = new Record(10, ['column1' => ['key' => 'value']], 'array');
        self::assertEquals(['key' => 'value'], $record->getColumnValueAndUnset('column1', null, 'array'));
        self::assertNull($record->getColumnValue('column1'));
    }

    public function testColumnExists()
    {
        $record = new Record(10, ['column1' => 'value']);
        self::assertFalse($record->columnExists('column2'));
        self::assertTrue($record->columnExists('column1'));
    }

    public function testAsArray()
    {
        $record = new Record(10, ['column1' => 'value1', 'column2' => 'value2']);
        self::assertEquals(['column1' => 'value1', 'column2' => 'value2'], $record->asArray());
    }

    public function testTransform()
    {
        $record = new Record(10, ['column1' => 2]);
        $record->transform('column1', function ($value) {
            return $value * 2;
        });
        self::assertEquals(4, $record->getColumnValue('column1'));
    }

    public function testRenameColumn()
    {
        $record = new Record(10, ['column1' => 2]);
        $record->renameColumn('column1', 'column2');

        self::assertNull($record->getColumnValue('column1'));
        self::assertEquals(2, $record->getColumnValue('column2'));
    }

    public function testMoveColumnToArrayCreatesArrayWhenItDoesNotExistAndUsesOriginalKey()
    {
        $record = new Record(10, ['column1' => 2]);
        $record->moveColumnToArray('column1', 'attributes');
        self::assertNull($record->getColumnValue('column1'));
        self::assertEquals(['column1' => 2], $record->getColumnValue('attributes'));
    }

    public function testMoveColumnToArrayAddsToArrayIfItExistsAndUsesOriginalKey()
    {
        $record = new Record(10, ['column1' => 2, 'attributes' => ['brand' => 'apple']]);
        $record->moveColumnToArray('column1', 'attributes');
        self::assertNull($record->getColumnValue('column1'));
        self::assertEquals(['brand' => 'apple', 'column1' => 2], $record->getColumnValue('attributes'));
    }

    public function testMoveColumnToArrayWithNewKey()
    {
        $record = new Record(10, ['column1' => 2]);
        $record->moveColumnToArray('column1', 'attributes', 'new-column-name');
        self::assertNull($record->getColumnValue('column1'));
        self::assertEquals(['new-column-name' => 2], $record->getColumnValue('attributes'));
    }

    public function testMoveMultipleColumnsToArray()
    {
        $record = new Record(10, ['column1' => 2, 'column2' => 4]);
        $record->moveMultipleColumnsToArray(['column1', 'column2'], 'attributes');
        self::assertNull($record->getColumnValue('column1'));
        self::assertNull($record->getColumnValue('column2'));
        self::assertEquals(['column1' => 2, 'column2' => 4], $record->getColumnValue('attributes'));
    }

    public function testAddValueToArray()
    {
        $record = new Record(10, ['attributes' => ['brand' => 'apple']]);
        $record->addValueToArray('attributes', 'bear-species', 'spectacled');

        self::assertEquals(['brand' => 'apple', 'bear-species' => 'spectacled'], $record->getColumnValue('attributes'));
    }
}
