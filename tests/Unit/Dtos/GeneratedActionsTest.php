<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos;

use Brackets\AdminGenerator\Dtos\GeneratedActions;
use Brackets\AdminGenerator\Enums\GeneratedAction;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

final class GeneratedActionsTest extends TestCase
{
    public function testTheDefaultSetIsEverythingExceptExport(): void
    {
        $actions = GeneratedActions::fromOptions(null, null, false, false);

        self::assertTrue($actions->has(GeneratedAction::Create));
        self::assertTrue($actions->has(GeneratedAction::Show));
        self::assertTrue($actions->has(GeneratedAction::Edit));
        self::assertTrue($actions->has(GeneratedAction::Delete));
        self::assertTrue($actions->has(GeneratedAction::BulkDelete));
        self::assertFalse($actions->has(GeneratedAction::Export));
    }

    public function testOnlyTakesExactlyWhatItIsGiven(): void
    {
        $actions = GeneratedActions::fromOptions('show,export', null, false, false);

        self::assertTrue($actions->has(GeneratedAction::Show));
        self::assertTrue($actions->has(GeneratedAction::Export));
        self::assertFalse($actions->has(GeneratedAction::Create));
        self::assertFalse($actions->has(GeneratedAction::Edit));
        self::assertFalse($actions->has(GeneratedAction::Delete));
        self::assertFalse($actions->has(GeneratedAction::BulkDelete));
    }

    public function testExceptSubtractsFromEverythingIncludingExport(): void
    {
        $actions = GeneratedActions::fromOptions(null, 'delete,bulk-delete', false, false);

        self::assertTrue($actions->has(GeneratedAction::Export));
        self::assertTrue($actions->has(GeneratedAction::Create));
        self::assertFalse($actions->has(GeneratedAction::Delete));
        self::assertFalse($actions->has(GeneratedAction::BulkDelete));
    }

    /**
     * @param array{0: ?string, 1: ?string, 2: bool, 3: bool} $options
     */
    #[DataProvider('getPrecedenceCases')]
    public function testTheDeprecatedFlagsWin(array $options, GeneratedAction $action, bool $expected): void
    {
        $actions = GeneratedActions::fromOptions(...$options);

        self::assertSame($expected, $actions->has($action));
    }

    /**
     * @return iterable<string, array{array{0: ?string, 1: ?string, 2: bool, 3: bool}, GeneratedAction, bool}>
     */
    public static function getPrecedenceCases(): iterable
    {
        $export = GeneratedAction::Export;
        $bulk = GeneratedAction::BulkDelete;

        yield 'only without export' => [['create', null, false, false], $export, false];
        yield 'only without export, -e' => [['create', null, true, false], $export, true];
        yield 'only with export' => [['create,export', null, false, false], $export, true];
        yield 'except without export listed' => [[null, 'create', false, false], $export, true];
        yield 'except with export listed' => [[null, 'export', false, false], $export, false];
        yield 'except with export listed, -e' => [[null, 'export', true, false], $export, true];

        yield 'only with bulk' => [['bulk-delete', null, false, false], $bulk, true];
        yield 'only with bulk, -wb' => [['bulk-delete', null, false, true], $bulk, false];
        yield 'only without bulk' => [['create', null, false, false], $bulk, false];
        yield 'except without bulk listed' => [[null, 'create', false, false], $bulk, true];
        yield 'except without bulk listed, -wb' => [[null, 'create', false, true], $bulk, false];
        yield 'except with bulk listed' => [[null, 'bulk-delete', false, false], $bulk, false];
        yield 'except with bulk listed, -wb' => [[null, 'bulk-delete', false, true], $bulk, false];

        yield 'no options, -e' => [[null, null, true, false], $export, true];
        yield 'no options, -wb' => [[null, null, false, true], $bulk, false];
    }

    public function testOnlyAndExceptCannotBeCombined(): void
    {
        $this->assertThrows(
            static fn (): GeneratedActions => GeneratedActions::fromOptions('show', 'delete', false, false),
            'cannot be combined',
        );
    }

    public function testAnEmptyValueIsRejected(): void
    {
        $this->assertThrows(
            static fn (): GeneratedActions => GeneratedActions::fromOptions('', null, false, false),
            'needs at least one action',
        );
    }

    public function testAnUnknownActionIsRejected(): void
    {
        $this->assertThrows(
            static fn (): GeneratedActions => GeneratedActions::fromOptions('publish', null, false, false),
            'does not know the action "publish"',
        );
    }

    public function testIndexCannotBeListed(): void
    {
        $this->assertThrows(
            static fn (): GeneratedActions => GeneratedActions::fromOptions('index,show', null, false, false),
            'does not know the action "index"',
        );
    }

    public function testWhitespaceAroundNamesIsTolerated(): void
    {
        $actions = GeneratedActions::fromOptions(' show , export ', null, false, false);

        self::assertTrue($actions->has(GeneratedAction::Show));
        self::assertTrue($actions->has(GeneratedAction::Export));
    }

    public function testItRoundTripsThroughItsOptionValue(): void
    {
        $actions = GeneratedActions::fromOptions('show,export', null, false, false);
        $roundTripped = GeneratedActions::fromOptions($actions->toOptionValue(), null, false, false);

        self::assertSame($actions->toOptionValue(), $roundTripped->toOptionValue());
    }

    private function assertThrows(callable $callback, string $expectedMessage): void
    {
        try {
            $callback();

            self::fail(sprintf('Expected an exception containing "%s".', $expectedMessage));
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString($expectedMessage, $exception->getMessage());
        } catch (Throwable $throwable) {
            self::fail(sprintf('Expected an InvalidArgumentException, got %s.', $throwable::class));
        }
    }
}
