@extends('layouts.dashboard')

@section('title', 'Dashboard - LeadXchange')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Welcome Section -->
    <div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8 mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
            Hi {{ auth()->user()->first_name }}! 👋
        </h1>
        <p class="text-gray-600">What would you like to do today?</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm mb-1">Connections</p>
                    <p class="text-3xl font-bold text-gray-900">42</p>
                    <p class="text-green-600 text-sm mt-2">
                        <i class="fas fa-arrow-up mr-1"></i>+12% this month
                    </p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-teal-100 to-teal-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-teal-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm mb-1">Messages</p>
                    <p class="text-3xl font-bold text-gray-900">12</p>
                    <p class="text-blue-600 text-sm mt-2">
                        <i class="fas fa-circle mr-1 text-xs"></i>3 unread
                    </p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-envelope text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm mb-1">Leads</p>
                    <p class="text-3xl font-bold text-gray-900">8</p>
                    <p class="text-purple-600 text-sm mt-2">
                        <i class="fas fa-arrow-up mr-1"></i>2 new today
                    </p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                Quick Actions
            </h2>
            <div class="space-y-3">
                <a href="{{ route('connections.index') }}" class="flex items-center p-4 bg-gradient-to-r from-teal-50 to-teal-100 rounded-xl hover:from-teal-100 hover:to-teal-200 transition group">
                    <div class="w-12 h-12 bg-teal-500 rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition">
                        <i class="fas fa-user-plus text-white text-xl"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="font-semibold text-gray-900">Find Connections</p>
                        <p class="text-sm text-gray-600">Expand your network</p>
                    </div>
                    <i class="fas fa-arrow-right text-teal-600 group-hover:translate-x-1 transition"></i>
                </a>

                <a href="#" class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl hover:from-blue-100 hover:to-blue-200 transition group">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition">
                        <i class="fas fa-plus text-white text-xl"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="font-semibold text-gray-900">Create Lead</p>
                        <p class="text-sm text-gray-600">Add a new opportunity</p>
                    </div>
                    <i class="fas fa-arrow-right text-blue-600 group-hover:translate-x-1 transition"></i>
                </a>

                <a href="#" class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl hover:from-purple-100 hover:to-purple-200 transition group">
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition">
                        <i class="fas fa-paper-plane text-white text-xl"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="font-semibold text-gray-900">Send Message</p>
                        <p class="text-sm text-gray-600">Reach out to contacts</p>
                    </div>
                    <i class="fas fa-arrow-right text-purple-600 group-hover:translate-x-1 transition"></i>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-clock text-gray-500 mr-2"></i>
                Recent Activity
            </h2>
            <div class="space-y-4">
                <div class="flex items-start space-x-3 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-white"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900">Connection accepted</p>
                        <p class="text-sm text-gray-600">You're now connected with John Doe</p>
                        <p class="text-xs text-gray-400 mt-1">2 hours ago</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-envelope text-white"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900">New message received</p>
                        <p class="text-sm text-gray-600">Sarah sent you a message</p>
                        <p class="text-xs text-gray-400 mt-1">5 hours ago</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-star text-white"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900">New lead added</p>
                        <p class="text-sm text-gray-600">Tech Solutions Inc. - $15,000</p>
                        <p class="text-xs text-gray-400 mt-1">Yesterday</p>
                    </div>
                </div>
            </div>

            <a href="#" class="block mt-6 text-center text-sm text-teal-600 hover:text-teal-700 font-semibold">
                View all activity →
            </a>
        </div>
    </div>
</div>
@endsection
