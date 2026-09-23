    <!-- §15 CNIL — Bannière de consentement cookies -->
    @if(!isset($_COOKIE['lx_cookie_consent']))
    <div id="lx-cookie-banner"
         class="fixed bottom-0 left-0 right-0 z-50 bg-gray-900 text-white px-4 py-4 shadow-2xl"
         style="border-top:2px solid #14A98C;">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex-1 text-sm text-gray-300 leading-relaxed">
                <span class="text-white font-semibold">🍪 Cookies & Confidentialité</span> —
                Nous utilisons des cookies essentiels au fonctionnement et des cookies analytiques pour améliorer votre expérience.
                <a href="{{ url('/legal/privacy') }}" target="_blank" class="underline text-teal-400 hover:text-teal-300 ml-1">En savoir plus</a>
            </div>
            <div class="flex gap-3 flex-shrink-0">
                <button onclick="lxCookieRefuse()"
                        class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-600 text-gray-300 hover:bg-gray-800 transition">
                    Refuser
                </button>
                <button onclick="lxCookieAccept()"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                        style="background:#14A98C;">
                    Accepter
                </button>
            </div>
        </div>
    </div>
    <script>
    function lxSetCookie(name, value, days) {
        const d = new Date(); d.setTime(d.getTime() + days*24*60*60*1000);
        document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
    }
    function lxCookieAccept() {
        lxSetCookie('lx_cookie_consent', 'accepted', 395); // ~13 mois CNIL
        document.getElementById('lx-cookie-banner').style.display = 'none';
    }
    function lxCookieRefuse() {
        lxSetCookie('lx_cookie_consent', 'refused', 395);
        document.getElementById('lx-cookie-banner').style.display = 'none';
    }
    </script>
    @endif
