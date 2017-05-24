<?php

namespace Jh\ImportTest;

use Jh\Import\Config;
use PHPUnit\Framework\TestCase;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class ConfigTest extends TestCase
{
    public function testGetters()
    {
        $config = new Config('my-import', [
            'source'        => 'MySource',
            'specification' => 'MySpecification',
            'writer'        => 'MyWriter',
            'id_field'      => 'sku',
            'arbitrary_1'   => 'some-value',
            'arbitrary_2'   => 'some-value',
            'indexers'      => ['My\Indexer', 'My\OtherIndexer']
        ]);

        self::assertEquals('my-import', $config->getImportName());
        self::assertEquals('MySource', $config->getSourceService());
        self::assertEquals('MyWriter', $config->getWriterService());
        self::assertEquals('some-value', $config->get('arbitrary_1'));
        self::assertEquals('some-value', $config->get('arbitrary_2'));
        self::assertEquals(['My\Indexer', 'My\OtherIndexer'], $config->get('indexers'));
    }

    public function testReportHandlers()
    {
        $config = new Config('my-import', [
            'report_handlers' => ['My\Handler', 'My\OtherHandler']
        ]);

        self::assertEquals(['My\Handler', 'My\OtherHandler'], $config->getReportHandlers());
    }

    public function testReportHandlersDefault()
    {
        $config = new Config('my-import', []);

        self::assertEquals([], $config->getReportHandlers());
    }
}
