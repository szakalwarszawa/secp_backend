<?php

declare(strict_types=1);

namespace App\Validator\Rules;

interface RuleInterface
{
    /**
     * Get rules of status change
     * ex.
     * "ROLE_USER": [
     *   "WORK-SCHEDULE-STATUS-OWNER-ACCEPT"
     * ],
     * "ROLE_HR": [
     *  "WORK-SCHEDULE-STATUS-OWNER-ACCEPT",
     *  "WORK-SCHEDULE-STATUS-MANAGER-ACCEPT",
     *  "WORK-SCHEDULE-STATUS-HR-ACCEPT"
     *  ]
     */
    public function getRules(): ?string;

    /**
     * Set rules
     *
     * @param string $rules - json array
     *
     * @return StatusRuleInterface
     */
    public function setRules(string $rules);
}
