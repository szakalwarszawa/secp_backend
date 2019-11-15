<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AppConfig;
use App\Utils\SpecialId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class AppConfigAction
 */
class AppConfigAction extends AbstractController
{
    /**
     * @var SpecialId
     */
    private $specialId;

    /**
     * AppConfig constructor.
     *
     * @param SpecialId $specialId
     */
    public function __construct(SpecialId $specialId)
    {
        $this->specialId = $specialId;
    }

    /**
     * @return AppConfig[]|array
     */
    public function __invoke(): array
    {
        $configs = [];

        $specIdParameters = array_keys($this->getParameter('app.special_id.parameters'));

        foreach ($specIdParameters as $specIdParameter) {
            $appConfig = new AppConfig();
            $appConfig
                ->setConfigKey($specIdParameter)
                ->setConfigValue($this->specialId->getIdForSpecialObjectKey($specIdParameter))
            ;
            $configs[] = $appConfig;
        }
        return $configs;
    }
}
