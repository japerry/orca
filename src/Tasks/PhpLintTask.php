<?php

namespace Acquia\Orca\Tasks;

use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Lints PHP files.
 */
class PhpLintTask extends TaskBase {

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    try {
      $this->processRunner->runVendorBinProcess([
        'parallel-lint',
        '-e',
        'inc,install,module,php,profile,test,theme',
        '--exclude',
        'vendor',
        '--colors',
        '--blame',
        $this->getPath(),
      ]);
    }
    catch (ProcessFailedException $e) {
      throw new TaskFailureException();
    }
  }

}
