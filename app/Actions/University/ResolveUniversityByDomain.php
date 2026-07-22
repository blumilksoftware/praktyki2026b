<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\University;

class ResolveUniversityByDomain
{
    public function execute(string $email): ?University
    {
        return $this->resolveUniversity($this->extractDomainFromEmail($email));
    }

    private function extractDomainFromEmail(string $email): string
    {
        return substr($email, strrpos($email, "@") + 1);
    }

    private function resolveUniversity(string $domain): ?University
    {
        if ($university = University::where("domain", $domain)->first()) {
            return $university;
        }

        $parts = explode(".", $domain);

        while (count($parts) > 1) {
            array_shift($parts);
            $parentDomain = implode(".", $parts);

            if ($university = University::where("domain", $parentDomain)->first()) {
                return $university;
            }
        }

        return null;
    }
}
