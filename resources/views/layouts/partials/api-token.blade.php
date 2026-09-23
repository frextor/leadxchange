    @auth
    @php
        if (!session('web_api_token')) {
            auth()->user()->tokens()->where('name', 'web-spa')->delete();
            $freshTok = auth()->user()->createToken('web-spa');
            session(['web_api_token' => $freshTok->plainTextToken]);
        }
    @endphp
    @endauth
    <script>
        window.API_TOKEN = '{{ session("web_api_token", "") }}';
        window.CSRF      = '{{ csrf_token() }}';
    </script>
