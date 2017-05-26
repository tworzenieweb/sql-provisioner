<?php

namespace spec\Tworzenieweb\SqlProvisioner\Model;

use Tworzenieweb\SqlProvisioner\Model\Candidate;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class CandidateSpec
 * @package spec\Tworzenieweb\SqlProvisioner\Model
 * @mixin Candidate
 */
class CandidateSpec extends ObjectBehavior
{
    const CANDIDATE_SQL = <<<SQL
CREATE TABLE `test` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`));
SQL;

    const CANDIDATE_FILENAME = '001_test_dbdeploy.sql';

    function let()
    {
        $this->beConstructedWith(self::CANDIDATE_FILENAME, self::CANDIDATE_SQL);
    }

    function it_should_have_basic_data_assigned()
    {
        $this->getName()->shouldBe(self::CANDIDATE_FILENAME);
        $this->getContent()->shouldBe(self::CANDIDATE_SQL);
        $this->getNumber()->shouldBe(1);
        $this->shouldBePending();
        $this->shouldNotBeIgnored();
    }

    function it_should_set_status_when_marked_as_queued()
    {
        $this->markAsQueued();
        $this->shouldBeQueued();
        $this->shouldNotBeIgnored();
    }

    function it_should_set_ignored_status_when_marked_as_ignored()
    {
        $this->markAsIgnored(Candidate::STATUS_ALREADY_DEPLOYED);
        $this->shouldBeAlreadyDeployed();
        $this->shouldBeIgnored();

        $this->markAsIgnored(Candidate::STATUS_HAS_SYNTAX_ERROR);
        $this->shouldBeIgnored();
    }
}
