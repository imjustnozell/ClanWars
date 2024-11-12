<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\utils;

class KickQueueManager
{

    private $kickRequests = [];


    public function addKickRequest(string $playerName, callable $onKick): void
    {

        if (!isset($this->kickRequests[$playerName])) {
            $this->kickRequests[$playerName] = [
                'voters' => [],
                'onKick' => $onKick
            ];
        }
    }

    public function addVote(string $playerName, string $adminName): bool
    {

        if (isset($this->kickRequests[$playerName])) {

            if (!in_array($adminName, $this->kickRequests[$playerName]['voters'])) {

                $this->kickRequests[$playerName]['voters'][] = $adminName;


                if (count($this->kickRequests[$playerName]['voters']) >= ClanUtils::Votes) {

                    $this->kickRequests[$playerName]['onKick']();

                    unset($this->kickRequests[$playerName]);
                    return true;
                }
            }
        }

        return false;
    }

    public function isVotingActive(string $playerName): bool
    {
        return isset($this->kickRequests[$playerName]);
    }

    public function hasVoted(string $playerName, string $adminName): bool
    {
        return isset($this->kickRequests[$playerName]) && in_array($adminName, $this->kickRequests[$playerName]['voters']);
    }

    public function getRemainingVotes(string $playerName): int
    {
        return isset($this->kickRequests[$playerName]) ? ClanUtils::Votes - count($this->kickRequests[$playerName]['voters']) : 3;
    }

    public function getActiveVotes(): array
    {
        return $this->kickRequests;
    }

    public function removeVote(string $playerName): void
    {
        unset($this->kickRequests[$playerName]);
    }
}
