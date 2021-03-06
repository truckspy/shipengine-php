<?php declare(strict_types=1);

namespace Service\Tag;

use PHPUnit\Framework\TestCase;
use ShipEngine\ShipEngine;

/**
 * Tests the methods provided in the `TagTrait`.
 *
 * @covers \ShipEngine\Model\Tag\Tag
 * @covers \ShipEngine\Service\Tag\TagTrait
 * @covers \ShipEngine\Service\Tag\TagService
 * @covers \ShipEngine\Service\AbstractService
 * @covers \ShipEngine\Service\ServiceFactory
 * @covers \ShipEngine\ShipEngine
 * @covers \ShipEngine\ShipEngineClient
 */
final class TagTraitTest extends TestCase
{
    /**
     * @var ShipEngine
     */
    private ShipEngine $shipengine;

    /**
     * Import `simengine/rpc/rpc.json` into *Hoverfly* before class instantiation.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        exec('hoverctl import simengine/rpc/rpc.json');
    }

    /**
     * Delete `simengine/rpc/rpc.json` from *Hoverfly*.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        exec('hoverctl delete --force simengine/rpc/rpc.json');
    }

    /**
     * Pass in an `api-key` the new instance of the *ShipEngine* class.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->shipengine = new ShipEngine('baz');
    }

    /**
     * Test the `createTag()` convenience method on the *TagTrait* successfully creates a new tag using
     * the `tag/create` remote procedure.
     *
     * @return void
     */
    public function testCreateValidTag(): void
    {
        $good_test_value = 'calque_rpc';
        $new_tag = $this->shipengine->createTag($good_test_value);

        $this->assertEquals($new_tag->name, $good_test_value);
    }
}
