<div class="gp-col-left">

    <div class="gp-sidebar">

        <a href="#" class="gp-side-item active">
            <i class="bi bi-house-door-fill"></i>
            <span>Community Home</span>
        </a>

        <a href="#" class="gp-side-item">
            <i class="bi bi-info-circle-fill"></i>
            <span>About</span>
        </a>

        <hr>

        @if($isAdmin)
        <div class="gp-side-title">
            Admin Tools
        </div>

        <a href="#" class="gp-side-item">
            <i class="bi bi-person-check-fill"></i>
            <span>Member Requests</span>
        </a>

        <a href="#" class="gp-side-item">
            <i class="bi bi-patch-check-fill"></i>
            <span>Badge Requests</span>
        </a>

        <a href="#" class="gp-side-item">
            <i class="bi bi-question-circle-fill"></i>
            <span>Membership Questions</span>
        </a>

        <a href="#" class="gp-side-item">
            <i class="bi bi-file-earmark-post-fill"></i>
            <span>Pending Posts</span>
        </a>

        <hr>
        @endif

        <div class="gp-side-title">
            About
        </div>

        <div class="gp-side-info">

            @if($group->description)
                <p>{{ $group->description }}</p>
            @endif

            <div>
                <i class="bi bi-{{ $group->privacy=='private' ? 'lock-fill':'globe' }}"></i>

                {{ ucfirst($group->privacy) }} Group
            </div>

            <div>
                <i class="bi bi-people-fill"></i>

                {{ number_format($group->members_count) }}
                Members
            </div>

            @if($group->created_at)
            <div>
                <i class="bi bi-calendar-event-fill"></i>

                Created {{ $group->created_at->format('F Y') }}
            </div>
            @endif

        </div>

    </div>

</div>