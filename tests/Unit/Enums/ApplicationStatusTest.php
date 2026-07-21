<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\ApplicationStatus;
use Tests\TestCase;

class ApplicationStatusTest extends TestCase
{
    public function testIsTerminalReturnsTrueForAcceptedAndRejected(): void
    {
        $this->assertFalse(ApplicationStatus::Pending->isTerminal());
        $this->assertFalse(ApplicationStatus::Reviewed->isTerminal());
        $this->assertTrue(ApplicationStatus::Accepted->isTerminal());
        $this->assertTrue(ApplicationStatus::Rejected->isTerminal());
    }

    public function testCanTransitionToFromPending(): void
    {
        $this->assertFalse(ApplicationStatus::Pending->canTransitionTo(ApplicationStatus::Pending));
        $this->assertTrue(ApplicationStatus::Pending->canTransitionTo(ApplicationStatus::Reviewed));
        $this->assertTrue(ApplicationStatus::Pending->canTransitionTo(ApplicationStatus::Accepted));
        $this->assertTrue(ApplicationStatus::Pending->canTransitionTo(ApplicationStatus::Rejected));
    }

    public function testCanTransitionToFromReviewed(): void
    {
        $this->assertFalse(ApplicationStatus::Reviewed->canTransitionTo(ApplicationStatus::Pending));
        $this->assertFalse(ApplicationStatus::Reviewed->canTransitionTo(ApplicationStatus::Reviewed));
        $this->assertTrue(ApplicationStatus::Reviewed->canTransitionTo(ApplicationStatus::Accepted));
        $this->assertTrue(ApplicationStatus::Reviewed->canTransitionTo(ApplicationStatus::Rejected));
    }

    public function testCanTransitionToFromAcceptedOrRejectedIsAlwaysFalse(): void
    {
        foreach ([ApplicationStatus::Pending, ApplicationStatus::Reviewed, ApplicationStatus::Accepted, ApplicationStatus::Rejected] as $target) {
            $this->assertFalse(ApplicationStatus::Accepted->canTransitionTo($target));
            $this->assertFalse(ApplicationStatus::Rejected->canTransitionTo($target));
        }
    }
}
