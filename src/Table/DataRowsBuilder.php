<?php

namespace Tworzenieweb\SqlProvisioner\Table;

use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * Class DataRowsBuilder
 * @package Tworzenieweb\SqlProvisioner\Table
 */
class DataRowsBuilder
{
    const TABLE_HEADERS = ['FILENAME', 'STATUS'];

    /**
     * @param Candidate $candidate
     * @param boolean   $skipAlreadyDeployed
     * @return array|null
     */
    public static function buildCandidateRow(Candidate $candidate, $skipAlreadyDeployed)
    {
        $status = $candidate->getStatus();

        if ($skipAlreadyDeployed && $status === Candidate::STATUS_ALREADY_DEPLOYED) {
            return null;
        }

        return [$candidate->getName(), self::normalizeStatus($status)];
    }



    /**
     * @param $status
     * @return string
     */
    private static function normalizeStatus($status)
    {
        switch ($status) {
            case Candidate::STATUS_QUEUED:
                $status = sprintf('<comment>%s</comment>', $status);
                break;
            case Candidate::STATUS_HAS_SYNTAX_ERROR:
                $status = sprintf('<error>%s</error>', $status);
                break;
        }

        return $status;
    }



    /**
     * @param Candidate[] $candidates
     * @param boolean     $skipAlreadyDeployed
     * @return array
     */
    public function build(array $candidates, $skipAlreadyDeployed)
    {
        return array_filter(array_map(
            function(Candidate $candidate) use ($skipAlreadyDeployed) {
                return DataRowsBuilder::buildCandidateRow($candidate, $skipAlreadyDeployed);
            },
            $candidates
        ));
    }
}
