@extends('layouts.app')

@section('title', 'Network - Find Members')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Find Members</h1>
        <p class="text-gray-600 mt-1">Connect with professionals in your network</p>
    </div>

    <!-- Search Bar -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="relative">
            <input
                type="text"
                id="searchInput"
                placeholder="Search by name (min 3 characters)..."
                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
        </div>
    </div>

    <!-- Users Container -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden min-h-[400px]">

        <!-- Loading -->
        <div id="loading" class="p-12 text-center">
            <div class="w-12 h-12 border-4 border-teal-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-500">Loading members...</p>
        </div>

        <!-- Users List -->
        <div id="usersList" class="divide-y divide-gray-100" style="display: none;">
            <!-- Users will be inserted here -->
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="p-12 text-center" style="display: none;">
            <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No members found</h3>
            <p class="text-gray-500">Try a different search or check back later</p>
        </div>

        <!-- Load More -->
        <div id="loadMore" class="p-6 text-center border-t" style="display: none;">
            <button
                onclick="loadMore()"
                class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg">
                <i class="fas fa-chevron-down mr-2"></i>Load More
            </button>
            <p class="text-sm text-gray-500 mt-3">
                <span id="loadedCount">0</span> of <span id="totalCount">0</span> members
            </p>
        </div>

    </div>
</div>

