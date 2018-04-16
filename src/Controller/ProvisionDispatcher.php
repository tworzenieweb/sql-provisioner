<?php

namespace Tworzenieweb\SqlProvisioner\Controller;

use RuntimeException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tworzenieweb\SqlProvisioner\Check\HasSyntaxCorrectCheck;
use Tworzenieweb\SqlProvisioner\Database\Exception;
use Tworzenieweb\SqlProvisioner\Database\Executor;
use Tworzenieweb\SqlProvisioner\Formatter\Sql;
use Tworzenieweb\SqlProvisioner\Model\Candidate;
use Tworzenieweb\SqlProvisioner\Processor\CandidateProcessor;

/**
 * Class ActionDispatcher
 *
 * @package Tworzenieweb\SqlProvisioner\Controller
 */
class ProvisionDispatcher
{
    const ACTION_DEPLOY = 'DEPLOY';
    const ACTION_SKIP = 'SKIP';
    const ACTION_QUIT = 'QUIT';

    /** @var Executor */
    private $executor;

    /** @var CandidateProcessor */
    private $processor;

    /** @var HasSyntaxCorrectCheck */
    private $syntaxCheck;

    /** @var Sql */
    private $sqlFormatter;

    /** @var SymfonyStyle */
    private $input;

    /** @var int */
    private $startTimestamp;

    /** @var int */
    private $candidateIndexValue = 1;



    /**
     * ActionDispatcher constructor.
     *
     * @param Executor              $executor
     * @param CandidateProcessor    $processor
     * @param HasSyntaxCorrectCheck $check
     * @param Sql                   $sqlFormatter
     */
    public function __construct(
        Executor $executor,
        CandidateProcessor $processor,
        HasSyntaxCorrectCheck $check,
        Sql $sqlFormatter
    ) 
    {
        $this->executor = $executor;
        $this->processor = $processor;
        $this->syntaxCheck = $check;
        $this->sqlFormatter = $sqlFormatter;
        $this->startTimestamp = time();
    }



    /**
     * @param SymfonyStyle $io
     */
    public function setInputOutput(SymfonyStyle $io)
    {
        $this->input = $io;
    }



    /**
     * @param Candidate[] $workingDirectoryCandidates
     * @param int         $queuedCandidatesCount
     */
    public function deploy(array $workingDirectoryCandidates, $queuedCandidatesCount)
    {
        while (!empty($workingDirectoryCandidates)) {
            $candidate = array_shift($workingDirectoryCandidates);

            if ($candidate->isQueued()) {
                $this->executeCandidateScript($candidate, $queuedCandidatesCount);
            }
        }
        $this->input->writeln('<info>All candidates scripts were executed</info>');
        $this->finalizeAndExit();
    }



    /**
     * @param Candidate $candidate
     */
    public function validate(Candidate $candidate)
    {
        if ($this->processor->isValid($candidate)) {
            $candidate->markAsQueued();
            $candidate->setIndex($this->candidateIndexValue++);

            return;
        }

        $this->ignoreCandidateHavingError($candidate);
    }



    public function skipSyntaxCheck()
    {
        $this->input->warning('SQL parsing disabled. This could lead to executing invalid queries.');
        $this->processor->removeCheck($this->syntaxCheck);
    }



    /**
     * @param Candidate $candidate
     */
    private function ignoreCandidateHavingError(Candidate $candidate)
    {
        $candidate->markAsIgnored($this->processor->getLastError());
        $lastErrorMessage = $this->processor->getLastErrorMessage();

        if (null !== $lastErrorMessage) {
            throw new RuntimeException($lastErrorMessage);
        }
    }



    /**
     * @param Candidate $candidate
     * @param int $queuedCandidatesCount
     */
    private function executeCandidateScript(Candidate $candidate, $queuedCandidatesCount)
    {
        $this->input->warning(
            sprintf(
                'PROCESSING [%d/%d] %s',
                $candidate->getIndex(),
                $queuedCandidatesCount,
                $candidate->getName()
            )
        );
        $this->input->text($this->sqlFormatter->format($candidate->getContent()));
        $action = $this->input->choice(
            sprintf('What action to perform for "%s"', $candidate->getName()),
            [self::ACTION_DEPLOY, self::ACTION_SKIP, self::ACTION_QUIT]
        );

        $this->dispatchAction($candidate, $action);
    }



    /**
     * @param Candidate $candidate
     */
    private function deployCandidate(Candidate $candidate)
    {
        try {
            $this->executor->execute($candidate);
            $this->processor->postValidate($candidate);
        } catch (Exception $databaseException) {
            $this->input->error($databaseException->getMessage());
            $this->input->writeln(
                sprintf(
                    "<bg=yellow>%s\n\r%s</>",
                    $databaseException->getPrevious()->getMessage(),
                    $candidate->getContent()
                )
            );
            $this->terminate();
        } catch (RuntimeException $runtimeException) {
            $this->input->error($runtimeException->getMessage());
            $this->terminate();
        }
    }



    public function finalizeAndExit()
    {
        $this->input->text(sprintf('Provisioning ended at %s', date('Y-m-d H:i:s')));
        $this->input->writeln(str_repeat('=', 120));
        $this->input->writeln(
            sprintf(
                '<info>Memory used: %s MB. Total Time of provisioning: %s seconds</info>',
                memory_get_peak_usage(true) / (pow(1024, 2)),
                time() - $this->startTimestamp
            )
        );
        die(0);
    }



    private function terminate()
    {
        $this->input->text(sprintf('Provisioning ended with error at %s', date('Y-m-d H:i:s')));
        die(1);
    }



    /**
     * @param Candidate $candidate
     * @param string $action
     */
    private function dispatchAction(Candidate $candidate, $action)
    {
        switch ($action) {
            case self::ACTION_DEPLOY:
                $this->deployCandidate($candidate);
                break;
            case self::ACTION_QUIT:
                $this->finalizeAndExit();
                break;
        }
    }
}
