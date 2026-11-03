<?php

namespace App\Services;

use App\Models\Candidate;
use Illuminate\Validation\ValidationException;

class CandidateConsentService
{
    public function canDiscover(Candidate $candidate): bool
    {
        return filled($candidate->headline)
            && filled($candidate->coarse_location)
            && $candidate->skills()->exists();
    }

    public function setDiscoverable(Candidate $candidate, bool $discoverable): bool
    {
        if ($discoverable && ! $this->canDiscover($candidate)) {
            throw ValidationException::withMessages([
                'discoverable' => 'Add a headline, a coarse location and at least one skill before opting in.',
            ]);
        }

        if ($candidate->discoverable === $discoverable) {
            return false;
        }

        $candidate->update(['discoverable' => $discoverable]);
        $candidate->consentEvents()->create([
            'purpose' => 'discovery',
            'action' => $discoverable ? 'granted' : 'revoked',
            'policy_version' => config('talent.consent_policy_version'),
            'created_at' => now(),
        ]);

        return true;
    }

    public function withdrawIfIncomplete(Candidate $candidate): bool
    {
        if (! $candidate->discoverable || $this->canDiscover($candidate)) {
            return false;
        }

        return $this->setDiscoverable($candidate, false);
    }
}
