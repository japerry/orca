<?php

namespace Acquia\Orca\Command\Tests;

use Acquia\Orca\Command\StatusCodes;
use Acquia\Orca\Fixture\Facade;
use Acquia\Orca\Tests\Tester;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a command.
 *
 * @property \Acquia\Orca\Fixture\Facade $facade
 * @property \Acquia\Orca\Tests\Tester $tester
 * @property string $fixtureDir
 */
class RunCommand extends Command {

  protected static $defaultName = 'tests:run';

  /**
   * {@inheritdoc}
   */
  public function __construct(Facade $facade, Tester $tester, string $fixture_dir) {
    $this->facade = $facade;
    $this->fixtureDir = $fixture_dir;
    $this->tester = $tester;
    parent::__construct(self::$defaultName);
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setAliases(['test'])
      ->setDescription('Runs automated tests');
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    if (!$this->facade->exists()) {
      $output->writeln([
        "Error: No fixture exists at {$this->facade->rootPath()}.",
        'Hint: Use the "fixture:create" command to create one.',
      ]);
      return StatusCodes::ERROR;
    }
    $this->tester->test();
    return StatusCodes::OK;
  }

}
