<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Builders;

use Brackets\AdminGenerator\Builders\FrontendRulesBuilder;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class FrontendRulesBuilderTest extends TestCase
{
    private readonly FrontendRulesBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new FrontendRulesBuilder();
    }

    public function testRequiredNonBoolNonPasswordColumnAddsRequiredRule(): void
    {
        $rules = $this->builder->build(name: 'title', majorType: 'string', required: true, isForeignKey: false);

        self::assertContains('required', $rules->all());
    }

    public function testRequiredBoolColumnDoesNotAddRequiredRule(): void
    {
        $rules = $this->builder->build(name: 'is_active', majorType: 'bool', required: true, isForeignKey: false);

        self::assertNotContains('required', $rules->all());
    }

    public function testRequiredPasswordColumnDoesNotAddRequiredRule(): void
    {
        $rules = $this->builder->build(name: 'password', majorType: 'string', required: true, isForeignKey: false);

        self::assertNotContains('required', $rules->all());
    }

    public function testNotRequiredColumnDoesNotAddRequiredRule(): void
    {
        $rules = $this->builder->build(name: 'title', majorType: 'string', required: false, isForeignKey: false);

        self::assertNotContains('required', $rules->all());
    }

    public function testEmailNameAddsEmailRule(): void
    {
        $rules = $this->builder->build(name: 'email', majorType: 'string', required: false, isForeignKey: false);

        self::assertContains('email', $rules->all());
    }

    public function testNonEmailNameDoesNotAddEmailRule(): void
    {
        $rules = $this->builder->build(name: 'title', majorType: 'string', required: false, isForeignKey: false);

        self::assertNotContains('email', $rules->all());
    }

    public function testForeignKeySkipsTypeBasedRule(): void
    {
        $rules = $this->builder->build(name: 'category_id', majorType: 'integer', required: false, isForeignKey: true);

        self::assertNotContains('integer', $rules->all());
    }

    public function testIntegerMajorTypeAddsIntegerRule(): void
    {
        $rules = $this->builder->build(name: 'count', majorType: 'integer', required: false, isForeignKey: false);

        self::assertContains('integer', $rules->all());
    }

    public function testFloatMajorTypeAddsNoTypeRule(): void
    {
        $rules = $this->builder->build(name: 'amount', majorType: 'float', required: false, isForeignKey: false);

        self::assertNotContains('numeric', $rules->all());
        self::assertNotContains('integer', $rules->all());
        self::assertNotContains('', $rules->all());
    }

    public function testBoolMajorTypeAddsEmptyString(): void
    {
        $rules = $this->builder->build(name: 'is_active', majorType: 'bool', required: false, isForeignKey: false);

        self::assertContains('', $rules->all());
    }

    public function testDuplicatesAreRemovedViaUnique(): void
    {
        // email name + calling build twice should not accumulate; unique() deduplicates within a single call
        $this->builder->build(name: 'email', majorType: 'string', required: true, isForeignKey: false);
        $rules = $this->builder->build(name: 'email', majorType: 'string', required: true, isForeignKey: false);

        $all = $rules->all();
        self::assertCount(1, array_keys($all, 'required', true));
        self::assertCount(1, array_keys($all, 'email', true));
    }

    public function testOutputIsCollection(): void
    {
        $rules = $this->builder->build(name: 'title', majorType: 'string', required: true, isForeignKey: false);

        self::assertInstanceOf(Collection::class, $rules);
    }

    public function testStringMajorTypeAddsNoTypeRule(): void
    {
        $rules = $this->builder->build(name: 'title', majorType: 'string', required: false, isForeignKey: false);

        self::assertNotContains('integer', $rules->all());
        self::assertNotContains('numeric', $rules->all());
        self::assertNotContains('', $rules->all());
    }
}
