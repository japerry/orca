<?php

namespace Acquia\Orca\Tests\Console\Command\Fixture;

use Acquia\Orca\Console\Command\Fixture\FixtureBackupCommand;
use Acquia\Orca\Enum\StatusCode;
use Acquia\Orca\Facade\GitFacade;
use Acquia\Orca\Filesystem\FixturePathHandler;
use Acquia\Orca\Tests\Console\Command\CommandTestBase;
use Symfony\Component\Console\Command\Command;

/**
 * @property \Acquia\Orca\Facade\GitFacade|\Prophecy\Prophecy\ObjectProphecy $git
 * @property \Acquia\Orca\Filesystem\FixturePathHandler|\Prophecy\Prophecy\ObjectProphecy $fixture
 */
class FixtureBackupCommandTest extends CommandTestBase {

  protected function setUp(): void {
    $this->fixture = $this->prophesize(FixturePathHandler::class);
    $this->fixture->exists()
      ->willReturn(TRUE);
    $this->fixture->getPath()
      ->willReturn(self::FIXTURE_ROOT);
    $this->git = $this->prophesize(GitFacade::class);
  }

  protected function createCommand(): Command {
    $fixture = $this->fixture->reveal();
    $git = $this->git->reveal();
    return new FixtureBackupCommand($fixture, $git);
  }

  /**
   * @dataProvider providerCommand
   */
  public function testCommand($fixture_exists, $args, $inputs, $remove_called, $status_code, $display) {
    $this->fixture
      ->exists()
      ->shouldBeCalled()
      ->willReturn($fixture_exists);
    $this->git
      ->backupFixtureState()
      ->shouldBeCalledTimes($remove_called);

    $this->executeCommand($args, $inputs);

    $this->assertEquals($display, $this->getDisplay(), 'Displayed correct output.');
    $this->assertEquals($status_code, $this->getStatusCode(), 'Returned correct status code.');
  }

  public function providerCommand() {
    return [
      [FALSE, [], [], 0, StatusCode::ERROR, sprintf("Error: No fixture exists at %s.\n", self::FIXTURE_ROOT)],
      [TRUE, [], ['n'], 0, StatusCode::USER_CANCEL, 'Are you sure you want to overwrite the backup of the test fixture at /var/www/orca-build? '],
      [TRUE, [], ['y'], 1, StatusCode::OK, 'Are you sure you want to overwrite the backup of the test fixture at /var/www/orca-build? '],
      [TRUE, ['-n' => TRUE], [], 0, StatusCode::USER_CANCEL, ''],
      [TRUE, ['-f' => TRUE], [], 1, StatusCode::OK, ''],
      [TRUE, ['-f' => TRUE, '-n' => TRUE], [], 1, StatusCode::OK, ''],
    ];
  }

}
