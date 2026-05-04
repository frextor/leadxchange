@extends('layouts.dashboard')

@section('title', $user['first_name'] . ' ' . $user['last_name'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Profile Header -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <!-- Cover -->
        <div class="h-36 bg-gradient-to-r from-teal-500 via-teal-600 to-teal-700"></div>

        <!-- Avatar + Actions -->
        <div class="px-6 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 -mt-12 mb-4">

                <!-- Avatar -->
                <div class="w-24 h-24 bg-gradient-to-br from-teal-500 to-teal-600 rounded-full flex items-center justify-center shadow-lg border-4 border-white flex-shrink-0">
                    <span class="text-white font-bold text-3xl">
                        {{ strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) }}
                    </span>
                </div>

                <!-- Connection action -->
                <div id="connectionAction" class="sm:mb-2">
                    @if ($isOwnProfile)
                        <span class="inline-flex items-center px-5 py-2.5 bg-teal-50 text-teal-700 font-semibold rounded-lg border border-teal-200 text-sm">
                            <i class="fas fa-user mr-2"></i>Your Profile
                        </span>
                    @elseif ($user['connection_status'] === 'accepted')
                        <button disabled class="px-6 py-2.5 bg-gray-100 text-gray-500 font-semibold rounded-lg cursor-not-allowed text-sm">
                            <i class="fas fa-check mr-2"></i>Connected
                        </button>
                    @elseif ($user['connection_status'] === 'pending' && $user['i_am_sender'])
                        <button disabled class="px-6 py-2.5 bg-yellow-100 text-yellow-700 font-semibold rounded-lg cursor-not-allowed text-sm">
                            <i class="fas fa-clock mr-2"></i>Pending
                        </button>
                    @elseif ($user['connection_status'] === 'pending' && $user['i_am_receiver'])
                        <div class="flex gap-2">
                            <button
                                onclick="acceptRequest({{ $user['connection_id'] }})"
                                id="accept-btn-{{ $user['connection_id'] }}"
                                class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition text-sm">
                                <i class="fas fa-check mr-1"></i>Accept
                            </button>
                            <button
                                onclick="rejectRequest({{ $user['connection_id'] }}, {{ $user['id'] }})"
                                id="reject-btn-{{ $user['connection_id'] }}"
                                class="px-5 py-2.5 bg-red-100 hover:bg-red-200 text-red-700 font-semibold rounded-lg transition text-sm">
                                <i class="fas fa-times mr-1"></i>Reject
                            </button>
                        </div>
                    @else
                        <button
                            onclick="connect({{ $user['id'] }})"
                            id="connect-btn"
                            class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition shadow-sm text-sm">
                            <i class="fas fa-user-plus mr-2"></i>Connect
                        </button>
                    @endif
                </div>
            </div>

            <!-- Name & subtitle -->
            <h1 class="text-2xl font-bold text-gray-900">{{ $user['first_name'] }} {{ $user['last_name'] }}</h1>

            @if ($user['company'])
                <p class="text-gray-600 mt-1 text-sm">
                    <i class="fas fa-building mr-1.5 text-teal-500"></i>
                    {{ $user['company']['name'] }}
                    @if ($user['company']['sector'])
                        <span class="text-gray-400 mx-1">·</span>{{ $user['company']['sector'] }}
                    @endif
                </p>
            @endif

            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2">
                @if ($user['city_living'])
                    <p class="text-sm text-gray-500">
                        <i class="fas fa-map-marker-alt mr-1.5 text-teal-500"></i>{{ $user['city_living'] }}
                    </p>
                @endif
                @if ($user['member_since'])
                    <p class="text-sm text-gray-400">
                        <i class="fas fa-calendar mr-1.5"></i>Member since {{ $user['member_since'] }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Personal Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-user text-teal-500"></i>Personal Information
            </h2>
            <div class="space-y-4">

                <div class="flex items-start gap-3">
                    <i class="fas fa-envelope text-gray-400 mt-0.5 w-4 flex-shrink-0"></i>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Email</p>
                        <p class="text-sm font-medium text-gray-700">{{ $user['email'] }}</p>
                    </div>
                </div>

                @if ($user['city_living'])
                    <div class="flex items-start gap-3">
                        <i class="fas fa-map-marker-alt text-gray-400 mt-0.5 w-4 flex-shrink-0"></i>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">City</p>
                            <p class="text-sm font-medium text-gray-700">{{ $user['city_living'] }}</p>
                        </div>
                    </div>
                @endif

                @if ($user['city_birth'])
                    <div class="flex items-start gap-3">
                        <i class="fas fa-baby text-gray-400 mt-0.5 w-4 flex-shrink-0"></i>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">City of birth</p>
                            <p class="text-sm font-medium text-gray-700">{{ $user['city_birth'] }}</p>
                        </div>
                    </div>
                @endif

                @if ($user['gender'])
                    <div class="flex items-start gap-3">
                        <i class="fas fa-venus-mars text-gray-400 mt-0.5 w-4 flex-shrink-0"></i>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Gender</p>
                            <p class="text-sm font-medium text-gray-700 capitalize">{{ $user['gender'] }}</p>
                        </div>
                    </div>
                @endif

                @if ($user['birthday'])
                    <div class="flex items-start gap-3">
                        <i class="fas fa-birthday-cake text-gray-400 mt-0.5 w-4 flex-shrink-0"></i>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Birthday</p>
                            <p class="text-sm font-medium text-gray-700">
                                {{ \Carbon\Carbon::parse($user['birthday'])->format('d F Y') }}
                            </p>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        <!-- Company Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-building text-teal-500"></i>Company
            </h2>

            @if ($user['company'])
                <div class="space-y-4">

                    <div class="flex items-start gap-3">
                        <i class="fas fa-tag text-gray-400 mt-0.5 w-4 flex-shrink-0"></i>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Company name</p>
                            <p class="text-sm font-medium text-gray-700">{{ $user['company']['name'] }}</p>
                        </div>
                    </div>

                    @if ($user['company']['sector'])
                        <div class="flex items-start gap-3">
                            <i class="fas fa-industry text-gray-400 mt-0.5 w-4 flex-shrink-0"></i>
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Sector</p>
                                <p class="text-sm font-medium text-gray-700">{{ $user['company']['sector'] }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($user['company']['website'])
                        <div class="flex items-start gap-3">
                            <i class="fas fa-globe text-gray-400 mt-0.5 w-4 flex-shrink-0"></i>
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Website</p>
                                <a href="{{ $user['company']['website'] }}" target="_blank" rel="noopener noreferrer"
                                   class="text-sm font-medium text-teal-600 hover:underline">
                                    {{ $user['company']['website'] }}
                                </a>
                            </div>
                        </div>
                    @endif

                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-building text-gray-200 text-4xl mb-3"></i>
                    <p class="text-sm text-gray-400">No company information</p>
                </div>
            @endif
        </div>

    </div>
</div>

@if (!$isOwnProfile)
<script>
    async function connect(userId) {
        const btn = document.getElementById('connect-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
        try {
            const res = await fetch('/api/connections', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                credentials: 'same-origin',
                body: JSON.stringify({ receiver_id: userId })
            });
            if (!res.ok) throw new Error();
            btn.innerHTML = '<i class="fas fa-clock mr-2"></i>Pending';
            btn.className = 'px-6 py-2.5 bg-yellow-100 text-yellow-700 font-semibold rounded-lg cursor-not-allowed text-sm';
            showToast('Connection request sent!', 'success');
        } catch {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-user-plus mr-2"></i>Connect';
            showToast('Failed to send request', 'error');
        }
    }

    async function acceptRequest(connectionId) {
        const acceptBtn = document.getElementById(`accept-btn-${connectionId}`);
        const rejectBtn = document.getElementById(`reject-btn-${connectionId}`);
        acceptBtn.disabled = rejectBtn.disabled = true;
        acceptBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Accepting...';
        try {
            const res = await fetch(`/api/connections/${connectionId}/accept`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error();
            document.getElementById('connectionAction').innerHTML = `
                <button disabled class="px-6 py-2.5 bg-gray-100 text-gray-500 font-semibold rounded-lg cursor-not-allowed text-sm">
                    <i class="fas fa-check mr-2"></i>Connected
                </button>`;
            showToast('Connection accepted!', 'success');
        } catch {
            acceptBtn.disabled = rejectBtn.disabled = false;
            acceptBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Accept';
            showToast('Failed to accept request', 'error');
        }
    }

    async function rejectRequest(connectionId, userId) {
        const acceptBtn = document.getElementById(`accept-btn-${connectionId}`);
        const rejectBtn = document.getElementById(`reject-btn-${connectionId}`);
        acceptBtn.disabled = rejectBtn.disabled = true;
        rejectBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Rejecting...';
        try {
            const res = await fetch(`/api/connections/${connectionId}/reject`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error();
            document.getElementById('connectionAction').innerHTML = `
                <button onclick="connect(${userId})" id="connect-btn" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition shadow-sm text-sm">
                    <i class="fas fa-user-plus mr-2"></i>Connect
                </button>`;
            showToast('Request rejected', 'info');
        } catch {
            acceptBtn.disabled = rejectBtn.disabled = false;
            rejectBtn.innerHTML = '<i class="fas fa-times mr-1"></i>Reject';
            showToast('Failed to reject request', 'error');
        }
    }

    function showToast(message, type = 'success') {
        const colors = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-gray-600' };
        const icons  = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle' };
        const toast = document.createElement('div');
        toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-2xl flex items-center space-x-3 transform transition-all`;
        toast.style.transform = 'translateX(400px)';
        toast.innerHTML = `<i class="fas ${icons[type]}"></i><span class="font-medium">${message}</span>`;
        document.getElementById('toastContainer').appendChild(toast);
        setTimeout(() => toast.style.transform = 'translateX(0)', 10);
        setTimeout(() => toast.style.transform = 'translateX(400px)', 3000);
        setTimeout(() => toast.remove(), 3300);
    }
</script>
@endif
@endsection