<script>
    console.log('🚀 Members page JavaScript loaded');

    let currentPage = 1;
    let totalPages = 1;
    let isLoading = false;
    let searchQuery = '';

    // Elements
    const loading = document.getElementById('loading');
    const usersList = document.getElementById('usersList');
    const emptyState = document.getElementById('emptyState');
    const loadMoreBtn = document.getElementById('loadMore');
    const searchInput = document.getElementById('searchInput');

    // Load users on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('📄 DOM loaded, fetching users...');
        fetchUsers(1);
    });

    // Search with debounce
    let searchTimeout;
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();

        clearTimeout(searchTimeout);

        if (query.length === 0) {
            searchQuery = '';
            resetAndFetch();
        } else if (query.length >= 3) {
            searchTimeout = setTimeout(() => {
                searchQuery = query;
                resetAndFetch();
            }, 500);
        }
    });

    function resetAndFetch() {
        currentPage = 1;
        usersList.innerHTML = '';
        fetchUsers(1);
    }

    // Fetch users from API
    async function fetchUsers(page) {
        if (isLoading) return;

        isLoading = true;
        console.log(`📡 Fetching users page ${page}...`);

        // Show loading
        if (page === 1) {
            loading.style.display = 'block';
            usersList.style.display = 'none';
            emptyState.style.display = 'none';
            loadMoreBtn.style.display = 'none';
        }

        try {
            let url = `/api/users?page=${page}`;
            if (searchQuery) {
                url += `&search=${encodeURIComponent(searchQuery)}`;
            }

            console.log(`🔗 Calling: ${url}`);

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            });

            console.log(`📥 Response status: ${response.status}`);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            console.log('✅ Data received:', data);

            // Hide loading
            loading.style.display = 'none';

            // Check if we have users
            if (!data.users || data.users.length === 0) {
                if (page === 1) {
                    usersList.style.display = 'none';
                    emptyState.style.display = 'block';
                    console.log('❌ No users found');
                }
                isLoading = false;
                return;
            }

            console.log(`✅ Found ${data.users.length} users`);

            // Show users list
            usersList.style.display = 'block';
            emptyState.style.display = 'none';

            // Render users
            data.users.forEach(user => {
                usersList.appendChild(createUserCard(user));
            });

            // Update pagination
            currentPage = data.current_page;
            totalPages = data.last_page;

            document.getElementById('loadedCount').textContent = usersList.children.length;
            document.getElementById('totalCount').textContent = data.total;

            // Show/hide load more button
            if (data.has_more_pages) {
                loadMoreBtn.style.display = 'block';
            } else {
                loadMoreBtn.style.display = 'none';
            }

        } catch (error) {
            console.error('❌ Error fetching users:', error);
            loading.style.display = 'none';

            if (page === 1) {
                emptyState.style.display = 'block';
                emptyState.querySelector('p').textContent = 'Failed to load members. Please refresh the page.';
            }
        } finally {
            isLoading = false;
        }
    }

    // Load more users
    function loadMore() {
        if (currentPage < totalPages) {
            fetchUsers(currentPage + 1);
        }
    }

    // Create user card
    function createUserCard(user) {
        const div = document.createElement('div');
        div.className = 'p-6 hover:bg-gray-50 transition';

        const initials = ((user.first_name || '?').charAt(0) + (user.last_name || '?').charAt(0)).toUpperCase();
        const isConnected = user.connection_status === 'accepted';
        const isPendingAndISent = user.connection_status === 'pending' && user.i_am_sender;
        const isPendingAndIReceived = user.connection_status === 'pending' && user.i_am_receiver;

        div.innerHTML = `
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 flex-1">
                <!-- Avatar -->
                <div class="w-16 h-16 bg-gradient-to-br from-teal-500 to-teal-600 rounded-full flex items-center justify-center shadow-md flex-shrink-0">
                    <span class="text-white font-bold text-xl">${initials}</span>
                </div>
                
                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <a href="/profile/${user.id}" class="text-lg font-bold text-gray-900 hover:text-teal-600 transition truncate block">
                        ${user.first_name} ${user.last_name}
                    </a>
                    <p class="text-sm text-gray-600 truncate">${user.email}</p>
                    ${user.company ? `
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-building mr-1"></i>${user.company.name}
                            ${user.company.sector ? ` • ${user.company.sector}` : ''}
                        </p>
                    ` : ''}
                    ${user.city ? `
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-map-marker-alt mr-1"></i>${user.city}
                        </p>
                    ` : ''}
                </div>
            </div>
            
            <!-- Button -->
            <div class="flex-shrink-0">
                ${isConnected ? `
                    <button disabled class="px-6 py-2.5 bg-gray-100 text-gray-500 font-semibold rounded-lg cursor-not-allowed">
                        <i class="fas fa-check mr-2"></i>Connected
                    </button>
                ` : isPendingAndISent ? `
                    <button disabled class="px-6 py-2.5 bg-yellow-100 text-yellow-700 font-semibold rounded-lg cursor-not-allowed">
                        <i class="fas fa-clock mr-2"></i>Pending
                    </button>
                ` : isPendingAndIReceived ? `
                    <div class="flex gap-2">
                        <button 
                            onclick="acceptRequest(${user.connection_id})"
                            id="accept-btn-${user.connection_id}"
                            class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition"
                        >
                            <i class="fas fa-check mr-1"></i>Accept
                        </button>
                        <button 
                            onclick="rejectRequest(${user.connection_id})"
                            id="reject-btn-${user.connection_id}"
                            class="px-4 py-2.5 bg-red-100 hover:bg-red-200 text-red-700 font-semibold rounded-lg transition"
                        >
                            <i class="fas fa-times mr-1"></i>Reject
                        </button>
                    </div>
                ` : `
                    <button 
                        onclick="connect(${user.id})"
                        id="btn-${user.id}"
                        class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition shadow-sm"
                    >
                        <i class="fas fa-user-plus mr-2"></i>Connect
                    </button>
                `}
            </div>
        </div>
    `;

        return div;
    }

    // Send connection request
    async function connect(userId) {
        const btn = document.getElementById(`btn-${userId}`);
        if (!btn) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';

        try {
            const response = await fetch('/api/connections', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Authorization': 'Bearer ' + window.API_TOKEN
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    receiver_id: userId
                })
            });

            if (!response.ok) throw new Error('Failed');

            btn.innerHTML = '<i class="fas fa-clock mr-2"></i>Pending';
            btn.className = 'px-6 py-2.5 bg-yellow-100 text-yellow-700 font-semibold rounded-lg cursor-not-allowed';

            showToast('Connection request sent! 🎉', 'success');

        } catch (error) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-user-plus mr-2"></i>Connect';
            showToast('Failed to send request', 'error');
        }
    }

    // Accept connection request
    async function acceptRequest(connectionId) {
        const acceptBtn = document.getElementById(`accept-btn-${connectionId}`);
        const rejectBtn = document.getElementById(`reject-btn-${connectionId}`);

        if (!acceptBtn) return;

        acceptBtn.disabled = true;
        rejectBtn.disabled = true;
        acceptBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Accepting...';

        try {
            const response = await fetch(`/api/connections/${connectionId}/accept`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Authorization': 'Bearer ' + window.API_TOKEN
                },
                credentials: 'same-origin'
            });

            if (!response.ok) throw new Error('Failed');

            // Replace both buttons with "Connected"
            const container = acceptBtn.parentElement;
            container.innerHTML = `
            <button disabled class="px-6 py-2.5 bg-gray-100 text-gray-500 font-semibold rounded-lg cursor-not-allowed">
                <i class="fas fa-check mr-2"></i>Connected
            </button>
        `;

            showToast('Request accepted! 🎉', 'success');

        } catch (error) {
            acceptBtn.disabled = false;
            rejectBtn.disabled = false;
            acceptBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Accept';
            showToast('Failed to accept request', 'error');
        }
    }

    // Reject connection request
    async function rejectRequest(connectionId) {
        const acceptBtn = document.getElementById(`accept-btn-${connectionId}`);
        const rejectBtn = document.getElementById(`reject-btn-${connectionId}`);

        if (!rejectBtn) return;

        acceptBtn.disabled = true;
        rejectBtn.disabled = true;
        rejectBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Rejecting...';

        try {
            const response = await fetch(`/api/connections/${connectionId}/reject`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Authorization': 'Bearer ' + window.API_TOKEN
                },
                credentials: 'same-origin'
            });

            if (!response.ok) throw new Error('Failed');

            // Remove the user from list or show Connect button
            const container = acceptBtn.parentElement;
            container.innerHTML = `
            <button 
                onclick="connect(${connectionId})"
                id="btn-${connectionId}"
                class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition shadow-sm"
            >
                <i class="fas fa-user-plus mr-2"></i>Connect
            </button>
        `;

            showToast('Request rejected', 'info');

        } catch (error) {
            acceptBtn.disabled = false;
            rejectBtn.disabled = false;
            rejectBtn.innerHTML = '<i class="fas fa-times mr-1"></i>Reject';
            showToast('Failed to reject request', 'error');
        }
    }

    // Toast notification
    function showToast(message, type = 'success') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500'
        };
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-2xl z-50 transform transition-all`;
        toast.style.transform = 'translateX(400px)';
        toast.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${message}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.style.transform = 'translateX(0)', 10);
        setTimeout(() => toast.style.transform = 'translateX(400px)', 3000);
        setTimeout(() => toast.remove(), 3300);
    }

    console.log('✅ All functions loaded');
</script>
@endsection