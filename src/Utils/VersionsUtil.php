<?php

namespace App\Utils;

use Symfony\Component\Dotenv\Dotenv;

/**
 * Service VersionsUtil
 * @package App\Utils
 */
class VersionsUtil
{
    /**
     * @var
     */
    private $commit;
    /**
     * @var
     */
    private $tag;
    /**
     * @var
     */
    private $deploy;

    /**
     * @return mixed
     */
    public function getCommit()
    {
        return $this->commit;
    }

    /**
     * @param mixed $commit
     */
    public function setCommit($commit): void
    {
        $this->commit = $commit;
    }

    /**
     * @return mixed
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param mixed $tag
     */
    public function setTag($tag): void
    {
        $this->tag = $tag;
    }

    /**
     * @return mixed
     */
    public function getDeploy()
    {
        return $this->deploy;
    }

    /**
     * @param mixed $deploy
     */
    public function setDeploy($deploy): void
    {
        $this->deploy = $deploy;
    }

    /**
     * VersionsUtil constructor.
     * @param $commit
     * @param $tag
     * @param $deploy
     */
    public function __construct($commit, $tag, $deploy)
    {
        $this->commit = $commit;
        $this->tag = $tag;
        $this->deploy = $deploy;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $dotEnv = new Dotenv();
        $dotEnv->load(getcwd() . '/.env');

        $versions = array(
            'git_commit' => $this->getCommit(),
            'git_tag' => $this->getTag(),
            'deploy_time' =>$this->getDeploy()
        );

        return $versions;
    }
}