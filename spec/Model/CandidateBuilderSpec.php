<?php

namespace spec\Tworzenieweb\SqlProvisioner\Model;

use Symfony\Component\Finder\SplFileInfo;
use Tworzenieweb\SqlProvisioner\Model\CandidateBuilder;
use PhpSpec\ObjectBehavior;

/**
 * Class CandidateBuilderSpec
 * @package spec\Tworzenieweb\SqlProvisioner\Model
 * @mixin CandidateBuilder
 */
class CandidateBuilderSpec extends ObjectBehavior
{
    function it_should_build_candidate(SplFileInfo $fileInfo)
    {
        $fileInfo->getFilename()->willReturn('001_test.sql');
        $fileInfo->getContents()->willReturn('select * from foo;');

        $candidate = $this->build($fileInfo);
        $candidate->getName()->shouldBe('001_test.sql');
        $candidate->getContent()->shouldBe('select * from foo;');
    }

    function it_should_split_dbdeploy_undo_part(SplFileInfo $fileInfo)
    {
        $fileInfo->getFilename()->willReturn('001_test.sql');
        $fileInfo->getContents()->willReturn(<<<SQL
select * from foo;
--//@UNDO
delete * from foo;
SQL
);
        $candidate = $this->build($fileInfo);
        $candidate->getName()->shouldBe('001_test.sql');
        $candidate->getContent()->shouldBe('select * from foo;');
    }
}
