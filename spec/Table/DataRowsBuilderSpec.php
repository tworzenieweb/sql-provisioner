<?php

namespace spec\Tworzenieweb\SqlProvisioner\Table;

use Tworzenieweb\SqlProvisioner\Model\Candidate;
use Tworzenieweb\SqlProvisioner\Table\DataRowsBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class DataRowsBuilderSpec
 * @package spec\Tworzenieweb\SqlProvisioner\Table
 * @mixin DataRowsBuilder
 */
class DataRowsBuilderSpec extends ObjectBehavior
{
    function it_should_build_structure(Candidate $c1, Candidate $c2, Candidate $c3)
    {
        $candidates = func_get_args();
        $c1->getName()->willReturn('001_c1.sql');
        $c2->getName()->willReturn('002_c2.sql');
        $c3->getName()->willReturn('003_c3.sql');
        $c1->getStatus()->willReturn(Candidate::STATUS_QUEUED);
        $c2->getStatus()->willReturn(Candidate::STATUS_ALREADY_DEPLOYED);
        $c3->getStatus()->willReturn(Candidate::STATUS_HAS_SYNTAX_ERROR);

        $this->build($candidates, false)->shouldReturn([
            ['001_c1.sql', '<comment>' . Candidate::STATUS_QUEUED .  '</comment>'],
            ['002_c2.sql', Candidate::STATUS_ALREADY_DEPLOYED],
            ['003_c3.sql', '<error>' . Candidate::STATUS_HAS_SYNTAX_ERROR . '</error>']
        ]);
    }
}
