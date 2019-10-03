<?php

declare(strict_types=1);

namespace App\Utils;

use DateTime;

/**
 * Class VersionsUtil
 */
class VersionsUtil
{
    /**
     * @var string
     */
    private $commit;

    /**
     * @var string
     */
    private $tag;

    /**
     * @var DateTime
     */
    private $deployTime;

    /**
     * @return string
     */
    public function getCommit(): string
    {
        return $this->commit;
    }

    /**
     * @return float
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @return DateTime
     */
    public function getDeployTime(): DateTime
    {
        return $this->deployTime;
    }

    /**
     * VersionsUtil constructor.
     * commit (last commit hash), tag (current version), deployTime (last build datetime)
     *
     * @param string $commit
     * @param string $tag
     * @param string $deployTime
     * @throws \Exception
     */
    public function __construct(string $commit, string $tag, string $deployTime)
    {
        $this->commit = $commit;
        $this->tag = $tag;
        $this->deployTime = new DateTime($deployTime);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return [
            'git_commit' => $this->getCommit(),
            'git_tag' => $this->getTag(),
            'deploy_time' => $this->getDeployTime(),
        ];
    }
}
