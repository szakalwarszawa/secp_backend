<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Serializer\GroupsRestrictions\GroupRestriction;
use stdClass;

/**
 * Class UserContextBuilder
 */
final class UserContextBuilder implements SerializerContextBuilderInterface
{
    /**
     * @var SerializerContextBuilderInterface
     */
    private $decorated;

    /**
     * @var stdClass
     */
    private $context = [];

    /**
     * @var GroupRestriction
     */
    private $groupRestriction;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        SerializerContextBuilderInterface $decorated,
        GroupRestriction $groupRestriction
    ) {
        $this->decorated = $decorated;
        $this->groupRestriction = $groupRestriction;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {

        $this->context = (object) $this
            ->decorated
            ->createFromRequest($request, $normalization, $extractedAttributes)
        ;


        $this
            ->groupRestriction
            ->addIOGroupRestrictions($this->context, $normalization)
        ;

        return (array) $this->context;
    }
}
