<?php

namespace Tequila\MongoDB\Tests\Command;

use MongoDB\Driver\ReadConcern;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Tequila\MongoDB\Command\AggregateResolver;
use Tequila\MongoDB\ServerInfo;
use Tequila\MongoDB\Tests\Traits\DatabaseAndCollectionNamesTrait;
use Tequila\MongoDB\Tests\Traits\InvalidValuesTrait;
use Tequila\MongoDB\Tests\Traits\ServerInfoTrait;

class AggregateTest extends TestCase
{
    use DatabaseAndCollectionNamesTrait;
    use InvalidValuesTrait;
    use ServerInfoTrait;

    /**
     * @covers AggregateResolver::__construct()
     * @expectedException \Tequila\MongoDB\Exception\InvalidArgumentException
     * @expectedExceptionMessage $pipeline cannot be empty
     */
    public function testConstructorWithEmptyPipeline()
    {
        new AggregateResolver($this->getCollectionName(), []);
    }

    /**
     * @covers AggregateResolver::__construct()
     * @dataProvider getInvalidOptions
     * @expectedException \Tequila\MongoDB\Exception\InvalidArgumentException
     *
     * @param array $invalidOptions
     */
    public function testConstructorWithInvalidOptions(array $invalidOptions)
    {
        new AggregateResolver($this->getCollectionName(), $this->getPipeline(), $invalidOptions);
    }

    /**
     * @covers AggregateResolver::getOptions()
     */
    public function testReadConcernOptionDeletedWhenReadConcernLevelIsNull()
    {
        $command = new AggregateResolver(
            $this->getCollectionName(),
            $this->getPipeline(),
            ['readConcern' => new ReadConcern()]
        );

        $options = $command->getOptions($this->getServerInfoSupportingAllFeatures());
        $this->assertArrayNotHasKey('readConcern', $options);
    }

    /**
     * @covers AggregateResolver::getOptions()
     */
    public function testReadConcernOptionDeletedWhenReadConcernLevelIsMajorityAndPipelineHasOutStage()
    {
        $pipeline = $this->getPipeline();
        $pipeline[] = ['$out' => 'foo'];

        $command = new AggregateResolver(
            $this->getCollectionName(),
            $pipeline,
            ['readConcern' => new ReadConcern(ReadConcern::MAJORITY)]
        );

        $options = $command->getOptions($this->getServerInfoSupportingAllFeatures());
        $this->assertArrayNotHasKey('readConcern', $options);
    }

    /**
     * @return array
     */
    public function getInvalidOptions()
    {
        $options = [];

        // non-existent options
        $options[] = [['foo' => 'bar', 'bar' => 'baz']];

        // Invalid values for "allowDiskUse" option
        foreach ($this->getInvalidBoolValues() as $invalidBoolValue) {
            $options[] = [['allowDiskUse' => $invalidBoolValue]];
        }

        // Invalid values for "collation" option
        foreach ($this->getInvalidDocumentValues() as $invalidDocumentValue) {
            $options[] = [['collation' => $invalidDocumentValue]];
        }

        // Invalid values for "batchSize" option
        foreach ($this->getInvalidIntegerValues() as $invalidIntegerValue) {
            $options[] = [['batchSize' => $invalidIntegerValue]];
        }

        // Case when "batchSize" option is valid, but "useCursor" option is false
        $options[] = [[
            'batchSize' => 100,
            'useCursor' => false,
        ]];

        // Invalid values for "bypassDocumentValidation" option
        foreach ($this->getInvalidBoolValues() as $invalidBoolValue) {
            $options[] = [['bypassDocumentValidation' => $invalidBoolValue]];
        }

        // Invalid values for "maxTimeMS" option
        foreach ($this->getInvalidIntegerValues() as $invalidIntegerValue) {
            $options[] = [['maxTimeMS' => $invalidIntegerValue]];
        }

        // Invalid values for "readConcern" option
        foreach ($this->getInvalidReadConcernValues() as $invalidReadConcernValue) {
            $options[] = [['readConcern' => $invalidReadConcernValue]];
        }

        // Invalid values for "readPreference" option
        foreach ($this->getInvalidReadPreferenceValues() as $invalidReadPreferenceValue) {
            $options[] = [['readPreference' => $invalidReadPreferenceValue]];
        }

        // Invalid values for "typeMap" option
        foreach ($this->getInvalidArrayValues() as $invalidArrayValue) {
            $options[] = [['typeMap' => $invalidArrayValue]];
        }

        // Invalid values for "typeMap" option
        foreach ($this->getInvalidTypeMapValues() as $invalidTypeMapValue) {
            $options[] = [['typeMap' => $invalidTypeMapValue]];
        }

        // Case when "typeMap" option is valid, but option "useCursor" is false
        $options[] = [[
            'typeMap' => ['array' => 'array', 'document' => 'array', 'root' => 'array'],
            'useCursor' => false,
        ]];

        // Invalid values for "useCursor" option
        foreach ($this->getInvalidBoolValues() as $invalidBoolValue) {
            $options[] = [['useCursor' => $invalidBoolValue]];
        }

        // Invalid values for "writeConcern" option
        foreach ($this->getInvalidWriteConcernValues() as $invalidWriteConcernValue) {
            $options[] = [['writeConcern' => $invalidWriteConcernValue]];
        }

        return $options;
    }

    /**
     * @return array aggregate valid pipeline example
     */
    private function getPipeline()
    {
        return [
            [
                '$group' => [
                    '_id' => null,
                    'count' => ['$sum' => 1],
                ]
            ]
        ];
    }

    private function getServerInfoSupportingAllFeatures()
    {
        $serverInfoProphecy = $this->prophesize(ServerInfo::class);
        $serverInfoProphecy->supportsFeature(Argument::type('integer'))->willReturn(true);

        return $serverInfoProphecy->reveal();
    }
}