<?php

namespace spec\Tworzenieweb\SqlProvisioner\Database;

use Prophecy\Argument;
use Tworzenieweb\SqlProvisioner\Database\Connection;
use Tworzenieweb\SqlProvisioner\Database\Exception;
use Tworzenieweb\SqlProvisioner\Database\Executor;
use PhpSpec\ObjectBehavior;
use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * @author Luke Adamczewski
 * @package spec\Tworzenieweb\SqlProvisioner\Database
 * @mixin Executor
 */
class ExecutorSpec extends ObjectBehavior
{
    /**
     * @param Connection $connection
     */
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    function it_should_execute_candidate_query(Connection $connection, Candidate $candidate, \PDO $pdo, \PDOStatement $pdoStatement)
    {
        $connection->getCurrentConnection()->willReturn($pdo);
        $pdo->prepare(Argument::any())->willReturn($pdoStatement);
        $pdoStatement->execute()->shouldBeCalled();

        $this->execute($candidate);
    }

    function it_should_execute_candidate_and_throw_exception_on_fail(Connection $connection, Candidate $candidate, \PDO $pdo, \PDOStatement $pdoStatement)
    {
        $connection->getCurrentConnection()->willReturn($pdo);
        $pdo->prepare(Argument::any())->willReturn($pdoStatement);
        $pdoException = new \PDOException();
        $pdoStatement->execute()->willThrow($pdoException);

        $this->shouldThrow(Exception::class)->during('execute', [$candidate]);
    }
}
