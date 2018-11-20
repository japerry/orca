<?php

namespace Acquia\Orca\Tests\Fixture;

use Acquia\Orca\Fixture\Creator;
use Acquia\Orca\Fixture\Remover;
use Acquia\Orca\Fixture\Fixture;
use Acquia\Orca\Fixture\ProductData;
use Acquia\Orca\TestRunner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers \Acquia\Orca\Fixture\Fixture
 *
 * @property \Prophecy\Prophecy\ObjectProphecy $filesystem
 * @property \Prophecy\Prophecy\ObjectProphecy $productData
 * @property string $rootPath
 */
class FixtureTest extends TestCase {

  protected function setUp() {
    $this->filesystem = $this->prophesize(Filesystem::class);
    $this->productData = $this->prophesize(ProductData::class);
    $this->rootPath = '/var/www/orca-build';
  }

  public function testConstruction() {
    $fixture = $this->createFixture();

    $this->assertTrue($fixture instanceof Fixture, 'Instantiated class.');
  }

  /**
   * @dataProvider providerExists
   */
  public function testExists($root_path, $exists) {
    $this->rootPath = $root_path;
    $this->filesystem
      ->exists($root_path)
      ->willReturn($exists);
    $fixture = $this->createFixture();

    $return = $fixture->exists();

    $this->filesystem
      ->exists($root_path)
      ->shouldHaveBeenCalledTimes(1);
    $this->assertEquals($exists, $return, 'Returned correct value.');
  }

  public function providerExists() {
    return [
      ['/fixture-exists', TRUE],
      ['/no-fixture-there', FALSE],
    ];
  }

  /**
   * @dataProvider providerPathResolution
   */
  public function testPathResolution($root_path, $docroot_path, $product_module_path) {
    $this->rootPath = $root_path;
    $fixture = $this->createFixture();
    $sub_path = '/some/sub-path';

    $this->assertEquals($root_path, $fixture->rootPath(), 'Resolved root path.');
    $this->assertEquals("{$root_path}/{$sub_path}", $fixture->rootPath($sub_path), 'Resolved root path with sub-path.');
    $this->assertEquals($docroot_path, $fixture->docrootPath(), 'Resolved docroot path.');
    $this->assertEquals("{$docroot_path}/{$sub_path}", $fixture->docrootPath($sub_path), 'Resolved docroot path with sub-path.');
    $this->assertEquals($product_module_path, $fixture->productModuleInstallPath(), 'Resolved product module path.');
    $this->assertEquals("{$product_module_path}/{$sub_path}", $fixture->productModuleInstallPath($sub_path), 'Resolved docroot path with sub-path.');
  }

  public function providerPathResolution() {
    return [
      ['/var/www/orca-build', '/var/www/orca-build/docroot', '/var/www/orca-build/docroot/modules/contrib/acquia'],
      ['/tmp/test', '/tmp/test/docroot', '/tmp/test/docroot/modules/contrib/acquia'],
    ];
  }

  protected function createFixture(): Fixture {
    /** @var \Symfony\Component\Filesystem\Filesystem $filesystem */
    $filesystem = $this->filesystem->reveal();
    /** @var \Acquia\Orca\Fixture\ProductData $product_data */
    $product_data = $this->productData->reveal();
    return new Fixture($filesystem, $product_data, $this->rootPath);
  }

}