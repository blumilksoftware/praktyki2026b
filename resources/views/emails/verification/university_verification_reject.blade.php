<x-mail::message>
Test mail

    @if($rejectionReason)
        <p>Reason for rejection: {{ $rejectionReason }}</p>
    @endif
</x-mail::message>
