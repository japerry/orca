<?php

namespace Acquia\Orca\Robo\Plugin\Commands;

use Acquia\Orca\Exception\FixtureNotReadyException;
use Robo\Result;

/**
 * Provides the "fixture:reset" command.
 */
class FixtureResetCommand extends CommandBase {

  /**
   * Resets the test fixture to its base state.
   *
   * Restores the last committed state of the build directory from Git and
   * reinstalls Drupal.
   *
   * @command fixture:reset
   * @aliases reset
   *
   * @return \Robo\ResultData|int
   */
  public function execute(array $options = []) {
    if (!file_exists($this->buildPath())) {
      throw new FixtureNotReadyException();
    }

    $confirm = $this->confirm('Are you sure you want to reset the test fixture?');
    if (!$confirm && !$options['no-interaction']) {
      return Result::EXITCODE_USER_CANCEL;
    }

    $git = $this->taskGitStack()
      ->dir($this->buildPath());

    return $this->collectionBuilder()
      ->addTask($git->exec('reset --hard ' . self::BASE_FIXTURE_BRANCH))
      ->addTask($git->exec('clean -fd'))
      ->addTask($this->installDrupal())
      ->run();
  }

}
