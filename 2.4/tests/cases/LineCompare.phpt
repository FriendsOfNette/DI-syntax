<?php declare(strict_types = 1);

use Nette\DI\Compiler;
use Nette\DI\ContainerLoader;
use Tester\Assert;
use Tester\AssertException;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

class CompareLoader extends ContainerLoader
{
	function __construct()
	{
		parent::__construct(TEMP_DIR, true);
	}

	public function generate($class, $generator)
	{
		return parent::generate($class, $generator);
	}
}

class LineCompareTest extends TestCase
{

	public function testCompareLines()
	{
		$loader = new CompareLoader();

		// Create container based on extension
		$container1 = $loader->generate('Container', function (Compiler $compiler) {
			$compiler->addExtension('test', new SyntaxExtension());
		}, '1');

		// Create container based on neon config
		$container2 = $loader->generate('Container', function (Compiler $compiler) {
			$compiler->loadConfig(__DIR__ . '/../../syntax/neon/syntax.neon');
		}, '2');

		Assert::type('array', $container1);
		Assert::type('array', $container2);

		$code1 = $container1[0];
		$code2 = $container2[0];

		$code1 = preg_replace('#^(.*)public function#sU', null, $code1);
		$code1 = preg_replace('#(public function initialize.*})#sU', null, $code1);
		$code2 = preg_replace('#^(.*)public function#sU', null, $code2);
		$code2 = preg_replace('#(public function initialize.*})#sU', null, $code2);

		//file_put_contents(TEMP_DIR . '/Container1.php', $code1);
		//file_put_contents(TEMP_DIR . '/Container2.php', $code2);

		$this->assertLines(explode(PHP_EOL, $code1), explode(PHP_EOL, $code2));
	}

	/**
	 * @param array $code1
	 * @param array $code2
	 * @throws AssertException
	 */
	protected function assertLines(array $code1, array $code2)
	{
		if (count($code1) !== count($code2)) {
			Assert::fail('Containers have not same number of lines.');
		}

		for ($i = 0; $i < count($code1); $i++) {
			$line1 = $code1[$i];
			$line2 = $code2[$i];

			if (strcmp($line1, $line2) !== 0) {
				Assert::fail("Lines $i are not equal", $line1, $line2);
			}
		}
	}

}

(new LineCompareTest())->run();
