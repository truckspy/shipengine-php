<?php declare(strict_types=1);

namespace Model\Address;

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use ShipEngine\Message\ValidationException;
use ShipEngine\Model\Address\Address;
use ShipEngine\Util\ShipEngineSerializer;

/**
 *
 * @covers \ShipEngine\Message\Events\RequestSentEvent
 * @covers \ShipEngine\Message\Events\ShipEngineEvent
 * @covers \ShipEngine\Model\Address\Address
 * @covers \ShipEngine\Util\Assert
 * @covers \ShipEngine\Message\ShipEngineException
 */
final class AddressTest extends TestCase
{
    private static ShipEngineSerializer $serializer;
    private static string $initial_address_validate_params;
    private static Address $successful_address_validate_params;

    public static function setUpBeforeClass(): void
    {
        self::$serializer = new ShipEngineSerializer();
        self::$initial_address_validate_params = json_encode(array(
            'street' =>
                array(
                    0 => 'validate-residential-address',
                ),
            'city_locality' => 'Boston',
            'state_province' => 'MA',
            'postal_code' => '02215',
            'country_code' => 'US',
        ));
        self::$successful_address_validate_params = self::$serializer->deserializeJsonToType(
            self::$initial_address_validate_params,
            Address::class
        );
    }

    /**
     * Test the instantiation via the construct function for the `AddressValidationParams` Type.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Address::class, self::$successful_address_validate_params);
    }

    /**
     * Tests a validation with `error` messages.
     *
     * `Assertions:`
     * - **request_id** is `null`.
     * - **source** is `ShipEngine`.
     * - **type** is `validation`.
     * - **error_code** os `field_value_required`.
     * - **message** is "Invalid address. At least one address line is required.".
     */
    public function testNoAddressLinesValidationError()
    {
        try {
            new Address(
                array(),
                'Boston',
                'MA',
                '02215',
                'US',
            );
            $this->expectException(ValidationException::class);
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('field_value_required', $error['error_code']);
            $this->assertEquals(
                'Invalid address. At least one address line is required.',
                $error['message']
            );
        }
    }

    /**
     * Tests a validation with too many address lines.
     *
     * `Assertions:`
     * - **request_id** is `null`.
     * - **source** is `ShipEngine`.
     * - **type** is `validation`.
     * - **error_code** os `field_value_required`.
     * - **message** is "Invalid address. No more than 3 street lines are allowed.".
     */
    public function testTooManyAddressLinesValidationError()
    {
        try {
            new Address(
                array('4 Jersey St', 'Ste 200', '2nd Floor', 'Clubhouse Level'),
                'Boston',
                'MA',
                '02215',
                'US',
            );
            $this->expectException(ValidationException::class);
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('invalid_field_value', $error['error_code']);
            $this->assertEquals(
                'Invalid address. No more than 3 street lines are allowed.',
                $error['message']
            );
        }
    }

    public function testMissingCityStateAndPostalCode()
    {
        try {
            new Address(
                array('4 Jersey St', 'Ste 200', '2nd Floor'),
                '',
                '',
                '',
                'US',
            );
            $this->expectException(ValidationException::class);
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('field_value_required', $error['error_code']);
            $this->assertEquals(
                'Invalid address. Either the postal code or the city/locality and state/province must be specified.',
                $error['message']
            );
        }
    }

    public function testMissingCityAndPostalCode()
    {
        try {
            new Address(
                array('4 Jersey St', 'Ste 200', '2nd Floor'),
                '',
                'MA',
                '',
                'US',
            );
            $this->expectException(ValidationException::class);
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('field_value_required', $error['error_code']);
            $this->assertEquals(
                'Invalid address. Either the postal code or the city/locality and state/province must be specified.',
                $error['message']
            );
        }
    }

    /**
     * Tests a validation with missing `state`.
     *
     * `Assertions:`
     * - **request_id** is `null`.
     * - **source** is `ShipEngine`.
     * - **type** is `validation`.
     * - **error_code** os `field_value_required`.
     * - **message** is -
     * "Invalid address. Either the postal code or the city/locality and state/province must be specified.".
     */
    public function testMissingStateAndPostalCode()
    {
        try {
            new Address(
                array('4 Jersey St', 'Ste 200', '2nd Floor'),
                'Boston',
                '',
                '',
                'US',
            );
            $this->expectException(ValidationException::class);
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('field_value_required', $error['error_code']);
            $this->assertEquals(
                'Invalid address. Either the postal code or the city/locality and state/province must be specified.',
                $error['message']
            );
        }
    }

    /**
     * Tests a validation with missing `country_code`.
     *
     * `Assertions:`
     * - **request_id** is `null`.
     * - **source** is `ShipEngine`.
     * - **type** is `validation`.
     * - **error_code** os `invalid_field_value`.
     * - **message** is "Invalid address. The country must be specified.".
     */
    public function testMissingCountryCode()
    {
        try {
            new Address(
                array('4 Jersey St', 'Ste 200', '2nd Floor'),
                'Boston',
                'MA',
                '02215',
                '',
            );
            $this->expectException(ValidationException::class);
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('invalid_field_value', $error['error_code']);
            $this->assertEquals(
                'Invalid address. The country must be specified.',
                $error['message']
            );
        }
    }

    /**
     * Tests a validation with invalid `country_code`.
     *
     * `Assertions:`
     * - **request_id** is `null`.
     * - **source** is `ShipEngine`.
     * - **type** is `validation`.
     * - **error_code** os `invalid_field_value`.
     * - **message** is "Invalid address. XX is not a valid country code."
     * (where XX is the value that was specified).
     */
    public function testInvalidCountryCode()
    {
        try {
            new Address(
                array('4 Jersey St', 'Ste 200', '2nd Floor'),
                'Boston',
                'MA',
                '02215',
                'USA',
            );
            $this->expectException(ValidationException::class);
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('invalid_field_value', $error['error_code']);
            $this->assertEquals(
                "Invalid address. USA is not a valid country code.",
                $error['message']
            );
        }
    }

    /**
     * Test the `jsonSerialize()` method on the *AddressValidationParams* type.
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testJsonSerialize(): void
    {
        $json = self::$successful_address_validate_params->jsonSerialize();

        $this->assertNotNull($json);
        $this->assertIsArray($json);
    }
}
