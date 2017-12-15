<?php
namespace Reverb\Process\Model\Locked\Cron;
use \Reverb\Process\Model\Locked\Abstractclass as Lockedabstract;
use \Reverb\Process\Model\Locked\Cron\Interfaceclass as Lockedcroninterface;
abstract class Abstractclass extends Lockedabstract implements Lockedcroninterface
{
    const ERROR_UNABLE_TO_SECURE_LOCK_FILE = 'Unable to secure a Lock for cron process %s. The cron will not run.';
    const ERROR_EXECUTING_CRON = 'Error executing cron process %s: %s';
    const ERROR_RELEASING_LOCK = 'Error attempting to release the Lock from cron process %s: %s';

    abstract public function executeCron();

    abstract public function getCronCode();

    abstract public function getParallelThreadCount();

    abstract public function attemptLockForThread($thread_number);

    protected $_suppress_failed_lock_attempt_error_messages = false;

    public function attemptLock()
    {
        $thread_count = $this->getParallelThreadCount();

        for($thread_number = 1; $thread_number <= $thread_count; $thread_number++)
        {
            $lock_successful = $this->attemptLockForThread($thread_number);
            if ($lock_successful)
            {
                return $thread_number;
            }
        }

        return false;
    }

    public function attemptCronExecution()
    {
        $this->_logError('attemptCronExecution method');

        $this->getCheckCreateLogFile();
        $thread_lock_number = $this->attemptLock();

        if ($this->attemptLock())
        {
            try
            {
                $this->executeCron();
            }
            catch(\Exception $e)
            {
                $error_message = sprintf(self::ERROR_EXECUTING_CRON, $this->getCronCode(), $e->getMessage());
                $this->_logError($error_message);
            }

            try
            {
                $this->releaseLock();
            }
            catch(\Exception $e)
            {
                $error_message = sprintf(self::ERROR_RELEASING_LOCK, $this->getCronCode(), $e->getMessage());
                $this->_logError($error_message);
            }
        }
        else
        {
            $cron_code = $this->getCronCode();
            $error_message = sprintf(self::ERROR_UNABLE_TO_SECURE_LOCK_FILE, $cron_code);

            if (!$this->_suppress_failed_lock_attempt_error_messages)
            {
                $this->_logError($error_message);
            }
        }
    }
}
