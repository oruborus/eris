<?php

declare(strict_types=1);

namespace Test\Support;

use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TextUI\TestRunner;
use ReflectionClass;
use RuntimeException;
use SimpleXMLElement;

use function array_filter;
use function array_values;
use function in_array;
use function ob_start;
use function ob_get_clean;
use function putenv;
use function realpath;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress InternalMethod
 */
class EndToEndTestCase extends TestCase
{
    protected ClassLoader $loader;

    /**
     * @var array<string, TestSuite> $testSuites
     */
    protected static array $testSuites = [];

    /**
     * @var array<string, string> $environment
     */
    protected array $environment = [];

    public function setUp(): void
    {
        parent::setUp();

        /** 
         * @var ClassLoader
         */
        $this->loader = require __DIR__ . '/../../vendor/autoload.php';
    }

    protected function setEnvironmentVariable(string $name, string $value): self
    {
        $this->environment[$name] = $value;

        return $this;
    }

    /**
     * @internal
     */
    protected function endToEndSetUp(): void
    {
        foreach ($this->environment as $name => $value) {
            putenv("{$name}={$value}");
        }
    }

    /**
     * @internal
     */
    protected function endToEndTearDown(): void
    {
        foreach ($this->environment as $name => $_) {
            putenv("{$name}");
        }
    }

    /**
     * @param class-string<TestCase> $className
     */
    protected function runTestClass(string $className): TestResult
    {
        return $this->runTestsFromTestClass($className);
    }

    /**
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     *
     * @param class-string<TestCase> $className
     */
    protected function runTestsFromTestClass(string $className, string ...$methodNames): TestResult
    {
        $this->endToEndSetUp();

        $log = $this->prepareLogging();

        $arguments = [
            'extensions'   => [],
            'junitLogfile' => $log,
        ];

        $file = $this->loader->findFile($className);

        if (!$file) {
            throw new RuntimeException("{$className} could not be found");
        }

        $file = realpath($file);

        $suite = $this->retrieveTestSuite($file);

        $suite = $this->filterTestMethods($suite, $methodNames);

        $assertionCountBeforeRun = Assert::getCount();

        ob_start();
        $testResult = (new TestRunner())->run($suite, $arguments, [], false);
        $output = ob_get_clean();

        $logContent = $this->finalizeLogging($log);
        $testLog = new SimpleXMLElement($logContent);

        /**
         * @psalm-suppress MixedPropertyFetch
         * @psalm-suppress MixedMethodCall
         */
        $assertionCountAfterRun = (int) $testLog->testsuite->attributes()->assertions;

        $this->setAssertionCount($assertionCountBeforeRun + $assertionCountAfterRun);

        $this->endToEndTearDown();

        return new TestResult($testResult, $testLog, $output);
    }

    /**
     * @return resource
     */
    private function prepareLogging()
    {
        return \fopen('php://memory', 'r+') ?: throw new RuntimeException('Logging could not be initialized');
    }

    /**
     * @param resource $log
     */
    private function finalizeLogging($log): string
    {
        \rewind($log);
        $content = \stream_get_contents($log) ?: throw new RuntimeException('Logging could not be finalized');
        \fclose($log);
        unset($log);

        return $content;
    }

    private function setAssertionCount(int $count): void
    {
        $reflectedClass = new ReflectionClass(Assert::class);
        $reflectedProperty = $reflectedClass->getProperty('count');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($count);
    }

    /**
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     *
     * @throws RuntimeException
     */
    private function retrieveTestSuite(string $filename): TestSuite
    {
        if (isset(static::$testSuites[$filename])) {
            return clone static::$testSuites[$filename];
        }

        static::$testSuites[$filename] = (new TestRunner())->getTest($filename) ??
            throw new RuntimeException('Test suite could not be retrieved');

        return clone static::$testSuites[$filename];
    }

    /**
     * @psalm-suppress InternalMethod
     */
    private function filterTestMethods(TestSuite $suite, array $methods): TestSuite
    {
        if (empty($methods)) {
            return $suite;
        }

        /**
         * @var TestCase[] $tests
         */
        $tests = $suite->tests();

        $tests = array_values(
            array_filter(
                $tests,
                fn (TestCase $element): bool => in_array($element->getName(false), $methods)
            )
        );

        $suite->setTests($tests);

        return $suite;
    }
}
